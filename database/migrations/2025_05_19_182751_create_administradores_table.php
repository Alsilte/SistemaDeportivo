<?php

/**
 * 1. MIGRACIÓN: CREAR TABLA ADMINISTRADORES - CORREGIDA
 * 
 * Archivo: database/migrations/2025_05_19_182751_create_administradores_table.php
 * 
 * REEMPLAZA TODO EL CONTENIDO DE TU ARCHIVO administradores con esto:
 */

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
        Schema::create('administradores', function (Blueprint $table) {
            $table->id();

            // Relación con usuario
            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Permisos y roles del administrador
            $table->json('permisos')->nullable(); // Array de permisos específicos
            $table->enum('nivel_acceso', ['super_admin', 'admin', 'moderador'])->default('admin');

            $table->timestamps();
            $table->softDeletes();

            // Asegurar que un usuario solo tenga un perfil de administrador
            $table->unique('usuario_id');

            // Índices
            $table->index('nivel_acceso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administradores');
    }
};
