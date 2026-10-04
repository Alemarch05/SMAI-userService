<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            // Llave primaria personalizada permissions_id
            $table->id('permissions_id');
            
            // Llave foránea hacia la tabla roles
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            
            // Nombre del permiso (ej: 'users.create', 'tickets.read')
            $table->string('name');
            $table->string('description')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};