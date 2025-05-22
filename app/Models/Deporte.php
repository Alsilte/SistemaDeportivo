<?php
/**
 * MODELO: DEPORTE
 * 
 * Archivo: app/Models/Deporte.php
 * Comando para crear: php artisan make:model Deporte
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deporte extends Model
{
    use HasFactory;

    protected $table = 'deportes';

    protected $fillable = [
        'nombre',
        'descripcion',
        'configuracion_json',
        'imagen',
        'activo',
    ];

    protected $casts = [
        'configuracion_json' => 'array',
        'activo' => 'boolean',
    ];

    // Relaciones
    public function equipos()
    {
        return $this->hasMany(Equipo::class);
    }

    public function torneos()
    {
        return $this->hasMany(Torneo::class);
    }
}