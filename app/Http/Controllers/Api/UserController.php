<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(protected UserServices $userService)
    {
    }

    /**
     * GET /api/users - Listar todos los usuarios.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->userService->getAllUsers(),
        ]);
    }

    /**
     * GET /api/users/{id?} - Ver detalle de un usuario o listar si no se envía ID.
     */
    public function show(?int $id = null): JsonResponse
    {
        if ($id === null) {
            return $this->index();
        }

        $user = $this->userService->getUserById($id);

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $user,
        ]);
    }

    /**
     * POST /api/users/{id?} - Crear un nuevo usuario.
     */
    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id !== null) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se debe enviar un ID en la URL al crear un usuario.',
            ], 400);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'integer', Rule::exists('US_roles', 'id')],
        ]);

        $user = $this->userService->createUser($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario creado correctamente',
            'data' => $user,
        ], 201);
    }

    /**
     * PUT|PATCH /api/users/{id?} - Actualizar datos o rol de un usuario.
     */
    public function update(Request $request, ?int $id = null): JsonResponse
    {
        if ($id === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'El ID del usuario es requerido para actualizar.',
            ], 400);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'role_id' => ['sometimes', 'required', 'integer', Rule::exists('US_roles', 'id')],
        ]);

        $user = $this->userService->updateUser($id, $validated);

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario actualizado correctamente',
            'data' => $user,
        ]);
    }

    /**
     * DELETE /api/users/{id?} - Eliminar un usuario.
     */
    public function destroy(?int $id = null): JsonResponse
    {
        if ($id === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'El ID del usuario es requerido para eliminar.',
            ], 400);
        }

        $deleted = $this->userService->deleteUser($id);

        if (! $deleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario eliminado correctamente',
        ]);
    }
}
