<?php

namespace App\Services\Validation;

use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InmueblesImport;
use Illuminate\Support\Facades\Log;

class FileValidationService
{
    /**
     * Validar archivo completo
     */
    public function validateFile(UploadedFile $file): void
    {
        $this->validateFileType($file);
        $this->validateFileSize($file);
        $this->validateFileContent($file);
    }

    /**
     * Validar tipo de archivo
     */
    private function validateFileType(UploadedFile $file): void
    {
        $allowedTypes = config('import.allowed_types', ['xlsx', 'xls', 'csv']);
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedTypes)) {
            throw new \Exception("Tipo de archivo no permitido. Tipos válidos: " . implode(', ', $allowedTypes));
        }

        // Validar MIME type real
        $mimeType = $file->getMimeType();
        $allowedMimes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
            'application/vnd.ms-excel', // xls
            'text/csv', // csv
            'application/csv',
            'text/plain'
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            throw new \Exception("Tipo MIME no válido: {$mimeType}");
        }
    }

    /**
     * Validar tamaño de archivo
     */
    private function validateFileSize(UploadedFile $file): void
    {
        $maxSize = config('import.max_file_size', 10240) * 1024; // Convertir KB a bytes
        
        if ($file->getSize() > $maxSize) {
            $maxSizeMB = $maxSize / (1024 * 1024);
            throw new \Exception("El archivo excede el tamaño máximo permitido de {$maxSizeMB}MB");
        }

        // Verificar archivo vacío
        if ($file->getSize() === 0) {
            throw new \Exception("El archivo está vacío");
        }
    }

    /**
     * Validar contenido del archivo
     */
    private function validateFileContent(UploadedFile $file): void
    {
        try {
            Log::info('Iniciando validación de contenido del archivo', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize()
            ]);
            
            // Leer el archivo sin configuración de encabezados para obtener la estructura real
            $data = Excel::toArray([], $file);
            
            Log::info('Datos leídos del Excel', [
                'sheets_count' => count($data),
                'first_sheet_rows' => count($data[0] ?? [])
            ]);
            
            if (empty($data) || empty($data[0])) {
                throw new \Exception("El archivo no contiene datos válidos");
            }

            // Los encabezados están en la primera fila (índice 0)
            $headers = $data[0][0] ?? [];
            
            Log::info('Encabezados encontrados', [
                'headers' => $headers,
                'headers_count' => count($headers)
            ]);
            
            if (empty($headers)) {
                throw new \Exception("El archivo no contiene encabezados");
            }

            // Validar esquema de datos
            $this->validateDataSchema($headers);

            // Verificar que hay al menos una fila de datos (después de los encabezados)
            if (count($data[0]) < 2) {
                throw new \Exception("El archivo debe contener al menos una fila de datos");
            }

        } catch (\Exception $e) {
            Log::error('Error en validación de contenido', [
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName()
            ]);
            
            if (strpos($e->getMessage(), 'ZipArchive') !== false) {
                throw new \Exception("El archivo Excel está corrupto o no es válido");
            }
            throw $e;
        }
    }

    /**
     * Validar esquema de datos
     */
    private function validateDataSchema(array $headers): void
    {
        $requiredColumns = config('import.validation.required_columns', ['numero', 'descripcion']);
        $optionalColumns = config('import.validation.optional_columns', [
            'calle', 'numeracion', 'lote_sitio', 'manzana', 'poblacion_villa',
            'foja', 'inscripcion_numero', 'inscripcion_anio', 'rol_avaluo',
            'superficie', 'deslinde_norte', 'deslinde_sur', 'deslinde_este',
            'deslinde_oeste', 'decreto_incorporacion', 'decreto_destinacion', 'observaciones'
        ]);

        $allValidColumns = array_merge($requiredColumns, $optionalColumns);
        
        // Normalizar los encabezados del archivo
        $normalizedHeaders = array_map(function($header) {
            return mb_strtolower(trim($header));
        }, $headers);
        
        // Normalizar las columnas esperadas
        $normalizedValidColumns = array_map(function($column) {
            return mb_strtolower(trim($column));
        }, $allValidColumns);
        
        $normalizedRequiredColumns = array_map(function($column) {
            return mb_strtolower(trim($column));
        }, $requiredColumns);
        
        // Encontrar columnas que coincidan
        $foundColumns = array_intersect($normalizedHeaders, $normalizedValidColumns);
        
        // Verificar columnas requeridas
        $missingRequired = array_diff($normalizedRequiredColumns, $foundColumns);
        if (!empty($missingRequired)) {
            throw new \Exception("Columnas requeridas faltantes: " . implode(', ', $missingRequired));
        }

        // Verificar que al menos 2 columnas requeridas estén presentes
        if (count($foundColumns) < 2) {
            throw new \Exception("El archivo debe contener al menos 2 columnas válidas");
        }
        
        // Log para debugging
        Log::info('Validación de encabezados', [
            'headers_original' => $headers,
            'headers_normalized' => $normalizedHeaders,
            'required_columns' => $requiredColumns,
            'found_columns' => array_values($foundColumns),
            'missing_required' => array_values($missingRequired)
        ]);
    }

    /**
     * Validar integridad de datos
     */
    public function validateDataIntegrity(array $data): array
    {
        $errors = [];
        $rowNumber = 1; // Empezar desde 1 (después del header)

        foreach ($data as $row) {
            $rowNumber++;
            
            // Validar que la fila no esté completamente vacía
            if (empty(array_filter($row))) {
                continue; // Saltar filas completamente vacías
            }

            // Validar número (requerido)
            if (empty($row['numero'])) {
                $errors[] = "Fila {$rowNumber}: El número es requerido";
            }

            // Validar descripción (requerida)
            if (empty($row['descripcion'])) {
                $errors[] = "Fila {$rowNumber}: La descripción es requerida";
            }

            // Validar formato de número
            if (!empty($row['numero']) && !is_numeric($row['numero'])) {
                $errors[] = "Fila {$rowNumber}: El número debe ser numérico";
            }

            // Validar superficie si está presente
            if (!empty($row['superficie']) && !is_numeric($row['superficie'])) {
                $errors[] = "Fila {$rowNumber}: La superficie debe ser numérica";
            }

            // Limitar número de errores
            if (count($errors) >= config('import.validation.max_errors', 100)) {
                $errors[] = "Demasiados errores. Se detuvo la validación.";
                break;
            }
        }

        return $errors;
    }
} 