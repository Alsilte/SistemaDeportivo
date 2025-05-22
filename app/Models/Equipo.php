<?php
/**
 * MODELO: EQUIPO
 * 
 * Archivo: app/Models/Equipo.php
 * Comando para crear: php artisan make:model Equipo
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipos';

    protected $fillable = [
        'nombre',
        'logo',
        'email',
        'telefono',
        'deporte_id',
        'administrador_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relaciones
    public function deporte()
    {
        return $this->belongsTo(Deporte::class);
    }

    public function administrador()
    {
        return $this->belongsTo(Usuario::class, 'administrador_id');
    }

    public function jugadores()
    {
        return $this->belongsToMany(Jugador::class, 'jugador_equipo');
    }

    public function torneos()
    {
        return $this->belongsToMany(Torneo::class, 'equipo_torneo');
    }

    public function partidosLocal()
    {
        return $this->hasMany(Partido::class, 'equipo_local_id');
    }

    public function partidosVisitante()
    {
        return $this->hasMany(Partido::class, 'equipo_visitante_id');
    }

    public function clasificaciones()
    {
        return $this->hasMany(Clasificacion::class);
    }
}