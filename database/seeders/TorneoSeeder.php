<?php

/**
 * SEEDER: TORNEOS
 * 
 * Comando para crear: php artisan make:seeder TorneoSeeder
 * Archivo: database/seeders/TorneoSeeder.php
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Torneo;
use App\Models\Deporte;
use Carbon\Carbon;

class TorneoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Obtener deportes
    $futbol = Deporte::where('nombre', 'Fútbol')->first();
    $baloncesto = Deporte::where('nombre', 'Baloncesto')->first();
    $voleibol = Deporte::where('nombre', 'Voleibol')->first();

    // ================================================================
    // TORNEOS DE FÚTBOL
    // ================================================================

    $torneosFutbol = [
      [
        'nombre' => 'Liga Española 2024-2025',
        'descripcion' => 'Temporada 2024-2025 de la Primera División de España',
        'formato' => 'liga',
        'estado' => 'activo',
        'fecha_inicio' => Carbon::now()->subMonths(2),
        'fecha_fin' => Carbon::now()->addMonths(4),
        'fecha_inscripcion_limite' => Carbon::now()->subMonths(3),
        'deporte_id' => $futbol->id,
        'configuracion' => [
          'max_equipos' => 20,
          'min_equipos' => 10,
          'tipo_eliminacion' => 'liga',
          'permite_empates' => true,
          'puntos_victoria' => 3,
          'puntos_empate' => 1,
          'puntos_derrota' => 0,
          'partidos_ida_vuelta' => true,
        ],
        'premios' => [
          'primer_lugar' => '€50,000',
          'segundo_lugar' => '€30,000',
          'tercer_lugar' => '€20,000',
        ],
      ],
      [
        'nombre' => 'Copa del Rey 2024',
        'descripcion' => 'Torneo de eliminación directa de la Copa del Rey',
        'formato' => 'eliminacion',
        'estado' => 'planificacion',
        'fecha_inicio' => Carbon::now()->addMonths(1),
        'fecha_fin' => Carbon::now()->addMonths(3),
        'fecha_inscripcion_limite' => Carbon::now()->addWeeks(2),
        'deporte_id' => $futbol->id,
        'configuracion' => [
          'max_equipos' => 16,
          'min_equipos' => 8,
          'tipo_eliminacion' => 'directa',
          'permite_empates' => false,
          'desempate_penales' => true,
          'puntos_victoria' => 3,
          'puntos_derrota' => 0,
        ],
        'premios' => [
          'ganador' => '€25,000',
          'finalista' => '€15,000',
        ],
      ],
      [
        'nombre' => 'Torneo de Verano 2024',
        'descripcion' => 'Torneo amistoso de temporada baja',
        'formato' => 'grupos',
        'estado' => 'finalizado',
        'fecha_inicio' => Carbon::now()->subMonths(4),
        'fecha_fin' => Carbon::now()->subMonths(3),
        'fecha_inscripcion_limite' => Carbon::now()->subMonths(5),
        'deporte_id' => $futbol->id,
        'configuracion' => [
          'max_equipos' => 8,
          'min_equipos' => 4,
          'permite_empates' => true,
          'puntos_victoria' => 3,
          'puntos_empate' => 1,
        ],
        'premios' => [
          'ganador' => '€10,000',
        ],
      ],
    ];

    // ================================================================
    // TORNEOS DE BALONCESTO
    // ================================================================

    $torneosBaloncesto = [
      [
        'nombre' => 'Liga ACB 2024-2025',
        'descripcion' => 'Liga Endesa de Baloncesto temporada 2024-2025',
        'formato' => 'liga',
        'estado' => 'activo',
        'fecha_inicio' => Carbon::now()->subMonths(1),
        'fecha_fin' => Carbon::now()->addMonths(5),
        'fecha_inscripcion_limite' => Carbon::now()->subMonths(2),
        'deporte_id' => $baloncesto->id,
        'configuracion' => [
          'max_equipos' => 18,
          'min_equipos' => 8,
          'permite_empates' => false,
          'puntos_victoria' => 2,
          'puntos_derrota' => 0,
          'partidos_ida_vuelta' => true,
        ],
        'premios' => [
          'campeon' => '€40,000',
          'subcampeon' => '€25,000',
          'tercer_lugar' => '€15,000',
        ],
      ],
      [
        'nombre' => 'Copa del Rey Baloncesto 2024',
        'descripcion' => 'Torneo de eliminación directa de baloncesto',
        'formato' => 'eliminacion',
        'estado' => 'planificacion',
        'fecha_inicio' => Carbon::now()->addMonth(),
        'fecha_fin' => Carbon::now()->addMonths(2),
        'fecha_inscripcion_limite' => Carbon::now()->addWeeks(1),
        'deporte_id' => $baloncesto->id,
        'configuracion' => [
          'max_equipos' => 8,
          'min_equipos' => 4,
          'tipo_eliminacion' => 'directa',
          'permite_empates' => false,
          'puntos_victoria' => 2,
          'puntos_derrota' => 0,
        ],
        'premios' => [
          'ganador' => '€20,000',
          'finalista' => '€12,000',
        ],
      ],
    ];

    // ================================================================
    // TORNEOS DE VOLEIBOL
    // ================================================================

    $torneosVoleibol = [
      [
        'nombre' => 'Liga Nacional de Voleibol 2024',
        'descripcion' => 'Campeonato nacional de voleibol',
        'formato' => 'liga',
        'estado' => 'activo',
        'fecha_inicio' => Carbon::now()->subWeeks(6),
        'fecha_fin' => Carbon::now()->addMonths(3),
        'fecha_inscripcion_limite' => Carbon::now()->subMonths(2),
        'deporte_id' => $voleibol->id,
        'configuracion' => [
          'max_equipos' => 12,
          'min_equipos' => 6,
          'permite_empates' => false,
          'puntos_victoria' => 3,
          'puntos_derrota' => 1, // Por sets ganados
          'partidos_ida_vuelta' => true,
        ],
        'premios' => [
          'campeon' => '€15,000',
          'subcampeon' => '€10,000',
        ],
      ],
    ];

    // ================================================================
    // CREAR TODOS LOS TORNEOS
    // ================================================================

    $todosLosTorneos = array_merge($torneosFutbol, $torneosBaloncesto, $torneosVoleibol);

    foreach ($todosLosTorneos as $torneoData) {
      Torneo::create($torneoData);
    }

    $this->command->info('✅ Torneos creados: ' . count($todosLosTorneos));
  }
}
