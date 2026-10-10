<?php
namespace App\Repositories;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
    public function getAll(): Collection
    {
        return Role::all(['id', 'name', 'description']);
    }

    public function getById(int $id): ?Role
    {
        return Role::find($id, ['id', 'name', 'description']);
    }

    public function findWithPermissions(int $roleId): Role
    {
        return Role::with('permissions')->findOrFail($roleId);
    }

    
    public function syncPermissions(int $roleId, array $permissionIds): Role
    {
        $role = Role::findOrFail($roleId);
        
        $role->permissions()->sync($permissionIds);
        
        return $role->load('permissions');
    }

}