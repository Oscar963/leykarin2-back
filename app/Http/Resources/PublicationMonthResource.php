<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicationMonthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'month_number' => $this->month_number,
            'year' => $this->year,
            'display_name' => $this->formatted_date,
            'full_display_name' => $this->name . ' ' . $this->year,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 