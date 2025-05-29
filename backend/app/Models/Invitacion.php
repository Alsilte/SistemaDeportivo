<?php
/**
 * MODELO: INVITACION
 * 
 * Archivo: app/Models/Invitacion.php
 * Comando para crear: php artisan make:model Invitacion
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitacion extends Model
{
    use HasFactory;

    protected $table = 'invitaciones';

    protected $fillable = [
        'email',
        'token',
        'estado',
        'torneo_id',
        'equipo_id',
        'enviado_por',
        'fecha_envio',
        'fecha_expiracion',
        'fecha_respuesta',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
        'fecha_expiracion' => 'datetime',
        'fecha_respuesta' => 'datetime',
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

    public function remitente()
    {
        return $this->belongsTo(Usuario::class, 'enviado_por');
    }
}