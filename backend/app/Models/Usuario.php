<?php
/**
 * MODELO: USUARIO
 * 
 * Archivo: app/Models/Usuario.php
 * Comando para crear: php artisan make:model Usuario
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'telefono',
        'fecha_nacimiento',
        'avatar',
        'tipo_usuario',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'activo' => 'boolean',
        'password' => 'hashed',
    ];

    // Relaciones
    public function jugador()
    {
        return $this->hasOne(Jugador::class);
    }

    public function arbitro()
    {
        return $this->hasOne(Arbitro::class);
    }

    public function administrador()
    {
        return $this->hasOne(Administrador::class);
    }

    public function equiposAdministrados()
    {
        return $this->hasMany(Equipo::class, 'administrador_id');
    }

    public function invitacionesEnviadas()
    {
        return $this->hasMany(Invitacion::class, 'enviado_por');
    }
}