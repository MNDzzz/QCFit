<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'marketplace' => $this->marketplace,
            'source_id' => $this->source_id,
            'original_link' => $this->original_link,
            'category' => new CategoryResource($this->whenLoaded('category')),
            // Prioriza la imagen original si se pide, o devuelve todas
            'thumbnail' => $this->images->where('type', 'original')->first()?->url ?? $this->images->first()?->url,
            'qc_images_count' => $this->images->where('type', 'qc')->count(),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
