<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $targetUser = User::findOrFail($request->user_id);
        $currentUser = Auth::user();

        if ($currentUser->id === $targetUser->id) {
            return response()->json(['message' => 'Cannot follow yourself'], 400);
        }

        // Toggle logic (using toggle() on relationship if exists, or manual attach/detach)
        // Assuming 'following' relationship is BelongsToMany or similar in User model
        $currentUser->following()->toggle($targetUser->id);

        $isFollowing = $currentUser->following()->where('following_id', $targetUser->id)->exists();

        return response()->json([
            'is_following' => $isFollowing,
            'message' => $isFollowing ? 'Followed' : 'Unfollowed'
        ]);
    }
}
