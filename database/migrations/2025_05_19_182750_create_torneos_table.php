<?php

/**
 * 3. MIGRACIÓN: CREAR TABLA TORNEOS - CORREGIDA
 * 
 * Archivo: database/migrations/2025_05_19_182751_create_torneos_table.php
 * 
 * REEMPLAZA TODO EL CONTENIDO DE TU ARCHIVO torneos con esto:
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
        Schema::create('torneos', function (Blueprint $table) {
            $table->id();

            // Información básica del torneo
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('formato', 50); // 'liga', 'eliminacion', 'grupos'
            $table->enum('estado', ['planificacion', 'activo', 'finalizado', 'cancelado'])->default('planificacion');

            // Fechas del torneo
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->datetime('fecha_inscripcion_limite')->nullable();

            // Relación con deporte
            $table->foreignId('deporte_id')
                ->constrained('deportes')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            // Configuración específica del torneo (JSON)
            $table->json('configuracion')->nullable();

            // Premios y reconocimientos (JSON)
            $table->json('premios')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['deporte_id', 'estado']);
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('torneos');
    }
};
