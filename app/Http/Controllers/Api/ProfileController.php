<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
 

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request)
    {
        $profile = Auth::user();
        $profile->name = $request->name;
        $profile->alias = $request->alias;
        $profile->email = $request->email;
        $profile->bio = $request->bio;
        $profile->agent_preference = $request->agent_preference;

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $profile->avatar = '/storage/' . $path;
        }

        $profile->save();

        return new UserResource($profile);
    }

    public function user(Request $request)
    {
        $user = $request->user()->load('roles');
        return new UserResource($user);
    }
}
