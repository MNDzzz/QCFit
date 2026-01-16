<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicProfileController extends Controller
{
    public function show($id)
    {
        $user = User::withCount(['followers', 'following', 'outfits'])
            ->with([
                'outfits' => function ($q) {
                    $q->latest()->limit(10);
                },
                'outfits.products.images'
            ])
            ->findOrFail($id);

        $isFollowing = false;
        if (Auth::guard('sanctum')->check()) {
            $isFollowing = Auth::guard('sanctum')->user()->following()->where('following_id', $user->id)->exists();
        }

        return response()->json([
            'user' => $user,
            'is_following' => $isFollowing
        ]);
    }
}
