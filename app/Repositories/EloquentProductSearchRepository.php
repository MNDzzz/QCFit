<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentProductSearchRepository implements ProductSearchRepository
{
    public function search(?string $query, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $products = Product::query()
            ->with(['images', 'category', 'brand', 'source']) // Añadido brand y source
            ->when($query, function (Builder $q) use ($query) {
                $q->where(function ($subQ) use ($query) {
                    $subQ->where('name', 'LIKE', "%{$query}%")
                        ->orWhereHas('brand', function($bq) use ($query) {
                            $bq->where('name', 'LIKE', "%{$query}%");
                        });
                });
            })
            ->when(isset($filters['category_id']), function (Builder $q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            })
            ->when(isset($filters['brand_id']), function (Builder $q) use ($filters) {
                $q->where('brand_id', $filters['brand_id']);
            })
            ->when(isset($filters['source_id']), function (Builder $q) use ($filters) {
                $q->where('source_id', $filters['source_id']);
            })
            ->orderBy('id', 'desc');

        return $products->paginate($perPage);
    }

    public function findBySourceId(string $sourceId)
    {
        return Product::where('source_id', $sourceId)->with('images')->first();
    }

    public function getLatestProducts(int $limit = 20)
    {
        return Product::with(['images', 'brand', 'category'])
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getLatestQCImages(int $limit = 20)
    {
        // Try getting real QC images first
        $images = \App\Models\ProductImage::where('type', 'qc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->with(['product.brand', 'product.source'])
            ->get();

        // Fallback: get any images if no QCs exist
        if ($images->isEmpty()) {
            $images = \App\Models\ProductImage::orderBy('id', 'desc')
                ->limit($limit)
                ->with(['product.brand', 'product.source'])
                ->get();
        }

        return $images;
    }
}
