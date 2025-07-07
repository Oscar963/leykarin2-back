<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\InmuebleImportService;
use App\Services\Validation\FileValidationService;
use App\Services\Security\RateLimitService;
use App\Services\Logging\ImportLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class InmuebleImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $importService;
    protected $fileValidationService;
    protected $rateLimitService;
    protected $importLogService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->fileValidationService = Mockery::mock(FileValidationService::class);
        $this->rateLimitService = Mockery::mock(RateLimitService::class);
        $this->importLogService = Mockery::mock(ImportLogService::class);
        
        $this->importService = new InmuebleImportService(
            $this->fileValidationService,
            $this->rateLimitService,
            $this->importLogService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_sanitizes_file_name_correctly()
    {
        $reflection = new \ReflectionClass($this->importService);
        $method = $reflection->getMethod('sanitizeFileName');
        $method->setAccessible(true);

        // Test casos normales
        $this->assertEquals('test.xlsx', $method->invoke($this->importService, 'test.xlsx'));
        $this->assertEquals('test_file.xlsx', $method->invoke($this->importService, 'test_file.xlsx'));

        // Test path traversal
        $this->assertEquals('test.xlsx', $method->invoke($this->importService, '../../../test.xlsx'));
        $this->assertEquals('test.xlsx', $method->invoke($this->importService, './test.xlsx'));

        // Test caracteres especiales
        $this->assertEquals('test.xlsx', $method->invoke($this->importService, 'test<script>.xlsx'));
        $this->assertEquals('test.xlsx', $method->invoke($this->importService, 'test"file".xlsx'));

        // Test longitud
        $longName = str_repeat('a', 300) . '.xlsx';
        $result = $method->invoke($this->importService, $longName);
        $this->assertLessThanOrEqual(255, strlen($result));
    }

    /** @test */
    public function it_throws_exception_when_rate_limit_exceeded()
    {
        $file = UploadedFile::fake()->create('test.xlsx', 100);
        $userId = 1;

        $this->rateLimitService
            ->shouldReceive('checkLimit')
            ->with($userId)
            ->once()
            ->andReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Demasiadas importaciones. Intente más tarde.');

        $this->importService->processImport($file, $userId);
    }

    /** @test */
    public function it_validates_file_before_processing()
    {
        $file = UploadedFile::fake()->create('test.xlsx', 100);
        $userId = 1;

        $this->rateLimitService
            ->shouldReceive('checkLimit')
            ->with($userId)
            ->once()
            ->andReturn(true);

        $this->fileValidationService
            ->shouldReceive('validateFile')
            ->with($file)
            ->once()
            ->andThrow(new \Exception('Archivo inválido'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Archivo inválido');

        $this->importService->processImport($file, $userId);
    }

    /** @test */
    public function it_logs_import_start_and_complete()
    {
        $file = UploadedFile::fake()->create('test.xlsx', 100);
        $userId = 1;

        $this->rateLimitService
            ->shouldReceive('checkLimit')
            ->with($userId)
            ->once()
            ->andReturn(true);

        $this->fileValidationService
            ->shouldReceive('validateFile')
            ->with($file)
            ->once();

        $this->importLogService
            ->shouldReceive('logImportStart')
            ->with($userId, 'test.xlsx', 100)
            ->once();

        $this->importLogService
            ->shouldReceive('logImportComplete')
            ->once();

        // Mock Excel import
        $this->mock(\Maatwebsite\Excel\Facades\Excel::class, function ($mock) {
            $mock->shouldReceive('import')->once();
        });

        $result = $this->importService->processImport($file, $userId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    /** @test */
    public function it_generates_preview_correctly()
    {
        $file = UploadedFile::fake()->create('test.xlsx', 100);

        $this->fileValidationService
            ->shouldReceive('validateFile')
            ->with($file)
            ->once();

        // Mock Excel toArray
        $this->mock(\Maatwebsite\Excel\Facades\Excel::class, function ($mock) {
            $mock->shouldReceive('toArray')
                ->once()
                ->andReturn([
                    [
                        ['numero', 'descripcion', 'calle'],
                        ['1', 'Inmueble 1', 'Calle 1'],
                        ['2', 'Inmueble 2', 'Calle 2'],
                        ['3', 'Inmueble 3', 'Calle 3'],
                        ['4', 'Inmueble 4', 'Calle 4'],
                        ['5', 'Inmueble 5', 'Calle 5'],
                        ['6', 'Inmueble 6', 'Calle 6'],
                    ]
                ]);
        });

        $result = $this->importService->generatePreview($file);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('headers', $result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('total_rows', $result);
        $this->assertArrayHasKey('file_name', $result);
        $this->assertArrayHasKey('preview_rows', $result);

        $this->assertEquals(['numero', 'descripcion', 'calle'], $result['headers']);
        $this->assertEquals(6, $result['total_rows']);
        $this->assertEquals(5, $result['preview_rows']);
        $this->assertCount(5, $result['rows']); // Solo 5 filas por configuración
    }

    /** @test */
    public function it_builds_correct_response_for_successful_import()
    {
        $reflection = new \ReflectionClass($this->importService);
        $method = $reflection->getMethod('buildResponse');
        $method->setAccessible(true);

        $fileName = 'test.xlsx';
        $stats = [
            'imported' => 10,
            'skipped' => 2,
            'duplicates' => 1,
            'errors' => 1
        ];
        $errors = ['Error en fila 5'];

        $result = $method->invoke($this->importService, $fileName, $stats, $errors);

        $this->assertTrue($result['success']);
        $this->assertEquals(207, $result['http_status']); // Importación parcial
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('test.xlsx', $result['data']['file_name']);
        $this->assertTrue($result['data']['has_errors']);
        $this->assertEquals(1, $result['data']['error_count']);
    }

    /** @test */
    public function it_builds_correct_response_for_failed_import()
    {
        $reflection = new \ReflectionClass($this->importService);
        $method = $reflection->getMethod('buildResponse');
        $method->setAccessible(true);

        $fileName = 'test.xlsx';
        $stats = [
            'imported' => 0,
            'skipped' => 5,
            'duplicates' => 0,
            'errors' => 5
        ];
        $errors = ['Error 1', 'Error 2', 'Error 3'];

        $result = $method->invoke($this->importService, $fileName, $stats, $errors);

        $this->assertFalse($result['success']);
        $this->assertEquals(422, $result['http_status']); // No se importó nada
        $this->assertStringContainsString('No se pudo importar ningún registro', $result['message']);
    }

    /** @test */
    public function it_builds_correct_import_message()
    {
        $reflection = new \ReflectionClass($this->importService);
        $method = $reflection->getMethod('buildImportMessage');
        $method->setAccessible(true);

        // Test importación exitosa
        $stats = ['imported' => 10, 'skipped' => 0, 'duplicates' => 0];
        $message = $method->invoke($this->importService, $stats);
        $this->assertStringContainsString('✅ 10 inmuebles importados exitosamente', $message);

        // Test con duplicados
        $stats = ['imported' => 8, 'skipped' => 2, 'duplicates' => 2];
        $message = $method->invoke($this->importService, $stats);
        $this->assertStringContainsString('✅ 8 inmuebles importados exitosamente', $message);
        $this->assertStringContainsString('⚠️ 2 duplicados omitidos', $message);

        // Test con errores
        $stats = ['imported' => 5, 'skipped' => 3, 'duplicates' => 1];
        $message = $method->invoke($this->importService, $stats);
        $this->assertStringContainsString('❌ 2 registros con errores', $message);

        // Test sin registros
        $stats = ['imported' => 0, 'skipped' => 0, 'duplicates' => 0];
        $message = $method->invoke($this->importService, $stats);
        $this->assertEquals('No se procesaron registros.', $message);
    }
} 