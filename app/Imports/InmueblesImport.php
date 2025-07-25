<?php

namespace App\Imports;

use App\Models\Inmueble;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;

class InmueblesImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation
{
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
    }

    public function model(array $row)
    {
        // Validar que todas las columnas requeridas existan
        foreach ($this->expectedHeaders as $header) {
            if (!array_key_exists($header, $row)) {
                throw ValidationException::withMessages([
                    'file' => ["Falta la columna requerida: '{$header}'. Verifica que las cabeceras sean correctas y estén en el orden exacto."]
                ]);
            }
        }

        // Limpia los valores: si es null, lo convierte a ""
        $clean = function ($value) {
            return is_null($value) ? '' : $value;
        };

        return new Inmueble([
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
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => [self::class, 'beforeImport'],
        ];
    }

    public function rules(): array
    {
        return [];
    }
}
