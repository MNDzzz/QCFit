<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductImageResource;
use App\Repositories\ProductSearchRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    protected $repository;
    protected $scrapingService;

    public function __construct(
        ProductSearchRepository $repository,
        \App\Services\ScrapingService $scrapingService
    ) {
        $this->repository = $repository;
        $this->scrapingService = $scrapingService;
    }

    /**
     * Smart Search: Handes both text queries and URLs.
     */
    public function index(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type', 'products'); // 'products' o 'outfits'
        
        $filters = [];
        if ($request->filled('category')) $filters['category_id'] = $request->input('category');
        if ($request->filled('brand')) $filters['brand_id'] = $request->input('brand');
        if ($request->filled('source')) $filters['source_id'] = $request->input('source');
        if ($request->filled('brand_name')) $filters['brand_name'] = $request->input('brand_name');

        try {
            // 1. URL Detection Logic (Regex for Weidian/Taobao/1688)
            if ($query) {
                $isUrl = preg_match('/(weidian\.com|taobao\.com|1688\.com)/i', $query);

                if ($isUrl) {
                    $scrapedData = $this->scrapingService->scrapeUrl($query);

                    // Devolvemos una estructura especial para indicar "Producto Único Encontrado"
                    return response()->json([
                        'type' => 'single_product',
                        'data' => $scrapedData
                    ]);
                }
            }

            if ($type === 'outfits') {
                $outfits = \App\Models\Outfit::with(['user', 'products.images'])
                    ->when($query, function ($q) use ($query) {
                        $q->where('title', 'LIKE', "%{$query}%")
                          ->orWhereHas('products', function($pq) use ($query) {
                              $pq->where('name', 'LIKE', "%{$query}%")
                                 ->orWhereHas('brand', function($bq) use ($query) {
                                     $bq->where('name', 'LIKE', "%{$query}%");
                                 });
                          });
                    })
                    ->when(isset($filters['category_id']), function ($q) use ($filters) {
                        $q->whereHas('products', function($pq) use ($filters) {
                            $pq->where('category_id', $filters['category_id']);
                        });
                    })
                    ->when(isset($filters['brand_id']), function ($q) use ($filters) {
                        $q->whereHas('products', function($pq) use ($filters) {
                            $pq->where('brand_id', $filters['brand_id']);
                        });
                    })
                    ->when(isset($filters['brand_name']), function ($q) use ($filters) {
                        $q->whereHas('products', function($pq) use ($filters) {
                            $pq->whereHas('brand', function($bq) use ($filters) {
                                $bq->where('name', 'LIKE', "%{$filters['brand_name']}%");
                            });
                        });
                    })
                    ->when(isset($filters['source_id']), function ($q) use ($filters) {
                        $q->whereHas('products', function($pq) use ($filters) {
                            $pq->where('source_id', $filters['source_id']);
                        });
                    })
                    ->latest()
                    ->paginate(20);

                return \App\Http\Resources\OutfitResource::collection($outfits);
            }

            // 2. Text Search for Products
            // Update the Eloquent implementation to support brand_name if needed
            $results = $this->repository->search($query, $filters);

            return ProductResource::collection($results);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error en la búsqueda',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Live Feed: Latest images added to the system.
     */
    public function liveFeed()
    {
        $images = $this->repository->getLatestQCImages(20);
        return ProductImageResource::collection($images);
    }
}
