<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InmueblesImport;
use Illuminate\Support\Facades\Log;

class TestExcelImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:excel {file : Ruta del archivo Excel a probar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la lectura de un archivo Excel y mostrar los encabezados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("El archivo no existe: {$filePath}");
            return 1;
        }
        
        try {
            $this->info("📁 Probando archivo: {$filePath}");
            
            // Leer el archivo Excel de forma más simple
            $data = Excel::toArray([], $filePath);
            
            if (empty($data) || empty($data[0])) {
                $this->error("❌ El archivo no contiene datos válidos");
                return 1;
            }
            
            $headers = $data[0][0] ?? [];
            
            $this->info("✅ Archivo leído correctamente");
            $this->info("📊 Hojas encontradas: " . count($data));
            $this->info("📋 Filas en primera hoja: " . count($data[0]));
            $this->info("🏷️  Encabezados encontrados: " . count($headers));
            
            $this->info("\n📝 Encabezados (fila 1):");
            foreach ($headers as $index => $header) {
                $this->line("  " . ($index + 1) . ". '{$header}'");
            }
            
            // Normalizar encabezados
            $normalizedHeaders = array_map(function($header) {
                return mb_strtolower(trim($header));
            }, $headers);
            
            $this->info("\n🔧 Encabezados normalizados:");
            foreach ($normalizedHeaders as $index => $header) {
                $this->line("  " . ($index + 1) . ". '{$header}'");
            }
            
            // Verificar columnas requeridas
            $requiredColumns = ['numero', 'descripcion'];
            $this->info("\n🔍 Verificando columnas requeridas:");
            
            foreach ($requiredColumns as $required) {
                $found = in_array($required, $normalizedHeaders);
                $status = $found ? "✅" : "❌";
                $this->line("  {$status} '{$required}': " . ($found ? "ENCONTRADO" : "NO ENCONTRADO"));
            }
            
            // Mostrar primeras filas de datos
            if (count($data[0]) > 1) {
                $this->info("\n📄 Primera fila de datos (fila 2):");
                $firstRow = $data[0][1];
                foreach ($firstRow as $index => $value) {
                    $header = $headers[$index] ?? "Columna {$index}";
                    $this->line("  {$header}: '{$value}'");
                }
            }
            
            if (count($data[0]) > 2) {
                $this->info("\n📄 Segunda fila de datos (fila 3):");
                $secondRow = $data[0][2];
                foreach ($secondRow as $index => $value) {
                    $header = $headers[$index] ?? "Columna {$index}";
                    $this->line("  {$header}: '{$value}'");
                }
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error al leer el archivo: " . $e->getMessage());
            Log::error('Error en comando test:excel', [
                'file' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
} 