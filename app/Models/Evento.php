<?php
/**
 * MODELO: EVENTO
 * 
 * Archivo: app/Models/Evento.php
 * Comando para crear: php artisan make:model Evento
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'eventos';

    protected $fillable = [
        'partido_id',
        'tipo',
        'minuto',
        'descripcion',
        'valor',
        'jugador_id',
    ];

    protected $casts = [
        'minuto' => 'integer',
        'valor' => 'decimal:2',
    ];

    // Relaciones
    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }

    public function jugador()
    {
        return $this->belongsTo(Jugador::class);
    }
}