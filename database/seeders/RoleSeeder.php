<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Acceso general al sistema, gestión de usuarios y parámetros.'],
            ['name' => 'soporte', 'description' => 'Atención de tickets escalados, diagnóstico Nivel 2.'],
            ['name' => 'cliente', 'description' => 'Acceso a funcionalidades básicas del sistema.'],

        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
            
        }

    }
}
