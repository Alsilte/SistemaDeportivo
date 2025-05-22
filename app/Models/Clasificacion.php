<?php
/**
 * MODELO: CLASIFICACION
 * 
 * Archivo: app/Models/Clasificacion.php
 * Comando para crear: php artisan make:model Clasificacion
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clasificacion extends Model
{
    use HasFactory;

    protected $table = 'clasificaciones';

    protected $fillable = [
        'torneo_id',
        'equipo_id',
        'posicion',
        'puntos',
        'partidos_jugados',
        'ganados',
        'empatados',
        'perdidos',
        'goles_favor',
        'goles_contra',
    ];

    protected $casts = [
        'posicion' => 'integer',
        'puntos' => 'integer',
        'partidos_jugados' => 'integer',
        'ganados' => 'integer',
        'empatados' => 'integer',
        'perdidos' => 'integer',
        'goles_favor' => 'integer',
        'goles_contra' => 'integer',
    ];

    // Relaciones
    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }
}