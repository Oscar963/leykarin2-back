<?php

namespace App\Services;

use App\Models\Inmueble;
use Illuminate\Pagination\LengthAwarePaginator;

class InmuebleService
{
    public function getAllInmuebles()
    {
        return Inmueble::orderBy('created_at', 'DESC')->get();
    }

    public function getAllInmueblesByQuery(?string $query, ?int $perPage = 15, ?array $filters = []): LengthAwarePaginator
    {
        $queryBuilder = Inmueble::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('numero', 'LIKE', "%{$query}%")
                    ->orWhere('descripcion', 'LIKE', "%{$query}%")
                    ->orWhere('calle', 'LIKE', "%{$query}%")
                    ->orWhere('poblacion_villa', 'LIKE', "%{$query}%");
            });
        }

        if (!empty($filters)) {
            $queryBuilder->where($filters);
        }

        return $queryBuilder->paginate($perPage);
    }

    public function createInmueble(array $data)
    {
        $inmueble = new Inmueble();
        $inmueble->numero = $data['numero'] ?? '';
        $inmueble->descripcion = $data['descripcion'] ?? '';
        $inmueble->calle = $data['calle'] ?? '';
        $inmueble->numeracion = $data['numeracion'] ?? '';
        $inmueble->lote_sitio = $data['lote_sitio'] ?? '';
        $inmueble->manzana = $data['manzana'] ?? '';
        $inmueble->poblacion_villa = $data['poblacion_villa'] ?? '';
        $inmueble->foja = $data['foja'] ?? '';
        $inmueble->inscripcion_numero = $data['inscripcion_numero'] ?? '';
        $inmueble->inscripcion_anio = $data['inscripcion_anio'] ?? '';
        $inmueble->rol_avaluo = $data['rol_avaluo'] ?? '';
        $inmueble->superficie = $data['superficie'] ?? '';
        $inmueble->deslinde_norte = $data['deslinde_norte'] ?? '';
        $inmueble->deslinde_sur = $data['deslinde_sur'] ?? '';
        $inmueble->deslinde_este = $data['deslinde_este'] ?? '';
        $inmueble->deslinde_oeste = $data['deslinde_oeste'] ?? '';
        $inmueble->decreto_incorporacion = $data['decreto_incorporacion'] ?? '';
        $inmueble->decreto_destinacion = $data['decreto_destinacion'] ?? '';
        $inmueble->observaciones = $data['observaciones'] ?? '';
        $inmueble->created_at = now();
        $inmueble->save();

        return $inmueble;
    }

    public function getInmuebleById($id)
    {
        return Inmueble::findOrFail($id);
    }

    public function updateInmueble($id, array $data)
    {
        $inmueble = $this->getInmuebleById($id);

        $inmueble->numero = $data['numero'] ?? '';
        $inmueble->descripcion = $data['descripcion'] ?? '';
        $inmueble->calle = $data['calle'] ?? '';
        $inmueble->numeracion = $data['numeracion'] ?? '';
        $inmueble->lote_sitio = $data['lote_sitio'] ?? '';
        $inmueble->manzana = $data['manzana'] ?? '';
        $inmueble->poblacion_villa = $data['poblacion_villa'] ?? '';
        $inmueble->foja = $data['foja'] ?? '';
        $inmueble->inscripcion_numero = $data['inscripcion_numero'] ?? '';
        $inmueble->inscripcion_anio = $data['inscripcion_anio'] ?? '';
        $inmueble->rol_avaluo = $data['rol_avaluo'] ?? '';
        $inmueble->superficie = $data['superficie'] ?? '';
        $inmueble->deslinde_norte = $data['deslinde_norte'] ?? '';
        $inmueble->deslinde_sur = $data['deslinde_sur'] ?? '';
        $inmueble->deslinde_este = $data['deslinde_este'] ?? '';
        $inmueble->deslinde_oeste = $data['deslinde_oeste'] ?? '';
        $inmueble->decreto_incorporacion = $data['decreto_incorporacion'] ?? '';
        $inmueble->decreto_destinacion = $data['decreto_destinacion'] ?? '';
        $inmueble->observaciones = $data['observaciones'] ?? '';
        $inmueble->updated_at = now();
        $inmueble->save();

        return $inmueble;
    }

    public function deleteInmueble($id)
    {
        $inmueble = $this->getInmuebleById($id);
        $inmueble->delete();

        return $inmueble;
    }
}
