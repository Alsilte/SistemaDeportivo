<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition()
    {
        return [
            'nombre'            => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'password'          => 'password', // se casteará a hash gracias al $casts
            'telefono'          => $this->faker->phoneNumber(),
            'fecha_nacimiento'  => $this->faker->date(),
            'avatar'            => null,
            'tipo_usuario'      => 'jugador', // o el default que uses
            'activo'            => true,
            'remember_token'    => Str::random(10),
        ];
    }
}
