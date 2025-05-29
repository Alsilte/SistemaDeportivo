<?php
/**
 * MODELO: ARBITRO
 * 
 * Archivo: app/Models/Arbitro.php
 * Comando para crear: php artisan make:model Arbitro
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Arbitro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'arbitros';

    protected $fillable = [
        'usuario_id',
        'licencia',
        'posicion',
        'fecha_nacimiento',
        'partidos_arbitrados',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'partidos_arbitrados' => 'integer',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function partidos()
    {
        return $this->hasMany(Partido::class);
    }
}