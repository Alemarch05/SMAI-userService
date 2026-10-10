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


}