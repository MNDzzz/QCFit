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

    public function __construct(ProductSearchRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Smart Search: Handes both text queries and URLs.
     */
    public function index(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json(['data' => []]);
        }

        // 1. URL Detection Logic (Regex for Weidian/Taobao/1688)
        // Detects patterns like: weidian.com/item.html?itemID=..., item.taobao.com..., etc.
        $isUrl = preg_match('/(weidian\.com|taobao\.com|1688\.com)/i', $query);

        if ($isUrl) {
            // TODO: In Future, call scraping service here if product not found.
            // For now, try to extract ID and search local DB or return specialized response.
            return response()->json([
                'type' => 'url_detected',
                'message' => 'Scraping service not yet fully linked. Searching by name/brand fallback.',
                // Fallback to text search for now to avoid empty results in demo
                'data' => ProductResource::collection($this->repository->search($query))
            ]);
        }

        // 2. Text Search
        $results = $this->repository->search($query);

        return ProductResource::collection($results);
    }

    /**
     * Live Feed: Latest QC photos uploaded.
     */
    public function liveFeed()
    {
        $images = $this->repository->getLatestQCImages(15);
        return ProductImageResource::collection($images);
    }
}
