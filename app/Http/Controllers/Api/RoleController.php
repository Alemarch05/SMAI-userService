<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index(): JsonResponse
    {
        $roles = $this->roleService->getAllRoles();
        return response()->json(
            [
                'success' => '200',
                'data' => $roles
            ]);
    }

    public function getRolePermissions(int $roleId): JsonResponse
    {
        $roleWithPermissions = $this->roleService->getRolePermissions($roleId);

        return response()->json([
            'status' => 'success',
            'data'   => $roleWithPermissions
        ], 200);
    }

    public function updatePermissions(Request $request, int $roleId): JsonResponse
    {
        $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'exists:US_permissions,id'
        ]);

        $roleWithPermissions = $this->roleService->assignPermissionsToRole(
            $roleId, 
            $request->input('permissions')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Permisos actualizados correctamente para el rol.',
            'data'    => $roleWithPermissions
        ], 200);
    }
}
