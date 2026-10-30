<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $result = $this->authService->login($credentials);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'data' => $result
        ], 200);
    }

    public function me(): JsonResponse
    {
        $userData = $this->authService->me();

        if (!$userData) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado o no autenticado'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $userData
        ], 200);
    }
}