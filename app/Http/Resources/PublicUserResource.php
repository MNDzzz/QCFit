<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicUserResource extends JsonResource
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
            'name' => $this->name,
            'alias' => $this->alias,
            'bio' => $this->bio,
            'avatar' => $this->avatar,
            // Si usas Spatie Media Library: count($this->getMedia('*')) > 0 ? $this->getMedia('*')[0]->getUrl() : null
            'stats' => [
                'followers_count' => $this->followers_count ?? 0,
                'following_count' => $this->following_count ?? 0,
                'outfits_count' => $this->outfits_count ?? 0,
            ],
            // 'is_following' debe ser inyectado o calculado si el usuario auth existe
            'is_following' => $this->when(isset($this->is_following), $this->is_following),
            'created_at' => $this->created_at->format('M Y'),
        ];
    }
}
