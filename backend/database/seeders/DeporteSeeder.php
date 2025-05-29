<?php

/**
 * SEEDER: DEPORTES
 * 
 * Comando para crear: php artisan make:seeder DeporteSeeder
 * Archivo: database/seeders/DeporteSeeder.php
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deporte;

class DeporteSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $deportes = [
      [
        'nombre' => 'Fútbol',
        'descripcion' => 'Deporte jugado entre dos equipos de once jugadores cada uno',
        'configuracion_json' => [
          'jugadores_por_equipo' => 11,
          'duracion_partido' => 90,
          'permite_empates' => true,
          'puntos_victoria' => 3,
          'puntos_empate' => 1,
          'puntos_derrota' => 0,
        ],
        'activo' => true,
      ],
      [
        'nombre' => 'Baloncesto',
        'descripcion' => 'Deporte jugado entre dos equipos de cinco jugadores cada uno',
        'configuracion_json' => [
          'jugadores_por_equipo' => 5,
          'duracion_partido' => 48,
          'permite_empates' => false,
          'puntos_victoria' => 2,
          'puntos_empate' => 0,
          'puntos_derrota' => 0,
        ],
        'activo' => true,
      ],
      [
        'nombre' => 'Voleibol',
        'descripcion' => 'Deporte jugado entre dos equipos de seis jugadores cada uno',
        'configuracion_json' => [
          'jugadores_por_equipo' => 6,
          'duracion_partido' => 0, // Por sets
          'permite_empates' => false,
          'puntos_victoria' => 3,
          'puntos_empate' => 0,
          'puntos_derrota' => 1,
        ],
        'activo' => true,
      ],
      [
        'nombre' => 'Tenis',
        'descripcion' => 'Deporte individual o de parejas',
        'configuracion_json' => [
          'jugadores_por_equipo' => 1,
          'duracion_partido' => 0, // Por sets
          'permite_empates' => false,
          'puntos_victoria' => 2,
          'puntos_empate' => 0,
          'puntos_derrota' => 0,
        ],
        'activo' => true,
      ],
    ];

    foreach ($deportes as $deporte) {
      Deporte::create($deporte);
    }
  }
}
