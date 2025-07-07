<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inmueble extends Model
{
    use HasFactory;

    protected $fillable = [
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
}
