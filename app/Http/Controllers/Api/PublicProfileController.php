<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Resources\PublicUserResource;
use App\Http\Resources\OutfitSimpleResource;
use App\Http\Resources\ProductResource;

class PublicProfileController extends Controller
{
    public function show($id)
    {
        $user = User::withCount(['followers', 'following', 'outfits'])
            ->findOrFail($id);

        // Check if authenticated user is following this profile
        $isFollowing = false;
        if (Auth::guard('sanctum')->check()) {
            $authUser = Auth::guard('sanctum')->user();
            if ($authUser) {
                // Optimización: usar exists() en la relación
                $isFollowing = $authUser->following()->where('following_id', $user->id)->exists();
            }
        }

        // Inyectar atributo temporal para el Resource
        $user->is_following = $isFollowing;

        // Cargar outfits del usuario (paginados)
        $outfits = $user->outfits()
            ->with('user') // Eager load user for the simple resource
            ->with(['products.images'])
            ->withCount('products')
            ->latest()
            ->paginate(12);

        // Cargar productos favoritos (wishlist)
        $favorites = $user->favorites()
            ->with(['brand', 'images', 'source', 'marketplace'])
            ->latest()
            ->get();

        return response()->json([
            'user' => new PublicUserResource($user),
            'outfits' => OutfitSimpleResource::collection($outfits)->response()->getData(true),
            'favorites' => ProductResource::collection($favorites),
        ]);
    }

    public function followers($id)
    {
        $user = User::findOrFail($id);
        
        $followers = $user->followers()
            ->select('users.id', 'users.name', 'users.alias', 'users.avatar')
            ->paginate(15);
            
        // Si hay usuario autenticado, verificamos si sigue a estos followers
        if (Auth::guard('sanctum')->check()) {
            $authUser = Auth::guard('sanctum')->user();
            $followingIds = $authUser->following()->pluck('users.id')->toArray();
            
            $followers->getCollection()->transform(function ($follower) use ($followingIds, $authUser) {
                // Prevenir mostrar "Siguiendo" a uno mismo
                $follower->is_following = in_array($follower->id, $followingIds);
                $follower->is_me = $follower->id === $authUser->id;
                return $follower;
            });
        }

        return response()->json($followers);
    }

    public function following($id)
    {
        $user = User::findOrFail($id);
        
        $following = $user->following()
            ->select('users.id', 'users.name', 'users.alias', 'users.avatar')
            ->paginate(15);

        // Si hay usuario autenticado, verificamos a cuáles de estos usuarios también sigue
        if (Auth::guard('sanctum')->check()) {
            $authUser = Auth::guard('sanctum')->user();
            $myFollowingIds = $authUser->following()->pluck('users.id')->toArray();
            
            $following->getCollection()->transform(function ($followed) use ($myFollowingIds, $authUser) {
                $followed->is_following = in_array($followed->id, $myFollowingIds);
                $followed->is_me = $followed->id === $authUser->id;
                return $followed;
            });
        }

        return response()->json($following);
    }

    public function favorites($id)
    {
        $user = User::findOrFail($id);
        $favorites = $user->favorites()
            ->with(['images', 'brand', 'category', 'source'])
            ->paginate(12);

        return ProductResource::collection($favorites);
    }
}
