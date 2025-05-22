<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipo_torneo', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('equipo_id')
                ->constrained('equipos')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('torneo_id')
                ->constrained('torneos')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Información específica de la participación
            $table->datetime('fecha_inscripcion');
            $table->enum('estado_participacion', ['inscrito', 'confirmado', 'retirado', 'descalificado'])->default('inscrito');
            $table->text('observaciones')->nullable();

            // Información de contacto específica para este torneo
            $table->string('telefono_contacto', 20)->nullable();
            $table->string('email_contacto', 100)->nullable();

            $table->timestamps();

            // Restricción única
            $table->unique(['equipo_id', 'torneo_id']);

            // Índices
            $table->index(['torneo_id', 'estado_participacion']);
            $table->index('fecha_inscripcion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_torneo');
    }
};
