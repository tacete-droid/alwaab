<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitPhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_url' => $this->original_url,
            'watermarked_url' => $this->watermarked_url,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'taken_at' => $this->taken_at?->toIso8601String(),
            'watermark_text' => $this->watermark_text,
        ];
    }
}
