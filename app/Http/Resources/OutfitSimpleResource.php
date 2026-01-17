<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutfitSimpleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'thumbnail_url' => $this->thumbnail_url,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar, // O lógica de media library
            ],
            'created_at' => $this->created_at->toISOString(),
            'items_count' => $this->products_count, // Asumiendo que cargamos withCount('products')
        ];
    }
}
