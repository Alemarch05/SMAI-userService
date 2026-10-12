<?php

namespace App\Services;

use App\Models\Permission;
use App\Repositories\PermissionRepository;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    protected PermissionRepository $permissionRepository;


    
    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function getAllPermissions(): Collection
    {
        return $this->permissionRepository->getAll();
    }

    public function createPermission(array $data): Permission
    {
        return $this->permissionRepository->create($data);
    }

    public function getPermissionById(int $id): ?Permission
    {
        return $this->permissionRepository->getById($id);
    }

    public function updatePermission(int $id, array $data): ?Permission
    {
        $permission = $this->permissionRepository->getById($id);

        if (!$permission) {
            return null;
        }

        return $this->permissionRepository->update($permission, $data);
    }

    /**
     * Eliminar un permiso por su ID.
     */
    public function deletePermission(int $id): bool
    {
        $permission = $this->permissionRepository->getById($id);

        if (!$permission) {
            return false;
        }

        return $this->permissionRepository->delete($permission);
    }

}