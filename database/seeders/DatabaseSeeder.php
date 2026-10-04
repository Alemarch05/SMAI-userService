<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);

        $adminRole = Role::where('name', 'ADMINISTRADOR')->first();
            

        User::factory()->create([
            'name' => 'Admin Inicial',
            'email' => 'admin@smai.com',
            'role_id' => $adminRole ? $adminRole->id : null,
        ]);
    }
}