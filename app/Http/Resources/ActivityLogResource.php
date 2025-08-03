<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $defaultData = parent::toArray($request);

        $customData = [
            'user' => $this->user,
            'created_at' => $this->created_at->format('d-m-Y H:i:s'),
        ];
        return array_merge($defaultData, $customData);
    }
}
