<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\ProductSearchRepository;
use App\Services\QcScraperService;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Controlador para la gestión de Productos (Búsqueda y CRUD Admin)
 */
class ProductController extends Controller
{
    protected $searchRepository;

    public function __construct(ProductSearchRepository $searchRepository)
    {
        $this->searchRepository = $searchRepository;
    }

    /**
     * Listado administrativo de productos con filtros
     */
    public function index()
    {
        $orderColumn = request('order_column', 'created_at');
        if (!in_array($orderColumn, ['id', 'name', 'created_at'])) {
            $orderColumn = 'created_at';
        }
        $orderDirection = request('order_direction', 'desc');
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        $products = Product::with(['category', 'brand', 'source', 'images'])
            ->when(request('search_global'), function ($query) {
                $query->where(function($q) {
                    $q->where('id', request('search_global'))
                        ->orWhere('name', 'like', '%'.request('search_global').'%')
                        ->orWhere('external_id', 'like', '%'.request('search_global').'%');
                });
            })
            ->when(request('category_id'), function ($query) {
                $query->where('category_id', request('category_id'));
            })
            ->when(request('brand_id'), function ($query) {
                $query->where('brand_id', request('brand_id'));
            })
            ->orderBy($orderColumn, $orderDirection)
            ->paginate(50);

        return ProductResource::collection($products);
    }

    /**
     * Motor de búsqueda para el frontend (Smart Search)
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $limit = $request->input('limit', 20);

        $results = $this->searchRepository->search($query, [], (int) $limit);

        return response()->json($results);
    }

    /**
     * Feed de imágenes QC para la home
     */
    public function liveFeed(Request $request)
    {
        $limit = $request->input('limit', 15);
        $results = $this->searchRepository->getLatestQCImages((int) $limit);
        return response()->json($results);
    }

    /**
     * Crear un nuevo producto (Admin)
     */
    public function store(StoreProductRequest $request)
    {
        $this->authorize('product-create');

        return DB::transaction(function () use ($request) {
            $product = Product::create($request->validated());

            if ($request->has('images')) {
                foreach ($request->images as $img) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'url' => $img['url'],
                        'type' => $img['type'] ?? 'original',
                    ]);
                }
            }

            return new ProductResource($product->load(['images', 'category', 'brand', 'source']));
        });
    }

    /**
     * Mostrar detalle de un producto (público y admin)
     */
    public function show($id, QcScraperService $qcScraper)
    {
        $product = Product::with(['images', 'category', 'brand', 'source'])->findOrFail($id);

        // Intento de Mock Scraping si no tiene imágenes QC
        $qcCount = $product->images->where('type', 'qc')->count();

        if ($qcCount === 0) {
            try {
                $qcScraper->fetchQCImages($product);
                $product->load('images');
            } catch (\Exception $e) {
                Log::error("Error auto-fetching QCs: " . $e->getMessage());
            }
        }

        return new ProductResource($product);
    }

    /**
     * Actualizar producto (Admin)
     */
    public function update(Product $product, StoreProductRequest $request)
    {
        $this->authorize('product-edit');

        return DB::transaction(function () use ($product, $request) {
            $product->update($request->validated());

            if ($request->has('images')) {
                // Para simplificar, reemplazamos imágenes si se envían nuevas
                $product->images()->delete();
                foreach ($request->images as $img) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'url' => $img['url'],
                        'type' => $img['type'] ?? 'original',
                    ]);
                }
            }

            return new ProductResource($product->load(['images', 'category', 'brand', 'source']));
        });
    }

    /**
     * Eliminar producto (Admin)
     */
    public function destroy(Product $product)
    {
        $this->authorize('product-delete');
        $product->delete();

        return response()->noContent();
    }
}
