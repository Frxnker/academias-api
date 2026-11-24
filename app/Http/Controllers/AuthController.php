<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST /api/register
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create($data);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // POST /api/login
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login correcto',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    // POST /api/logout
    public function logout(Request $request): JsonResponse
    {
        // Borra solo el token con el que se ha hecho esta petición
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ], 200);
    }
}