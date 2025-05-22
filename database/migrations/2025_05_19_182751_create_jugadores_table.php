<?php

/**
 * 2. MIGRACIÓN: CREAR TABLA JUGADORES - CORREGIDA
 * 
 * Archivo: database/migrations/2025_05_19_182751_create_jugadores_table.php
 * 
 * REEMPLAZA TODO EL CONTENIDO DE TU ARCHIVO jugadores con esto:
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
        Schema::create('jugadores', function (Blueprint $table) {
            $table->id();

            // Relación con usuario
            $table->foreignId('usuario_id')
                ->constrained('usuarios')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Datos específicos del jugador
            $table->string('posicion', 50)->nullable();
            $table->integer('numero_camiseta')->nullable();

            // Estadísticas del jugador
            $table->integer('puntos')->default(0);
            $table->integer('partidos_jugados')->default(0);
            $table->integer('goles_favor')->default(0);
            $table->integer('goles_contra')->default(0);
            $table->integer('empatados')->default(0);
            $table->integer('ganados')->default(0);
            $table->integer('perdidos')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Asegurar que un usuario solo tenga un perfil de jugador
            $table->unique('usuario_id');

            // Índices
            $table->index('puntos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jugadores');
    }
};
