<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Resources\PublicUserResource;
use App\Http\Resources\OutfitSimpleResource;

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

        return response()->json([
            'user' => new PublicUserResource($user),
            'outfits' => OutfitSimpleResource::collection($outfits)->response()->getData(true),
        ]);
    }
}
