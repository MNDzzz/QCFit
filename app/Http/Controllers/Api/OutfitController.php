<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outfit;
use Illuminate\Http\Request;

class OutfitController extends Controller
{
    public function index()
    {
        // Public feed: Latest outfits
        return Outfit::with('user', 'products.images')
            ->latest()
            ->limit(20)
            ->get();
    }

    public function show($id)
    {
        // Helper for Remix: Get products and their positions
        return Outfit::with([
            'products' => function ($q) {
                $q->withPivot(['pos_x', 'pos_y', 'rotation', 'scale_x', 'scale_y', 'z_index', 'selected_image_id']);
            },
            'products.images'
        ])->findOrFail($id);
    }

    public function store(Request $request)
    {
        // TODO: Save outfit logic (not explicitly asked but needed for Remix to have source data)
        // For now we assume outfits exist (I might need to seed one)
    }
}
