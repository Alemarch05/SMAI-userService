<?php
namespace App\Repositories;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository
{
    public function getAll(): Collection
    {
        return Permission::all(['id', 'name', 'description']);
    }

    public function getById(int $id): ?Permission
    {
        return Permission::find($id, ['id', 'name', 'description']);
    }

    public function create(array $data): Permission
    {
        return Permission::create($data);
    }
}
