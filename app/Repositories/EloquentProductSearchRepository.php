<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductSearchRepository implements ProductSearchRepository
{
    public function search(string $query, int $limit = 20): Collection
    {
        return Product::query()
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('brand', 'LIKE', "%{$query}%")
            ->with([
                'images' => function ($q) {
                    $q->where('type', 'qc')->take(1); // Prioritize QC images for thumbnails
                }
            ])
            ->limit($limit)
            ->get();
    }

    public function vectorSearch(array $vector)
    {
        // Placeholder for future Qdrant/Meilisearch implementation
        // For now, this could just return empty or throw an exception
        return [];
    }

    public function getLatestQCImages(int $limit = 10)
    {
        return \App\Models\ProductImage::where('type', 'qc')
            ->with('product')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
