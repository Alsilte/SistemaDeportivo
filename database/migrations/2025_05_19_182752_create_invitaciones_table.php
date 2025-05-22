<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================================

/**
 * MIGRACIÓN: CREAR TABLA INVITACIONES
 * 
 * Archivo: database/migrations/2025_05_19_182752_create_invitaciones_table.php
 */

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitaciones', function (Blueprint $table) {
            $table->id();
            
            // Información básica de la invitación
            $table->string('email');
            $table->string('token', 100)->unique();
            $table->enum('estado', ['pendiente', 'aceptada', 'rechazada', 'expirada'])->default('pendiente');
            
            // Relaciones (opcional - puede ser para torneo O para equipo)
            $table->foreignId('torneo_id')
                  ->nullable()
                  ->constrained('torneos')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            $table->foreignId('equipo_id')
                  ->nullable()
                  ->constrained('equipos')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            // Usuario que envía la invitación
            $table->foreignId('enviado_por')
                  ->constrained('usuarios')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            // Fechas importantes
            $table->datetime('fecha_envio');
            $table->datetime('fecha_expiracion');
            $table->datetime('fecha_respuesta')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['email', 'estado']);
            $table->index('token');
            $table->index(['fecha_expiracion', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitaciones');
    }
};
