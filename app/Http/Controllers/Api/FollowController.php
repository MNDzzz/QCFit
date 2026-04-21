<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Resources\PublicUserResource;

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

    public function followers($id)
    {
        $user = User::findOrFail($id);
        $followers = $user->followers()->paginate(20);
        return PublicUserResource::collection($followers);
    }

    public function following($id)
    {
        $user = User::findOrFail($id);
        $following = $user->following()->paginate(20);
        return PublicUserResource::collection($following);
    }
}
