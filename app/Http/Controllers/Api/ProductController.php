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

        $results = $this->searchRepository->search($query, (int) $limit);

        return response()->json($results);
    }

    public function liveFeed(Request $request)
    {
        $limit = $request->input('limit', 10);
        $results = $this->searchRepository->getLatestQCImages((int) $limit);
        return response()->json($results);
    }
    public function show($id)
    {
        $product = \App\Models\Product::with(['images', 'category'])->findOrFail($id);
        return new \App\Http\Resources\ProductResource($product);
    }
}
