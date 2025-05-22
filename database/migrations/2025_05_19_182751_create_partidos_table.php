<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN: CREAR TABLA PARTIDOS
 * 
 * Archivo: database/migrations/2025_05_19_182751_create_partidos_table.php
 */

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partidos', function (Blueprint $table) {
            $table->id();
            
            // Relación con torneo
            $table->foreignId('torneo_id')
                  ->constrained('torneos')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            // Equipos participantes
            $table->foreignId('equipo_local_id')
                  ->constrained('equipos')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            
            $table->foreignId('equipo_visitante_id')
                  ->constrained('equipos')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            
            // Información del partido
            $table->datetime('fecha');
            $table->string('lugar', 150)->nullable();
            $table->enum('estado', ['programado', 'en_curso', 'finalizado', 'suspendido', 'cancelado'])->default('programado');
            $table->string('resultado')->nullable(); // "2-1"
            
            // Relación con árbitro
            $table->foreignId('arbitro_id')
                  ->nullable()
                  ->constrained('arbitros')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
            
            // Estadísticas del partido
            $table->integer('goles_local')->default(0);
            $table->integer('goles_visitante')->default(0);
            
            // Información adicional (JSON)
            $table->json('estadisticas')->nullable();
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['torneo_id', 'fecha']);
            $table->index(['estado', 'fecha']);
            $table->index(['equipo_local_id', 'equipo_visitante_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partidos');
    }
};
