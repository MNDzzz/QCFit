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
            // When loaded on feed, we might need parent product info
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
