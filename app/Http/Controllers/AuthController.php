<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
         $user = User::create($request->validated() +['email_verified_at' => now()]);
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'data'    => new UserResource($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $token = auth()->attempt($request->only('email', 'password'));

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }
        $user = auth()->user();
         $user->token = $token;
        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data'    =>  new UserResource(auth()->user()),
        ]);
    }

    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    public function profile(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new UserResource(auth()->user()),
        ]);
    }
}