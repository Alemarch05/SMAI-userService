<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('US_role_permissions')->insert([
            // Permisos para Admin (ID 1) - Asignación de todos los permisos[cite: 5, 4]
            ['role_id' => 1, 'permission_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['role_id' => 1, 'permission_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['role_id' => 1, 'permission_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['role_id' => 1, 'permission_id' => 4, 'created_at' => $now, 'updated_at' => $now],

            // Permisos para Soporte (ID 2) - Asignación de lectura y actualización[cite: 5, 4]
            ['role_id' => 2, 'permission_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['role_id' => 2, 'permission_id' => 3, 'created_at' => $now, 'updated_at' => $now],

            // Permisos para Cliente (ID 3) - Asignación exclusiva de lectura[cite: 5, 4]
            ['role_id' => 3, 'permission_id' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}