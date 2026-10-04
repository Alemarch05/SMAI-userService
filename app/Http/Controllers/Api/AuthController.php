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

    /**
     * Endpoint POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        // 1. Validar las entradas de la petición
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 2. Procesar la autenticación a través del servicio
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
}