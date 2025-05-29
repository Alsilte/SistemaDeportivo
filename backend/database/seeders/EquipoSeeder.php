<?php

/**
 * SEEDER: EQUIPOS
 * 
 * Comando para crear: php artisan make:seeder EquipoSeeder
 * Archivo: database/seeders/EquipoSeeder.php
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipo;
use App\Models\Deporte;
use App\Models\Usuario;
use App\Models\Jugador;

class EquipoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Obtener deportes y administradores
    $futbol = Deporte::where('nombre', 'Fútbol')->first();
    $baloncesto = Deporte::where('nombre', 'Baloncesto')->first();
    $voleibol = Deporte::where('nombre', 'Voleibol')->first();

    $admin = Usuario::where('email', 'carlos.admin@sistema.com')->first();

    // ================================================================
    // EQUIPOS DE FÚTBOL
    // ================================================================

    $equiposFutbol = [
      [
        'nombre' => 'Real Madrid',
        'email' => 'info@realmadrid.com',
        'telefono' => '+34 91 344 00 52',
        'deporte_id' => $futbol->id,
        'administrador_id' => $admin->id,
      ],
      [
        'nombre' => 'FC Barcelona',
        'email' => 'info@fcbarcelona.com',
        'telefono' => '+34 93 496 36 00',
        'deporte_id' => $futbol->id,
        'administrador_id' => $admin->id,
      ],
      [
        'nombre' => 'Atlético Madrid',
        'email' => 'info@atleticomadrid.com',
        'telefono' => '+34 91 366 47 07',
        'deporte_id' => $futbol->id,
        'administrador_id' => $admin->id,
      ],
      [
        'nombre' => 'Sevilla FC',
        'email' => 'info@sevillafc.com',
        'telefono' => '+34 95 453 53 53',
        'deporte_id' => $futbol->id,
        'administrador_id' => $admin->id,
      ],
      [
        'nombre' => 'Valencia CF',
        'email' => 'info@valenciacf.com',
        'telefono' => '+34 96 337 26 26',
        'deporte_id' => $futbol->id,
        'administrador_id' => $admin->id,
      ],
      [
        'nombre' => 'Athletic Bilbao',
        'email' => 'info@athletic-club.com',
        'telefono' => '+34 94 441 14 45',
        'deporte_id' => $futbol->id,
        'administrador_id' => $admin->id,
      ],
    ];

    // ================================================================
    // EQUIPOS DE BALONCESTO
    // ================================================================

    $equiposBaloncesto = [
      [
        'nombre' => 'Real Madrid Baloncesto',
        'email' => 'basket@realmadrid.com',
        'telefono' => '+34 91 344 00 53',
        'deporte_id' => $baloncesto->id,
        'administrador_id' => $admin->id,
      ],
      [
        'nombre' => 'FC Barcelona Basket',
        'email' => 'basket@fcbarcelona.com',
        'telefono' => '+34 93 496 36 01',
        'deporte_id' => $baloncesto->id,
        'administrador_id' => $admin->id,
      ],
      [
        'nombre' => 'Baskonia',
        'email' => 'info@baskonia.com',
        'telefono' => '+34 945 16 40 00',
        'deporte_id' => $baloncesto->id,
        'administrador_id' => $admin->id,
      ],
      [
        'nombre' => 'Valencia Basket',
        'email' => 'info@valenciabasket.com',
        'telefono' => '+34 96 337 26 27',
        'deporte_id' => $baloncesto->id,
        'administrador_id' => $admin->id,
      ],
    ];

    // ================================================================
    // EQUIPOS DE VOLEIBOL
    // ================================================================

    $equiposVoleibol = [
      [
        'nombre' => 'CV Teruel',
        'email' => 'info@cvteruel.com',
        'telefono' => '+34 978 60 10 90',
        'deporte_id' => $voleibol->id,
        'administrador_id' => $admin->id,
      ],
      [
        'nombre' => 'Unicaja Almería',
        'email' => 'info@unicajaalmeria.com',
        'telefono' => '+34 950 25 25 25',
        'deporte_id' => $voleibol->id,
        'administrador_id' => $admin->id,
      ],
    ];

    // ================================================================
    // CREAR EQUIPOS
    // ================================================================

    $todosLosEquipos = array_merge($equiposFutbol, $equiposBaloncesto, $equiposVoleibol);

    foreach ($todosLosEquipos as $equipoData) {
      Equipo::create($equipoData);
    }

    // ================================================================
    // ASIGNAR JUGADORES A EQUIPOS
    // ================================================================

    $this->asignarJugadoresAEquipos();
  }

  /**
   * Asignar jugadores a equipos
   */
  private function asignarJugadoresAEquipos(): void
  {
    // Obtener equipos y jugadores
    $equiposFutbol = Equipo::whereHas('deporte', function ($q) {
      $q->where('nombre', 'Fútbol');
    })->get();

    $equiposBaloncesto = Equipo::whereHas('deporte', function ($q) {
      $q->where('nombre', 'Baloncesto');
    })->get();

    // Jugadores de fútbol (por email)
    $jugadoresFutbol = [
      'messi@sistema.com',
      'cristiano@sistema.com',
      'neymar@sistema.com',
      'modric@sistema.com',
      'ramos@sistema.com',
      'neuer@sistema.com',
      'alex.garcia@sistema.com',
      'maria.lopez@sistema.com',
      'david.martin@sistema.com',
      'miguel.sanchez@sistema.com',
    ];

    // Jugadores de baloncesto (por email)
    $jugadoresBaloncesto = [
      'lebron@sistema.com',
      'curry@sistema.com',
      'durant@sistema.com',
      'giannis@sistema.com',
      'sofia.rodriguez@sistema.com',
    ];

    // Asignar jugadores de fútbol a equipos de fútbol
    foreach ($jugadoresFutbol as $index => $email) {
      $jugador = Jugador::whereHas('usuario', function ($q) use ($email) {
        $q->where('email', $email);
      })->first();

      if ($jugador) {
        $equipo = $equiposFutbol[$index % $equiposFutbol->count()];

        $equipo->jugadores()->attach($jugador->id, [
          'numero_camiseta' => rand(1, 99),
          'posicion' => $jugador->posicion,
          'estado' => 'activo',
          'es_capitan' => $index === 0, // Primer jugador es capitán
          'es_titular' => rand(0, 1) === 1,
          'fecha_incorporacion' => now()->subDays(rand(30, 365)),
          'partidos_jugados' => rand(0, 20),
          'goles_marcados' => rand(0, 15),
          'asistencias' => rand(0, 10),
          'tarjetas_amarillas' => rand(0, 5),
          'tarjetas_rojas' => rand(0, 2),
        ]);
      }
    }

    // Asignar jugadores de baloncesto a equipos de baloncesto
    foreach ($jugadoresBaloncesto as $index => $email) {
      $jugador = Jugador::whereHas('usuario', function ($q) use ($email) {
        $q->where('email', $email);
      })->first();

      if ($jugador) {
        $equipo = $equiposBaloncesto[$index % $equiposBaloncesto->count()];

        $equipo->jugadores()->attach($jugador->id, [
          'numero_camiseta' => rand(1, 99),
          'posicion' => $jugador->posicion,
          'estado' => 'activo',
          'es_capitan' => $index === 0,
          'es_titular' => rand(0, 1) === 1,
          'fecha_incorporacion' => now()->subDays(rand(30, 365)),
          'partidos_jugados' => rand(0, 20),
          'goles_marcados' => rand(0, 25), // Puntos en baloncesto
          'asistencias' => rand(0, 15),
          'tarjetas_amarillas' => rand(0, 3),
          'tarjetas_rojas' => rand(0, 1),
        ]);
      }
    }
  }
}
