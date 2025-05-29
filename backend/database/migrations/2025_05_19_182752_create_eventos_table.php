<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================================

/**
 * MIGRACIÓN: CREAR TABLA EVENTOS
 * 
 * Archivo: database/migrations/2025_05_19_182752_create_eventos_table.php
 */

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            
            // Relación con partido
            $table->foreignId('partido_id')
                  ->constrained('partidos')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            // Información del evento
            $table->enum('tipo', ['gol', 'tarjeta_amarilla', 'tarjeta_roja', 'sustitucion', 'otro']);
            $table->integer('minuto');
            $table->text('descripcion');
            $table->decimal('valor', 10, 2)->nullable(); // Para eventos que tienen valor numérico
            
            // Relación con jugador (si aplica)
            $table->foreignId('jugador_id')
                  ->nullable()
                  ->constrained('jugadores')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
            
            $table->timestamps();
            
            // Índices
            $table->index(['partido_id', 'minuto']);
            $table->index(['tipo', 'jugador_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
