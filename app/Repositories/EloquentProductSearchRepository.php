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
            ->with(['images', 'category']) // Eager loading para optimizar
            ->when($query, function (Builder $q) use ($query) {
                $q->where(function ($subQ) use ($query) {
                    $subQ->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('brand', 'LIKE', "%{$query}%");
                });
            })
            ->when(isset($filters['category_id']), function (Builder $q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            })
            ->when(isset($filters['brand']), function (Builder $q) use ($filters) {
                $q->where('brand', $filters['brand']);
            })
            ->when(isset($filters['marketplace']), function (Builder $q) use ($filters) {
                $q->where('marketplace', $filters['marketplace']);
            })
            ->orderBy('id', 'desc'); // Los más recientes primero por defecto

        return $products->paginate($perPage);
    }

    public function findBySourceId(string $sourceId)
    {
        return Product::where('source_id', $sourceId)->with('images')->first();
    }
}
