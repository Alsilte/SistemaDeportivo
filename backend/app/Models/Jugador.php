<?php
/**
 * MODELO: JUGADOR
 * 
 * Archivo: app/Models/Jugador.php
 * Comando para crear: php artisan make:model Jugador
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jugador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jugadores';

    protected $fillable = [
        'usuario_id',
        'posicion',
        'numero_camiseta',
        'puntos',
        'partidos_jugados',
        'goles_favor',
        'goles_contra',
        'empatados',
        'ganados',
        'perdidos',
    ];

    protected $casts = [
        'puntos' => 'integer',
        'partidos_jugados' => 'integer',
        'goles_favor' => 'integer',
        'goles_contra' => 'integer',
        'empatados' => 'integer',
        'ganados' => 'integer',
        'perdidos' => 'integer',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function equipos()
    {
        return $this->belongsToMany(Equipo::class, 'jugador_equipo');
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }
}