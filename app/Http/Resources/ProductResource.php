<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transformar el recurso en un array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'external_id' => $this->external_id,
            'original_link' => $this->original_link,
            
            // Relaciones
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'source' => new SourceResource($this->whenLoaded('source')),
            
            // IDs para formularios (facilitar edición)
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'source_id' => $this->source_id,

            // Imágenes
            'thumbnail' => $this->images->where('type', 'original')->first()?->url ?? $this->images->first()?->url,
            'qc_images_count' => $this->images->where('type', 'qc')->count(),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
        ];
    }
}
