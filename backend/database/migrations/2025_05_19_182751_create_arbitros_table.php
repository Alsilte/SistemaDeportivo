<?php
/**
 * MIGRACIÓN: CREAR TABLA ARBITROS
 * 
 * Archivo: database/migrations/2025_05_19_182751_create_arbitros_table.php
 * 
 * Copia este contenido en tu archivo create_arbitros_table.php
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
        Schema::create('arbitros', function (Blueprint $table) {
            $table->id();
            
            // Relación con usuario
            $table->foreignId('usuario_id')
                  ->constrained('usuarios')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            // Datos específicos del árbitro
            $table->string('licencia', 50)->nullable()->unique();
            $table->enum('posicion', ['principal', 'asistente', 'cuarto_arbitro'])->default('principal');
            $table->date('fecha_nacimiento')->nullable();
            $table->integer('partidos_arbitrados')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Asegurar que un usuario solo tenga un perfil de árbitro
            $table->unique('usuario_id');
            
            // Índices
            $table->index('posicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arbitros');
    }
};