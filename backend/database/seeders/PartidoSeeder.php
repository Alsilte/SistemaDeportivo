<?php

/**
 * SEEDER: PARTIDOS
 * 
 * Comando: php artisan make:seeder PartidoSeeder
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partido;
use App\Models\Torneo;
use App\Models\Arbitro;
use Carbon\Carbon;

class PartidoSeeder extends Seeder
{
  public function run(): void
  {
    $this->crearPartidosLiga();
    $this->crearPartidosCopa();
  }

  private function crearPartidosLiga(): void
  {
    $liga = Torneo::where('nombre', 'Liga Española 2024-2025')->first();
    $equipos = $liga->equipos()->get();
    $arbitros = Arbitro::all();

    // Crear algunos partidos de ejemplo
    $partidos = [
      [$equipos[0], $equipos[1]], // Real Madrid vs Barcelona
      [$equipos[2], $equipos[3]], // Atlético vs Sevilla
      [$equipos[1], $equipos[2]], // Barcelona vs Atlético
      [$equipos[0], $equipos[3]], // Real Madrid vs Sevilla
    ];

    foreach ($partidos as $index => $partidoEquipos) {
      $fechaPartido = now()->subWeeks(rand(1, 8))->addDays(rand(0, 6));

      Partido::create([
        'torneo_id' => $liga->id,
        'equipo_local_id' => $partidoEquipos[0]->id,
        'equipo_visitante_id' => $partidoEquipos[1]->id,
        'fecha' => $fechaPartido,
        'lugar' => 'Estadio Santiago Bernabéu',
        'estado' => 'finalizado',
        'goles_local' => rand(0, 4),
        'goles_visitante' => rand(0, 4),
        'arbitro_id' => $arbitros->random()->id,
        'observaciones' => 'Partido de liga regular',
      ]);
    }
  }

  private function crearPartidosCopa(): void
  {
    $copa = Torneo::where('nombre', 'Copa del Rey 2024')->first();
    $equipos = $copa->equipos()->get();
    $arbitros = Arbitro::all();

    // Crear partidos de primera ronda
    for ($i = 0; $i < $equipos->count(); $i += 2) {
      if (isset($equipos[$i + 1])) {
        Partido::create([
          'torneo_id' => $copa->id,
          'equipo_local_id' => $equipos[$i]->id,
          'equipo_visitante_id' => $equipos[$i + 1]->id,
          'fecha' => now()->addMonths(1)->addDays(rand(1, 10)),
          'lugar' => 'Campo Municipal',
          'estado' => 'programado',
          'goles_local' => 0,
          'goles_visitante' => 0,
          'arbitro_id' => $arbitros->random()->id,
        ]);
      }
    }
  }
}
