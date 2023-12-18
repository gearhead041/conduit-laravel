<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['show']]);
    }

    public function show(Request $request, $username)
    {
        $requestUser = auth()->user();
        $user = User::where('username', $username)->first();
        if ($user) {
            return response()->json(['profile' => [
                'username' => $user->username,
                'bio' => $user->bio,
                'image' => $user->image,
                'following' => $requestUser != null ? $requestUser->isFollowing($user) : false,
            ]]);
        }
        return response()->json(['error' => 'User not found'], 404);
    }

    public function follow(Request $request, $username)
    {
        $requestUser = auth()->user();
        $user = User::where('username', $username)->first();
        if ($user) {
            $user->followers()->attach($request->user_id);
            return response()->json(['profile' => [
                'username' => $user->username,
                'bio' => $user->bio,
                'image' => $user->image,
                'following' => true,
            ]]);
        }
        return response()->json(['error' => 'user not found'], 404);

    }

    public function unfollow(Request $request, $username)
    {
        $requestUser = auth()->user();
        $user = User::where('username', $username)->first();
        if ($user) {
            $user->followers()->detach($request->user_id);
            return response()->json(['profile' => [
                'username' => $user->username,
                'bio' => $user->bio,
                'image' => $user->image,
                'following' => $requestUser != null ? $requestUser->isFollowing($user) : false,
            ]]);
        }
        return response()->json(['error' => 'user not found'], 404);

    }
}
