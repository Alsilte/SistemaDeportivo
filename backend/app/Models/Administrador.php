<?php
/**
 * MODELO: ADMINISTRADOR
 * 
 * Archivo: app/Models/Administrador.php
 * Comando para crear: php artisan make:model Administrador
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Administrador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'administradores';

    protected $fillable = [
        'usuario_id',
        'permisos',
        'nivel_acceso',
    ];

    protected $casts = [
        'permisos' => 'array',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'administrador_id', 'usuario_id');
    }
}