<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    protected RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function getAllRoles(): Collection
    {
        return $this->roleRepository->getAll();
    }

    public function getRolePermissions(int $roleId)
    {
        return $this->roleRepository->findWithPermissions($roleId);
    }

    public function assignPermissionsToRole(int $roleId, array $permissionIds)
    {
        return $this->roleRepository->syncPermissions($roleId, $permissionIds);
    }
}