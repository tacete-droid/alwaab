<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'name' => $this->name,
            'location' => $this->location,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'status' => $this->status->value,
            'value_aed' => $this->value_aed,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'description' => $this->description,
            'consultant' => new ContactResource($this->whenLoaded('consultant')),
            'contractor' => new ContactResource($this->whenLoaded('contractor')),
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'field_visits' => FieldVisitResource::collection($this->whenLoaded('fieldVisits')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
