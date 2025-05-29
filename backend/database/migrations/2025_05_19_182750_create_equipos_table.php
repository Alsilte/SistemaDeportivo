<?php
/**
 * MIGRACIÓN: CREAR TABLA EQUIPOS
 * 
 * Archivo: database/migrations/2025_05_19_182750_create_equipos_table.php
 * 
 * Copia este contenido en tu archivo create_equipos_table.php
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
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            
            // Información básica del equipo
            $table->string('nombre', 100);
            $table->string('logo')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            
            // Relación con deporte
            $table->foreignId('deporte_id')
                  ->constrained('deportes')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
            
            // Relación con administrador del equipo
            $table->foreignId('administrador_id')
                  ->nullable()
                  ->constrained('usuarios')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
            
            // Estado del equipo
            $table->boolean('activo')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['deporte_id', 'activo']);
            $table->unique(['nombre', 'deporte_id']); // Nombre único por deporte
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};