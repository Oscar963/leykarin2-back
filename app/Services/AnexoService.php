<?php

namespace App\Services;

use App\Models\Anexo;

class AnexoService
{
    public function getAllAnexos()
    {
        return Anexo::orderBy('created_at', 'DESC')->get();
    }

    public function createAnexo(array $data)
    {
        $anexo = new Anexo();
        $anexo->internal_number = $data['internal_number'];
        $anexo->external_number = $data['external_number'];
        $anexo->office = $data['office'] ?? null;
        $anexo->unit = $data['unit'] ?? null;
        $anexo->person = $data['person'] ?? null;
        $anexo->created_at = now();
        $anexo->save();

        return $anexo;
    }

    public function getAnexoById($id)
    {
        return Anexo::findOrFail($id);
    }

    public function updateAnexo($id, array $data)
    {
        $anexo = $this->getAnexoById($id);

        $anexo->internal_number = $data['internal_number'];
        $anexo->external_number = $data['external_number'];
        $anexo->office = $data['office'] ?? null;
        $anexo->unit = $data['unit'] ?? null;
        $anexo->person = $data['person'] ?? null;
        $anexo->updated_at = now();
        $anexo->save();

        return $anexo;
    }

    public function deleteAnexo($id)
    {
        $anexo = $this->getAnexoById($id);
        $anexo->delete();
    }
}
