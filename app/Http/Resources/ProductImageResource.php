<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
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
            'url' => $this->url,
            'type' => $this->type,
            'product_id' => $this->product_id,
            'title' => $this->product?->name, // Para LiveFeed
            'date_human' => $this->created_at?->diffForHumans(),
            'created_at_human' => $this->created_at?->diffForHumans(),
            // SOLO cuando se carga explícitamente el producto, devolver datos básicos (NO Resource completo para evitar recursión)
            'product' => $this->when($this->relationLoaded('product'), [
                'id' => $this->product?->id,
                'name' => $this->product?->name,
                'brand' => $this->product?->brand,
            ]),
        ];
    }
}
