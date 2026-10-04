<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('US_role_permissions', function (Blueprint $table) {
        $table->id();
        
        // Llave foránea apuntando explícitamente a 'US_roles'
        $table->foreignId('role_id')
            ->constrained('US_roles')
            ->onDelete('cascade');
            
        // Llave foránea apuntando explícitamente a 'US_permissions'
        $table->foreignId('permission_id')
            ->constrained('US_permissions')
            ->onDelete('cascade');

        $table->timestamps();
        
        // Evitar permisos duplicados en un mismo rol
        $table->unique(['role_id', 'permission_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('us_role_permissions');
    }
};
