<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FieldVisitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'employee_id' => $this->employee_id,
            'employee' => new UserResource($this->whenLoaded('employee')),
            'lat' => $this->lat,
            'lng' => $this->lng,
            'visited_at' => $this->visited_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'notes' => $this->notes,
            'status' => $this->status->value,
            'photos' => VisitPhotoResource::collection($this->whenLoaded('photos')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
