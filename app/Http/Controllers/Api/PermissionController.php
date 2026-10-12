<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

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

    /**
     * PUT Actualizar un permiso existente.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // 1. Validar las entradas (ignorando el ID actual en el unique)
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('US_permissions', 'name')->ignore($id),
            ],
            'description' => 'nullable|string|max:255',
        ]);

        // 2. Ejecutar la actualización en la capa de servicio
        $updatedPermission = $this->permissionService->updatePermission($id, $validated);

        // 3. Manejo de respuesta 404 o 200
        if (!$updatedPermission) {
            return response()->json([
                'success' => false,
                'message' => 'Permiso no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permiso actualizado correctamente',
            'data' => $updatedPermission
        ], 200);
    }

}