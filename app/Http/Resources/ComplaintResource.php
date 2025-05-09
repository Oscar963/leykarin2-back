<?php

namespace App\Http\Resources;

use App\Models\Complainant;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ComplaintResource  extends JsonResource
{
    public function toArray($request)
    {
        return array_filter([
            'id' => $this->id,
            'date' => $this->date ? Carbon::parse($this->date)->format('d-m-Y H:i:s') : null,
            'folio' => $this->folio,
            'hierarchical_level' => $this->hierarchical_level,
            'work_directly' => $this->work_directly,
            'immediate_leadership' => $this->immediate_leadership,
            'narration_facts' => $this->narration_facts,
            'narration_consequences' => $this->narration_consequences,
            'signature' => $this->signature,
            'type_complaint_id' => $this->type_complaint_id,
            'complainant_id' => $this->complainant_id,
            'denounced_id' => $this->denounced_id,

            // Relaciones con otras tablas
            'type_complaint' => new TypeComplaintResource($this->whenLoaded('typeComplaint')),
            'complainant' => new ComplainantResource($this->whenLoaded('complainant')),
            'denounced' => new DenouncedResource($this->whenLoaded('denounced')),
            'evidences' => EvidenceResource::collection($this->whenLoaded('evidences'))

        ], function ($value) {
            return !is_null($value); // Filtra los valores nulos
        });
    }
}
