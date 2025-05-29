<?php
/**
 * MIGRACIÓN: CREAR TABLA USUARIOS
 * 
 * Archivo: database/migrations/2025_05_19_182750_create_usuarios_table.php
 * 
 * Copia este contenido en tu archivo create_usuarios_table.php
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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            
            // Campos básicos del usuario
            $table->string('nombre', 100);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // Campos adicionales de perfil
            $table->string('telefono', 20)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('avatar')->nullable();
            
            // Campo para identificar el tipo de usuario
            $table->enum('tipo_usuario', ['jugador', 'arbitro', 'administrador']);
            
            // Estado del usuario
            $table->boolean('activo')->default(true);
            
            // Campos de auditoría
            $table->timestamps();
            $table->softDeletes(); // Para borrado lógico
            
            // Token para "recordarme"
            $table->rememberToken();
            
            // Índices para optimizar consultas
            $table->index(['tipo_usuario', 'activo']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};