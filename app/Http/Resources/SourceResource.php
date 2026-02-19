<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SourceResource extends JsonResource
{
    /**
     * Transformar el recurso en un array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo_url' => $this->logo_url,
            'base_url' => $this->base_url,
            'created_at' => $this->created_at->format('d/m/Y H:i'),
        ];
    }
}
