<?php
/**
 * MIGRACIÓN: CREAR TABLA DEPORTES
 * 
 * Archivo: database/migrations/2025_05_19_182750_create_deportes_table.php
 * 
 * Copia este contenido en tu archivo create_deportes_table.php
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
        Schema::create('deportes', function (Blueprint $table) {
            $table->id();
            
            // Campos básicos del deporte
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            
            // Configuración específica del deporte (JSON)
            $table->json('configuracion_json')->nullable();
            
            // Imagen del deporte
            $table->string('imagen')->nullable();
            
            // Estado del deporte
            $table->boolean('activo')->default(true);
            
            $table->timestamps();
            
            // Índices
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deportes');
    }
};