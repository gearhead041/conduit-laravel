<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }


    public function getUser(Request $request)
    {
        $user = auth()->user();

        return response()->json(['user' =>
            [
                'email' => $user->email,
                'username' => $user->username,
                'bio' => $user->bio,
                'image' => $user->image,
                'token' => $request->bearerToken(),
            ]]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'user.email' => 'required|email',
            'user.password' => 'required|string',
        ]);

        $credentials = ['email' => $request->user['email'], 'password' => $request->user['password']];
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user = auth()->user();
        return response()->json(['user' => [
            'username' => $user->username,
            'email' => $user->email,
            'bio' => $user->bio,
            'image' => $user->image,
            'token' => $token,
        ]]);
    }

    public function register(Request $request, User $user)
    {

        $request->validate([
            'user.username' => 'required|string',
            'user.email' => 'required|email|unique:users,email',
            'user.bio' => 'string',
            'user.password' => 'required|string',
        ]);

        $user = User::create([
            'username' => $request->user['username'],
            'email' => $request->user['email'],
            'bio' => $request->user['bio'] ?? null,
            'image' => $request->user['image'] ?? null,
            'password' => $request->user['password'],
        ]);

        $token = auth()->login($user);

        return response()->json(['user' => [
            'username' => $user->username,
            'email' => $user->email,
            'image' => $user->image,
            'bio' => $user->bio,
            'token' => $token,
        ]], 201);
    }

    public function update(Request $request, User $user)
    {
        $user = auth()->user();
        $user->update($request->user);
        $user['token'] = $request->bearerToken();
        return response()->json(['user' => $user], 200);
    }

}
