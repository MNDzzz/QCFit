<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * OutfitResource
 * 
 * Transforma los datos del outfit incluyendo los atributos pivote
 * necesarios para reconstruir el canvas en el frontend.
 */
class OutfitResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     * Incluye los datos del outfit y los productos con sus posiciones en el canvas.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail_url,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Información del creador
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'alias' => $this->user->alias ?? null,
                    'avatar' => $this->user->avatar ?? null,
                ];
            }),

            // Productos con datos pivote para reconstruir el canvas
            'items' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        // Datos del producto
                        'product_id' => $product->id,
                        'product' => [
                            'id' => $product->id,
                            'name' => $product->name,
                            'brand' => $product->brand,
                            'marketplace' => $product->marketplace,
                            // Obtener imágenes con sus tipos
                            'images' => $product->images->map(function ($image) {
                                return [
                                    'id' => $image->id,
                                    'url' => $image->url,
                                    'type' => $image->type,
                                ];
                            }),
                        ],

                        // Datos pivote del canvas (posiciones y transformaciones)
                        'x' => (float) $product->pivot->pos_x,
                        'y' => (float) $product->pivot->pos_y,
                        'rotation' => (float) $product->pivot->rotation,
                        'scaleX' => (float) $product->pivot->scale_x,
                        'scaleY' => (float) $product->pivot->scale_y,
                        'zIndex' => (int) $product->pivot->z_index,
                        'isFlipped' => (bool) $product->pivot->is_flipped,
                        'imageId' => $product->pivot->selected_image_id,
                    ];
                });
            }),

            // Conteo rápido de productos
            'items_count' => $this->whenLoaded('products', function () {
                return $this->products->count();
            }),
        ];
    }
}
