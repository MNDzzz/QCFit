<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OutfitAdminResource
 *
 * Resource simplificado para la vista de moderación admin.
 * Muestra datos esenciales para identificar y gestionar outfits.
 */
class OutfitAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail_url,
            'created_at' => $this->created_at?->toISOString(),
            'products_count' => $this->whenCounted('products'),

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'avatar' => $this->user->avatar ?? null,
                ];
            }),
        ];
    }
}
