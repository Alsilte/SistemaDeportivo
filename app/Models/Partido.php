<?php
/**
 * MODELO: PARTIDO
 * 
 * Archivo: app/Models/Partido.php
 * Comando para crear: php artisan make:model Partido
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partido extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'partidos';

    protected $fillable = [
        'torneo_id',
        'equipo_local_id',
        'equipo_visitante_id',
        'fecha',
        'lugar',
        'estado',
        'resultado',
        'arbitro_id',
        'goles_local',
        'goles_visitante',
        'estadisticas',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'goles_local' => 'integer',
        'goles_visitante' => 'integer',
        'estadisticas' => 'array',
    ];

    // Relaciones
    public function torneo()
    {
        return $this->belongsTo(Torneo::class);
    }

    public function equipoLocal()
    {
        return $this->belongsTo(Equipo::class, 'equipo_local_id');
    }

    public function equipoVisitante()
    {
        return $this->belongsTo(Equipo::class, 'equipo_visitante_id');
    }

    public function arbitro()
    {
        return $this->belongsTo(Arbitro::class);
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }
}