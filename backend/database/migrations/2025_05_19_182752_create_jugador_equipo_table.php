<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jugador_equipo', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('jugador_id')
                ->constrained('jugadores')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('equipo_id')
                ->constrained('equipos')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Información específica del jugador en este equipo
            $table->integer('numero_camiseta')->nullable();
            $table->string('posicion', 50)->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'lesionado', 'suspendido'])->default('activo');
            $table->boolean('es_capitan')->default(false);
            $table->boolean('es_titular')->default(false);

            // Fechas importantes
            $table->date('fecha_incorporacion');
            $table->date('fecha_salida')->nullable();

            // Estadísticas específicas en este equipo
            $table->integer('partidos_jugados')->default(0);
            $table->integer('goles_marcados')->default(0);
            $table->integer('asistencias')->default(0);
            $table->integer('tarjetas_amarillas')->default(0);
            $table->integer('tarjetas_rojas')->default(0);

            $table->timestamps();

            // Restricciones únicas
            $table->unique(['equipo_id', 'numero_camiseta']);

            // Índices
            $table->index(['jugador_id', 'equipo_id', 'estado']);
            $table->index(['equipo_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jugador_equipo');
    }
};
