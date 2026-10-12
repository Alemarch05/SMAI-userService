<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Endpoint POST para crear un nuevo permiso sin usar Resource.
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Validación inline
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:US_permissions,name',
            'description' => 'nullable|string|max:255',
        ]);

        // 2. Creación mediante la capa de Servicio / Repositorio
        $permission = $this->permissionService->createPermission($validated);

        // 3. Respuesta JSON directa devolviendo el modelo
        return response()->json([
            'success' => true,
            'message' => 'Permiso creado correctamente',
            'data' => $permission
        ], 201);
    }

    /**
     * GET /api/permissions
     * Listar todos los permisos.
     */
    public function index(): JsonResponse
    {
        $permissions = $this->permissionService->getAllPermissions();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ], 200);
    }

    /*
     * GET /api/permissions/{id} Obtener un permiso por ID.
     */
    public function show(int $id): JsonResponse
    {
        $permission = $this->permissionService->getPermissionById($id);

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permiso no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $permission
        ], 200);
    }

}