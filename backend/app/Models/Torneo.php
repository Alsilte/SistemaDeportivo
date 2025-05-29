<?php
/**
 * MODELO: TORNEO
 * 
 * Archivo: app/Models/Torneo.php
 * Comando para crear: php artisan make:model Torneo
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Torneo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'torneos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'formato',
        'estado',
        'fecha_inicio',
        'fecha_fin',
        'fecha_inscripcion_limite',
        'deporte_id',
        'configuracion',
        'premios',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_inscripcion_limite' => 'datetime',
        'configuracion' => 'array',
        'premios' => 'array',
    ];

    // Relaciones
    public function deporte()
    {
        return $this->belongsTo(Deporte::class);
    }

    public function equipos()
    {
        return $this->belongsToMany(Equipo::class, 'equipo_torneo');
    }

    public function partidos()
    {
        return $this->hasMany(Partido::class);
    }

    public function clasificacion()
    {
        return $this->hasMany(Clasificacion::class);
    }

    public function invitaciones()
    {
        return $this->hasMany(Invitacion::class);
    }
}