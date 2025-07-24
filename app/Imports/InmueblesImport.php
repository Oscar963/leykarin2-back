<?php

namespace App\Imports;

use App\Models\Inmueble;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Validation\ValidationException;
class InmueblesImport implements ToModel, WithHeadingRow
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
        
        return new Inmueble([
            'numero'     => $row['numero'],
            'descripcion'    => $row['descripcion'],
            'calle'    => $row['calle'],
            'numeracion'    => $row['numeracion'],
            'lote_sitio'    => $row['lote_sitio'],
            'manzana'    => $row['manzana'],
            'poblacion_villa'    => $row['poblacion_villa'],
            'foja'    => $row['foja'],
            'inscripcion_numero'    => $row['inscripcion_numero'],
            'inscripcion_anio'    => $row['inscripcion_anio'],
            'rol_avaluo'    => $row['rol_avaluo'],
            'superficie'    => $row['superficie'],
            'deslinde_norte'    => $row['deslinde_norte'],
            'deslinde_sur'    => $row['deslinde_sur'],
            'deslinde_este'    => $row['deslinde_este'],
            'deslinde_oeste'    => $row['deslinde_oeste'],
            'decreto_incorporacion'    => $row['decreto_incorporacion'],
            'decreto_destinacion'    => $row['decreto_destinacion'],
            'observaciones'    => $row['observaciones'],
        ]);
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => [self::class, 'beforeImport'],
        ];
    }
}
