<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $defaultData = parent::toArray($request);

        $customData = [
            'size' => round($this->size / (1024 * 1024), 2), // Tamaño en MB, redondeado a 2 decimales
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
            'mime_type' => $this->getFriendlyFileType($this->mime_type),
        ];
        return array_merge($defaultData, $customData);
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
            'image/webp' => 'Imagen WebP',
            'application/zip' => 'Archivo ZIP',
            'application/octet-stream' => 'Archivo ZIP',
            'application/x-zip-compressed' => 'Archivo ZIP',
            // Agregar más tipos según sea necesario
        ];

        return $mimeMap[$mimeType] ?? 'Desconocido';
    }
}
