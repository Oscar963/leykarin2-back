<?php

namespace App\Imports;

use App\Models\Inmueble;
use App\Services\ImportHistoriesService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;

class InmueblesImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation, WithEvents
{
    protected $importHistory;
    public $successCount = 0;
    public $errorCount = 0;
    public array $errorLog = [];
    protected ImportHistoriesService $importHistoriesService;
    protected $expectedHeaders = [
        'numero',
        'descripcion',
        'calle',
        'numeracion',
        'lote_sitio',
        'manzana',
        'poblacion_villa',
        'foja',
        'inscripcion_numero',
        'inscripcion_anio',
        'rol_avaluo',
        'superficie',
        'deslinde_norte',
        'deslinde_sur',
        'deslinde_este',
        'deslinde_oeste',
        'decreto_incorporacion',
        'decreto_destinacion',
        'observaciones',
    ];

    public function __construct(?string $filename = null, ?int $userId = null, ?string $model = null)
    {
        $this->importHistoriesService = app(ImportHistoriesService::class);

        // Solo crear el historial si tenemos los parámetros necesarios
        if ($filename && $userId && $model) {
            $this->importHistory = $this->importHistoriesService->startImport($filename, $userId, $model);
        }
    }

    /**
     * Registra los eventos de la importación.
     */
    public function registerEvents(): array
    {
        return [
            BeforeImport::class => [self::class, 'beforeImport'],
            AfterImport::class => function () {
                if ($this->importHistory) {
                    $this->importHistoriesService->completeImport(
                        $this->importHistory,
                        $this->successCount + $this->errorCount,
                        $this->successCount,
                        $this->errorCount,
                        $this->errorLog
                    );
                }
            },
        ];
    }

    public static function beforeImport(BeforeImport $event)
    {
        $sheet = $event->getReader()->getActiveSheet();
        $headings = $sheet->rangeToArray('A1:S1')[0];

        $expected = (new static)->expectedHeaders;

        // Normalizar para comparar (minúsculas, sin espacios extra)
        $normalize = function ($arr) {
            return array_map(fn($h) => mb_strtolower(trim($h)), $arr);
        };

        if ($normalize($headings) !== $normalize($expected)) {
            throw ValidationException::withMessages([
                'file' => ['El archivo no tiene las cabeceras requeridas o están en un orden/nombre incorrecto.']
            ]);
        }

        Session::put('import_start_time', now());
    }

    public function model(array $row)
    {
        $currentRow = $this->successCount + $this->errorCount + 1;
        
        try {
            // Validar que todas las columnas requeridas existan
            foreach ($this->expectedHeaders as $header) {
                if (!array_key_exists($header, $row)) {
                    $error = "Falta la columna requerida: '{$header}'. Verifica que las cabeceras sean correctas y estén en el orden exacto.";
                    $this->errorLog[] = [
                        'row' => $currentRow,
                        'error' => $error,
                        'timestamp' => now()
                    ];
                    $this->errorCount++;
                    return null; // No lanzar excepción, solo retornar null
                }
            }

            // Limpia los valores: si es null, lo convierte a ""
            $clean = function ($value) {
                return is_null($value) ? '' : $value;
            };

            $inmueble = new Inmueble([
                'numero'     => $clean($row['numero']),
                'descripcion'    => $clean($row['descripcion']),
                'calle'    => $clean($row['calle']),
                'numeracion'    => $clean($row['numeracion']),
                'lote_sitio'    => $clean($row['lote_sitio']),
                'manzana'    => $clean($row['manzana']),
                'poblacion_villa'    => $clean($row['poblacion_villa']),
                'foja'    => $clean($row['foja']),
                'inscripcion_numero'    => $clean($row['inscripcion_numero']),
                'inscripcion_anio'    => $clean($row['inscripcion_anio']),
                'rol_avaluo'    => $clean($row['rol_avaluo']),
                'superficie'    => $clean($row['superficie']),
                'deslinde_norte'    => $clean($row['deslinde_norte']),
                'deslinde_sur'    => $clean($row['deslinde_sur']),
                'deslinde_este'    => $clean($row['deslinde_este']),
                'deslinde_oeste'    => $clean($row['deslinde_oeste']),
                'decreto_incorporacion'    => $clean($row['decreto_incorporacion']),
                'decreto_destinacion'    => $clean($row['decreto_destinacion']),
                'observaciones'    => $clean($row['observaciones']),
            ]);

            $this->successCount++;
            return $inmueble;
        } catch (\Exception $e) {
            $this->errorLog[] = [
                'row' => $currentRow,
                'error' => $e->getMessage(),
                'timestamp' => now()
            ];
            $this->errorCount++;

            // No re-lanzar la excepción, retornar null para continuar con la siguiente fila
            return null;
        }
    }



    public function rules(): array
    {
        return [];
    }
}
