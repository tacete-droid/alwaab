<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'name' => $this->name,
            'type' => $this->type->value,
            'fitting_type' => $this->fitting_type,
            'size' => $this->size,
            'pressure_rating' => $this->pressure_rating,
            'unit' => $this->unit,
            'source_sno' => $this->source_sno,
            'price_aed' => $this->price_aed,
            'price_with_markup_aed' => $this->price_with_markup_aed,
            'datasheet_url' => $this->datasheet_url,
            'certifications' => $this->certifications,
            'images' => $this->images,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'inventory' => InventoryResource::collection($this->whenLoaded('inventory')),
            'is_active' => $this->is_active,
        ];
    }
}
