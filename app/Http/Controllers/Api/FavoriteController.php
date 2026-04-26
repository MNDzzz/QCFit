<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;

class FavoriteController extends Controller
{
    /**
     * Lista de productos guardados por un usuario (público).
     */
    public function userFavorites($id)
    {
        $user = User::findOrFail($id);
        
        $favorites = $user->favorites()
            ->with(['category', 'brand', 'source', 'images'])
            ->latest('product_user.created_at')
            ->paginate(12);

        return ProductResource::collection($favorites);
    }

    /**
     * Añadir/quitar un producto de los favoritos del usuario autenticado.
     */
    public function toggle($productId)
    {
        $user = auth()->user();
        $product = Product::findOrFail($productId);

        $isFavorited = $user->favorites()->where('product_id', $productId)->exists();

        if ($isFavorited) {
            $user->favorites()->detach($productId);
            $message = 'Producto eliminado de favoritos';
            $status = false;
        } else {
            $user->favorites()->attach($productId);
            $message = 'Producto añadido a favoritos';
            $status = true;
        }

        return response()->json([
            'message' => $message,
            'is_favorited' => $status
        ]);
    }
}
