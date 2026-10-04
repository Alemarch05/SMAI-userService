<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permisos = [
            ['name' => 'create', 'description' => 'Permiso para crear usuarios'],
            ['name' => 'read', 'description' => 'Permiso para leer usuarios'],
            ['name' => 'update', 'description' => 'Permiso para actualizar usuarios'],
            ['name' => 'delete', 'description' => 'Permiso para eliminar usuarios'],
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso['name'],
            ], [
                'description' => $permiso['description'],
            ]);
        }
    }
}
