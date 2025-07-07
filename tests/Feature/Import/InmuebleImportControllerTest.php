<?php

namespace Tests\Feature\Import;

use Tests\TestCase;
use App\Models\User;
use App\Contracts\Services\ImportServiceInterface;
use App\DTOs\Import\ImportResultDTO;
use App\Exceptions\Import\RateLimitExceededException;
use App\Exceptions\Import\FileValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;

class InmuebleImportControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected ImportServiceInterface $importService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->importService = Mockery::mock(ImportServiceInterface::class);
        
        $this->app->instance(ImportServiceInterface::class, $this->importService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_requires_authentication_to_import()
    {
        $response = $this->postJson('/api/inmuebles/import', [
            'excel_file' => UploadedFile::fake()->create('test.xlsx', 100)
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_successfully_imports_valid_file()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('test.xlsx', 100);
        
        $expectedResult = ImportResultDTO::success(
            '✅ 10 inmuebles importados exitosamente.',
            [
                'file_name' => 'test.xlsx',
                'statistics' => ['imported' => 10, 'skipped' => 0, 'duplicates' => 0],
                'has_errors' => false,
                'error_count' => 0
            ],
            200,
            'test-import-id'
        );

        $this->importService
            ->shouldReceive('processImport')
            ->once()
            ->with($file, $this->user->id)
            ->andReturn($expectedResult);

        $response = $this->postJson('/api/inmuebles/import', [
            'excel_file' => $file
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '✅ 10 inmuebles importados exitosamente.',
                'data' => [
                    'file_name' => 'test.xlsx',
                    'statistics' => ['imported' => 10, 'skipped' => 0, 'duplicates' => 0],
                    'has_errors' => false,
                    'error_count' => 0
                ]
            ]);
    }

    /** @test */
    public function it_handles_rate_limit_exceeded_exception()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('test.xlsx', 100);

        $this->importService
            ->shouldReceive('processImport')
            ->once()
            ->andThrow(new RateLimitExceededException($this->user->id, 3600));

        $response = $this->postJson('/api/inmuebles/import', [
            'excel_file' => $file
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'Demasiadas importaciones. Intente más tarde.'
                ]
            ]);
    }

    /** @test */
    public function it_handles_file_validation_exception()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('test.xlsx', 100);

        $this->importService
            ->shouldReceive('processImport')
            ->once()
            ->andThrow(new FileValidationException('Archivo inválido', ['error1', 'error2']));

        $response = $this->postJson('/api/inmuebles/import', [
            'excel_file' => $file
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'FILE_VALIDATION_ERROR',
                    'message' => 'Archivo inválido'
                ]
            ]);
    }

    /** @test */
    public function it_handles_partial_import_success()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('test.xlsx', 100);
        
        $expectedResult = ImportResultDTO::partial(
            '✅ 8 inmuebles importados exitosamente. Algunos registros fueron omitidos.',
            [
                'file_name' => 'test.xlsx',
                'statistics' => ['imported' => 8, 'skipped' => 2, 'duplicates' => 1],
                'has_errors' => true,
                'error_count' => 1,
                'errors' => ['Error en fila 5']
            ],
            ['Error en fila 5'],
            'test-import-id'
        );

        $this->importService
            ->shouldReceive('processImport')
            ->once()
            ->andReturn($expectedResult);

        $response = $this->postJson('/api/inmuebles/import', [
            'excel_file' => $file
        ]);

        $response->assertStatus(207)
            ->assertJson([
                'success' => true,
                'message' => '✅ 8 inmuebles importados exitosamente. Algunos registros fueron omitidos.',
                'warnings' => ['Error en fila 5']
            ]);
    }

    /** @test */
    public function it_validates_required_file_field()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/inmuebles/import', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['excel_file']);
    }

    /** @test */
    public function it_validates_file_type()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('test.txt', 100);

        $response = $this->postJson('/api/inmuebles/import', [
            'excel_file' => $file
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['excel_file']);
    }

    /** @test */
    public function it_validates_file_size()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('test.xlsx', 20000); // 20MB

        $response = $this->postJson('/api/inmuebles/import', [
            'excel_file' => $file
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['excel_file']);
    }

    /** @test */
    public function it_handles_unexpected_exceptions()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('test.xlsx', 100);

        $this->importService
            ->shouldReceive('processImport')
            ->once()
            ->andThrow(new \Exception('Unexpected error'));

        $response = $this->postJson('/api/inmuebles/import', [
            'excel_file' => $file
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'UNEXPECTED_ERROR',
                    'message' => 'Error inesperado durante la importación'
                ]
            ]);
    }

    /** @test */
    public function it_generates_preview_successfully()
    {
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('test.xlsx', 100);

        $this->importService
            ->shouldReceive('generatePreview')
            ->once()
            ->with($file)
            ->andReturn([
                'headers' => ['numero', 'descripcion', 'calle'],
                'rows' => [
                    ['1', 'Inmueble 1', 'Calle 1'],
                    ['2', 'Inmueble 2', 'Calle 2']
                ],
                'total_rows' => 2,
                'file_name' => 'test.xlsx',
                'preview_rows' => 5
            ]);

        $response = $this->postJson('/api/inmuebles/preview', [
            'excel_file' => $file
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'headers' => ['numero', 'descripcion', 'calle'],
                    'total_rows' => 2,
                    'file_name' => 'test.xlsx'
                ]
            ]);
    }

    /** @test */
    public function it_downloads_template_successfully()
    {
        $this->actingAs($this->user);

        $response = $this->get('/api/inmuebles/template');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('Content-Disposition', 'attachment; filename=plantilla_inmuebles.xlsx');
    }

    /** @test */
    public function it_returns_column_mapping()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/inmuebles/column-mapping');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'numero' => ['Número', 'N°', 'N', 'Item'],
                    'descripcion' => ['Descripción', 'Description'],
                    'calle' => ['Calle', 'Avenida', 'Pasaje', 'Avenida/Calle/Pasaje']
                ]
            ]);
    }
} 