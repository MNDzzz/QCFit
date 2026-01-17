<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ProductSearchRepository;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $searchRepository;

    public function __construct(ProductSearchRepository $searchRepository)
    {
        $this->searchRepository = $searchRepository;
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $limit = $request->input('limit', 20);

        $results = $this->searchRepository->search($query, [], (int) $limit);

        return response()->json($results);
    }

    public function liveFeed(Request $request)
    {
        $limit = $request->input('limit', 10);
        $results = $this->searchRepository->getLatestQCImages((int) $limit);
        return response()->json($results);
    }
    public function show($id, \App\Services\QcScraperService $qcScraper) // Inyección de dependencias
    {
        $product = \App\Models\Product::with(['images', 'category'])->findOrFail($id);

        // Lazy Loading de QCs si no existen
        // Verificamos si tiene imágenes de tipo 'qc'
        $qcCount = $product->images->where('type', 'qc')->count();

        if ($qcCount === 0) {
            // Intentar buscar QCs en tiempo real
            try {
                $qcScraper->fetchQCImages($product);
                // Recargamos las imágenes para incluirlas en la respuesta
                $product->load('images');
            } catch (\Exception $e) {
                // Silencioso: si falla, simplemente mostramos lo que hay
                \Illuminate\Support\Facades\Log::error("Error auto-fetching QCs en ProductController: " . $e->getMessage());
            }
        }

        return new \App\Http\Resources\ProductResource($product);
    }
}
