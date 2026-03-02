<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        $token = auth()->attempt($request->only('email', 'password'));

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas.',
                'data'    => null,
                'code'    => 401,
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso.',
            'data'    => [
                'token' => $token,
                'type'  => 'bearer',
                'user'  => auth()->user(),
            ],
            'code'    => 200,
        ], 200);
    }

    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso.',
            'data'    => null,
            'code'    => 200,
        ], 200);
    }
}
