<?php

namespace App\Services;

use App\Models\FormF1;
use App\Models\PurchasePlan;
use Illuminate\Support\Str;

class FormF1Service
{
    /**
     * Obtiene todos los formularios F1
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllFormF1s()
    {
        return FormF1::orderBy('created_at', 'DESC')->get();
    }

    /**
     * Obtiene formularios F1 paginados con filtrado
     *
     * @param string|null $query Término de búsqueda
     * @param int $perPage Número de elementos por página
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllFormF1sByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = FormF1::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            });
        }

        return $queryBuilder->paginate($perPage);
    }

    /**
     * Obtiene un formulario F1 por su ID
     *
     * @param int $id ID del formulario F1
     * @return FormF1
     */
    public function getFormF1ById($id)
    {
        return FormF1::findOrFail($id);
    }

    /**
     * Crea un nuevo formulario F1
     *
     * @param array $data Datos del formulario F1
     * @return FormF1
     */
    public function createFormF1(array $data)
    {
        $purchasePlan = PurchasePlan::findOrFail($data['purchase_plan_id']);
        $direction = $purchasePlan->direction;
        $currentDate = now()->format('Y-m-d H:i');
        $nameFile = $data['name_file'] ?? "{$currentDate} - {$direction->name} - FormF1";

        $formF1 = new FormF1();
        $formF1->name = trim($nameFile);
        $formF1->description = isset($data['description']) ? trim($data['description']) : "Formulario F1 generado automáticamente para el plan de compra de la dirección {$direction->name}";

        if (isset($data['file']) && $data['file'] instanceof \Illuminate\Http\UploadedFile) {
            $formF1->size = $data['file']->getSize();
            $formF1->type = $data['file']->getClientMimeType();

            // Generar nombre único para el archivo
            $fileName = Str::slug($nameFile) . '-' . uniqid() . '.' . $data['file']->getClientOriginalExtension();
            // Almacenar archivo en el disco público
            $filePath = $data['file']->storeAs('uploads/form_f1', $fileName, 'public');
            // Generar URL pública del archivo
            $formF1->url = url('storage/' . $filePath);
        }

        $formF1->amount = isset($data['amount']) ? $data['amount'] : 0;
        $formF1->created_by = auth()->id();
        $formF1->save();

        $purchasePlan->form_f1_id = $formF1->id;
        $purchasePlan->save();

        return $formF1;
    }

    /**
     * Actualiza un formulario F1 existente
     *
     * @param int $id ID del formulario F1
     * @param array $data Datos actualizados
     * @return FormF1
     */
    public function updateFormF1($id, array $data)
    {
        $formF1 = $this->getFormF1ById($id);
        $formF1->name = trim($data['name']);
        $formF1->description = isset($data['description']) ? trim($data['description']) : $formF1->description;
        $formF1->url = isset($data['url']) ? trim($data['url']) : $formF1->url;
        $formF1->type = isset($data['type']) ? trim($data['type']) : $formF1->type;
        $formF1->size = isset($data['size']) ? $data['size'] : $formF1->size;
        $formF1->amount = isset($data['amount']) ? $data['amount'] : $formF1->amount;
        $formF1->updated_by = auth()->id();
        $formF1->save();

        return $formF1;
    }

    /**
     * Elimina un formulario F1
     *
     * @param int $id ID del formulario F1
     * @return void
     */
    public function deleteFormF1($id)
    {
        $formF1 = $this->getFormF1ById($id);
        $formF1->delete();
    }
}
