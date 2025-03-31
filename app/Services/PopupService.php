<?php

namespace App\Services;

use App\Models\Popup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PopupService
{
    /**
     * Obtener todos los popups.
     */
    public function getAllPopups()
    {
        return Popup::orderBy('created_at', 'DESC')->get();
    }

    public function getAllPopupsByQuery(?string $query, int $perPage = 15)
    {
        $queryBuilder = Popup::orderBy('created_at', 'DESC');

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%");
            });
        }
        return $queryBuilder->paginate($perPage);
    }

    /**
     * Crear un nuevo popup.
     */
    public function createPopup(array $data)
    {
        $popup = new Popup();
        $popup->title = $data['title'];
        $popup->date = now();
        $popup->date_expiration = $data['date_expiration'];
        $popup->status = $data['status'];
        $popup->link = $data['link'];
        $popup->created_by = auth()->id();

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $imageName = Str::slug($data['title']) . '-' . uniqid() . '.' . $data['image']->getClientOriginalExtension();
            $imagePath = $data['image']->storeAs('banners', $imageName, 'public');
            $popup->image = url('storage/' . $imagePath);
        }

        $popup->save();
        return $popup;
    }

    /**
     * Obtener un popup por su ID.
     */
    public function getPopupById($id)
    {
        return Popup::findOrFail($id);
    }

    /**
     * Actualizar un popup existente.
     */
    public function updatePopup($id, array $data)
    {
        $popup = $this->getPopupById($id);

        $popup->title = $data['title'];
        $popup->date = now();
        $popup->date_expiration = $data['date_expiration'];
        $popup->status = $data['status'];
        $popup->link = $data['link'];
        $popup->updated_by = auth()->id();

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $imageName = Str::slug($data['title']) . '-' . uniqid() . '.' . $data['image']->getClientOriginalExtension();
            $imagePath = $data['image']->storeAs('banners', $imageName, 'public');
            $popup->image = url('storage/' . $imagePath);
        }

        $popup->save();
        return $popup;
    }

    /**
     * Eliminar un popup.
     */
    public function deletePopup($id)
    {
        $popup = $this->getPopupById($id);

        if ($popup->image) {
            $imagePath = str_replace('/storage/', '', $popup->image);
            Storage::disk('public')->delete($imagePath);
        }

        $popup->deleted_by = auth()->id();
        $popup->save();
        $popup->delete();
    }
}
