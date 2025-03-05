<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class FileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'url' => $this->url,
            'type' => $this->getFriendlyFileType($this->type),
            'size' => round($this->size / (1024 * 1024), 2), // Tamaño en MB, redondeado a 2 decimales
            'created_at' => $this->created_at ? $this->created_at->format('d-m-Y H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('d-m-Y H:i:s') : null,
            'created_by' => new UserResource($this->createdBy),
            'updated_by' => $this->updatedBy ? new UserResource($this->updatedBy) : null,
        ];
    }
    /**
     * Mapear el MIME type a un nombre de archivo amigable.
     */
    protected function getFriendlyFileType(string $mimeType): string
    {
        $mimeMap = [
            'application/pdf' => 'PDF',
            'application/msword' => 'Word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word',
            'application/vnd.ms-excel' => 'Excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel',
            'application/vnd.ms-powerpoint' => 'PowerPoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PowerPoint',
            'video/mp4' => 'Video MP4',
            'image/jpeg' => 'Imagen JPEG',
            'image/jpg' => 'Imagen JPEG',
            'image/png' => 'Imagen PNG',
            'image/gif' => 'Imagen GIF',
            'image/bmp' => 'Imagen BMP',
            'application/zip' => 'Archivo ZIP', 
            'application/octet-stream' => 'Archivo ZIP', 
            'application/x-zip-compressed' => 'Archivo ZIP',
            // Agregar más tipos según sea necesario
        ];

        return $mimeMap[$mimeType] ?? 'Desconocido';
    }
}
