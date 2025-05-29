<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================================

/**
 * MIGRACIÓN: CREAR TABLA CLASIFICACIONES
 * 
 * Archivo: database/migrations/2025_05_19_182752_create_clasificaciones_table.php
 */

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clasificaciones', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('torneo_id')
                  ->constrained('torneos')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            $table->foreignId('equipo_id')
                  ->constrained('equipos')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            // Estadísticas del equipo en el torneo
            $table->integer('posicion')->default(0);
            $table->integer('puntos')->default(0);
            $table->integer('partidos_jugados')->default(0);
            $table->integer('ganados')->default(0);
            $table->integer('empatados')->default(0);
            $table->integer('perdidos')->default(0);
            $table->integer('goles_favor')->default(0);
            $table->integer('goles_contra')->default(0);
            
            // Campo calculado automáticamente
            $table->integer('diferencia_goles')->storedAs('goles_favor - goles_contra');
            
            $table->timestamps();
            
            // Asegurar que un equipo solo tenga una clasificación por torneo
            $table->unique(['torneo_id', 'equipo_id']);
            $table->index(['torneo_id', 'puntos', 'diferencia_goles']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clasificaciones');
    }
};

