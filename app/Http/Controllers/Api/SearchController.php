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

        if (!$query) {
            return response()->json(['data' => []]);
        }

        try {
            // 1. URL Detection Logic (Regex for Weidian/Taobao/1688)
            $isUrl = preg_match('/(weidian\.com|taobao\.com|1688\.com)/i', $query);

            if ($isUrl) {
                $scrapedData = $this->scrapingService->scrapeUrl($query);

                // Devolvemos una estructura especial para indicar "Producto Único Encontrado"
                return response()->json([
                    'type' => 'single_product',
                    'data' => $scrapedData
                ]);
            }

            // 2. Text Search
            $results = $this->repository->search($query);

            return ProductResource::collection($results);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error en la búsqueda',
                'error' => $e->getMessage()
            ], 400);
        }
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
