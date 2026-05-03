<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\ProductSearchRepository;
use App\Services\QcScraperService;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductImageResource;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Controller for Product management (Search & Admin CRUD)
 */
class ProductController extends Controller
{
    protected $searchRepository;

    public function __construct(ProductSearchRepository $searchRepository)
    {
        $this->searchRepository = $searchRepository;
    }

    /**
     * Admin product listing with filters
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
     * Smart Search engine for frontend
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $limit = $request->input('limit', 20);

        $results = $this->searchRepository->search($query, [], (int) $limit);

        return ProductResource::collection($results);
    }

    /**
     * QC images feed for homepage
     */
    public function liveFeed(Request $request)
    {
        $limit = $request->input('limit', 15);
        $results = $this->searchRepository->getLatestQCImages((int) $limit);
        return ProductImageResource::collection($results);
    }

    /**
     * Create a new product (Admin)
     */
    public function store(StoreProductRequest $request)
    {
        $this->authorize('product-create');

        return DB::transaction(function () use ($request) {
            $product = Product::create($request->validated());

            // Handle URL-based images (API/legacy)
            if ($request->has('images')) {
                foreach ($request->images as $img) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'url' => $img['url'],
                        'type' => $img['type'] ?? 'original',
                    ]);
                }
            }

            // Handle file uploads from admin panel
            if ($request->hasFile('images_upload')) {
                $this->handleImageUploads($product, $request->file('images_upload'));
            }

            return new ProductResource($product->load(['images', 'category', 'brand', 'source']));
        });
    }

    /**
     * Show product detail (public and admin)
     */
    public function show($id, QcScraperService $qcScraper)
    {
        $product = Product::with(['images', 'category', 'brand', 'source'])->findOrFail($id);

        // Auto-fetch QC images if none exist
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
     * Update product (Admin)
     */
    public function update(Product $product, StoreProductRequest $request)
    {
        $this->authorize('product-edit');

        return DB::transaction(function () use ($product, $request) {
            $product->update($request->validated());

            // Handle URL-based images
            if ($request->has('images')) {
                $product->images()->delete();
                foreach ($request->images as $img) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'url' => $img['url'],
                        'type' => $img['type'] ?? 'original',
                    ]);
                }
            }

            // Handle file uploads from admin panel
            if ($request->hasFile('images_upload')) {
                $this->handleImageUploads($product, $request->file('images_upload'));
            }

            // Handle removal of existing images
            if ($request->has('remove_image_ids')) {
                $idsToRemove = is_array($request->remove_image_ids)
                    ? $request->remove_image_ids
                    : json_decode($request->remove_image_ids, true);

                if (!empty($idsToRemove)) {
                    ProductImage::where('product_id', $product->id)
                        ->whereIn('id', $idsToRemove)
                        ->delete();
                }
            }

            return new ProductResource($product->load(['images', 'category', 'brand', 'source']));
        });
    }

    /**
     * Delete product (Admin)
     */
    public function destroy(Product $product)
    {
        $this->authorize('product-delete');
        $product->delete();

        return response()->noContent();
    }

    /**
     * Toggle favorite for authenticated user
     */
    public function toggleFavorite($id)
    {
        $product = Product::findOrFail($id);
        $user = auth()->user();

        $user->favorites()->toggle($product->id);
        
        $isFavorite = $user->favorites()->where('product_id', $product->id)->exists();

        return response()->json([
            'is_favorite' => $isFavorite,
            'message' => $isFavorite ? 'Added to favorites' : 'Removed from favorites'
        ]);
    }

    /**
     * Handle uploaded image files: save to public/images/products/ and create DB records.
     */
    private function handleImageUploads(Product $product, array $files): void
    {
        $destinationPath = public_path('images/products');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        foreach ($files as $file) {
            $filename = Str::slug($product->name) . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);

            ProductImage::create([
                'product_id' => $product->id,
                'url' => '/images/products/' . $filename,
                'type' => 'original',
            ]);
        }
    }
}
