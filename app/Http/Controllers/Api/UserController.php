<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected UserServices $userService;


    public function __construct(UserServices $userService)
    {
        $this->userService = $userService;
    }

    // GET 
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->userService->getAllUsers()
        ]);
    }

    // GET 
    public function show(int $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->userService->getUserById($id)
        ]);
    }

    // POST
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:US_roles,id' 
        ]);

        $user = $this->userService->createUser($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario creado correctamente',
            'data' => $user
        ], 201);
    }

    // PUT o PATCH
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role_id' => 'sometimes|required|exists:US_roles,id'
        ]);

        $user = $this->userService->updateUser($id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario actualizado correctamente',
            'data' => $user
        ]);
    }

    // DELETE
    public function destroy(int $id): JsonResponse
    {
        $this->userService->deleteUser($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario eliminado correctamente'
        ]);
    }
}