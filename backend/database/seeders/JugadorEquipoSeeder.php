<?php

/**
 * SEEDER: JUGADOR-EQUIPO (Relaciones)
 * 
 * Comando para crear: php artisan make:seeder JugadorEquipoSeeder
 * Archivo: database/seeders/JugadorEquipoSeeder.php
 * 
 * Este seeder establece las relaciones entre jugadores y equipos
 * a través de la tabla pivot jugador_equipo
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jugador;
use App\Models\Equipo;
use App\Models\Usuario;
use Carbon\Carbon;

class JugadorEquipoSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->info('🔗 Iniciando asignación de jugadores a equipos...');

    // Asignar jugadores a equipos por deporte
    $this->asignarJugadoresFutbol();
    $this->asignarJugadoresBaloncesto();
    $this->asignarJugadoresVoleibol();

    $this->command->info('✅ Relaciones jugador-equipo creadas exitosamente');
  }

  /**
   * Asignar jugadores de fútbol a equipos de fútbol
   */
  private function asignarJugadoresFutbol(): void
  {
    // Obtener equipos de fútbol
    $equiposFutbol = Equipo::whereHas('deporte', function ($query) {
      $query->where('nombre', 'Fútbol');
    })->get();

    // Jugadores específicos de fútbol por email
    $jugadoresFutbolEmails = [
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

    $this->command->info('⚽ Asignando jugadores de fútbol...');

    foreach ($jugadoresFutbolEmails as $index => $email) {
      // Buscar el jugador por email del usuario
      $jugador = Jugador::whereHas('usuario', function ($query) use ($email) {
        $query->where('email', $email);
      })->first();

      if ($jugador && $equiposFutbol->count() > 0) {
        // Asignar a un equipo de forma rotativa
        $equipo = $equiposFutbol[$index % $equiposFutbol->count()];

        // Verificar si ya está asignado a este equipo
        if (!$equipo->jugadores()->where('jugador_id', $jugador->id)->exists()) {
          $equipo->jugadores()->attach($jugador->id, [
            'numero_camiseta' => $this->generarNumeroCamiseta($equipo),
            'posicion' => $jugador->posicion ?? 'Centrocampista',
            'estado' => 'activo',
            'es_capitan' => $index === 0, // Primer jugador es capitán
            'es_titular' => rand(0, 1) === 1,
            'fecha_incorporacion' => Carbon::now()->subDays(rand(30, 365)),
            'partidos_jugados' => rand(0, 25),
            'goles_marcados' => rand(0, 20),
            'asistencias' => rand(0, 15),
            'tarjetas_amarillas' => rand(0, 8),
            'tarjetas_rojas' => rand(0, 2),
            'created_at' => now(),
            'updated_at' => now(),
          ]);

          $this->command->info("   ✓ {$jugador->usuario->nombre} → {$equipo->nombre}");
        }
      }
    }
  }

  /**
   * Asignar jugadores de baloncesto a equipos de baloncesto
   */
  private function asignarJugadoresBaloncesto(): void
  {
    // Obtener equipos de baloncesto
    $equiposBaloncesto = Equipo::whereHas('deporte', function ($query) {
      $query->where('nombre', 'Baloncesto');
    })->get();

    // Jugadores específicos de baloncesto por email
    $jugadoresBaloncestoEmails = [
      'lebron@sistema.com',
      'curry@sistema.com',
      'durant@sistema.com',
      'giannis@sistema.com',
      'sofia.rodriguez@sistema.com',
    ];

    $this->command->info('🏀 Asignando jugadores de baloncesto...');

    foreach ($jugadoresBaloncestoEmails as $index => $email) {
      // Buscar el jugador por email del usuario
      $jugador = Jugador::whereHas('usuario', function ($query) use ($email) {
        $query->where('email', $email);
      })->first();

      if ($jugador && $equiposBaloncesto->count() > 0) {
        // Asignar a un equipo de forma rotativa
        $equipo = $equiposBaloncesto[$index % $equiposBaloncesto->count()];

        // Verificar si ya está asignado a este equipo
        if (!$equipo->jugadores()->where('jugador_id', $jugador->id)->exists()) {
          $equipo->jugadores()->attach($jugador->id, [
            'numero_camiseta' => $this->generarNumeroCamiseta($equipo),
            'posicion' => $jugador->posicion ?? 'Alero',
            'estado' => 'activo',
            'es_capitan' => $index === 0, // Primer jugador es capitán
            'es_titular' => rand(0, 1) === 1,
            'fecha_incorporacion' => Carbon::now()->subDays(rand(30, 365)),
            'partidos_jugados' => rand(0, 30),
            'goles_marcados' => rand(0, 35), // Puntos anotados en baloncesto
            'asistencias' => rand(0, 20),
            'tarjetas_amarillas' => rand(0, 5), // Faltas técnicas
            'tarjetas_rojas' => rand(0, 1), // Expulsiones
            'created_at' => now(),
            'updated_at' => now(),
          ]);

          $this->command->info("   ✓ {$jugador->usuario->nombre} → {$equipo->nombre}");
        }
      }
    }
  }

  /**
   * Asignar jugadores de voleibol a equipos de voleibol
   */
  private function asignarJugadoresVoleibol(): void
  {
    // Obtener equipos de voleibol
    $equiposVoleibol = Equipo::whereHas('deporte', function ($query) {
      $query->where('nombre', 'Voleibol');
    })->get();

    // Obtener jugadores sin equipo asignado aún
    $jugadoresSinEquipo = Jugador::whereDoesntHave('equipos')->get();

    $this->command->info('🏐 Asignando jugadores de voleibol...');

    foreach ($jugadoresSinEquipo->take(12) as $index => $jugador) {
      if ($equiposVoleibol->count() > 0) {
        // Asignar a un equipo de forma rotativa
        $equipo = $equiposVoleibol[$index % $equiposVoleibol->count()];

        $equipo->jugadores()->attach($jugador->id, [
          'numero_camiseta' => $this->generarNumeroCamiseta($equipo),
          'posicion' => $this->asignarPosicionVoleibol(),
          'estado' => 'activo',
          'es_capitan' => $index % 6 === 0, // Un capitán cada 6 jugadores
          'es_titular' => rand(0, 1) === 1,
          'fecha_incorporacion' => Carbon::now()->subDays(rand(30, 365)),
          'partidos_jugados' => rand(0, 20),
          'goles_marcados' => rand(0, 15), // Puntos en voleibol
          'asistencias' => rand(0, 25), // Asistencias/servicios
          'tarjetas_amarillas' => rand(0, 3),
          'tarjetas_rojas' => rand(0, 1),
          'created_at' => now(),
          'updated_at' => now(),
        ]);

        $this->command->info("   ✓ {$jugador->usuario->nombre} → {$equipo->nombre}");
      }
    }
  }

  /**
   * Generar número de camiseta único para el equipo
   * 
   * @param Equipo $equipo
   * @return int
   */
  private function generarNumeroCamiseta(Equipo $equipo): int
  {
    $numerosUsados = $equipo->jugadores()
      ->wherePivot('numero_camiseta', '!=', null)
      ->pluck('jugador_equipo.numero_camiseta')
      ->toArray();

    // Generar número del 1 al 99 que no esté usado
    do {
      $numero = rand(1, 99);
    } while (in_array($numero, $numerosUsados));

    return $numero;
  }

  /**
   * Asignar posición específica para voleibol
   * 
   * @return string
   */
  private function asignarPosicionVoleibol(): string
  {
    $posicionesVoleibol = [
      'Colocador',
      'Líbero',
      'Atacante',
      'Bloqueador Central',
      'Receptor-Atacante',
      'Opuesto'
    ];

    return $posicionesVoleibol[array_rand($posicionesVoleibol)];
  }

  /**
   * Crear equipos adicionales si es necesario
   */
  private function crearEquiposAdicionales(): void
  {
    // Verificar si necesitamos más equipos para distribuir mejor a los jugadores
    $jugadoresTotales = Jugador::count();
    $equiposTotales = Equipo::count();

    if ($jugadoresTotales > $equiposTotales * 15) {
      $this->command->info('⚠️  Se detectaron muchos jugadores sin asignar');
      $this->command->info('💡 Considera crear más equipos o ajustar la distribución');
    }
  }
}

/**
 * INSTRUCCIONES PARA EJECUTAR:
 * 
 * 1. Ejecutar seeder individual:
 *    php artisan db:seed --class=JugadorEquipoSeeder
 * 
 * 2. Ejecutar todos los seeders (recomendado):
 *    php artisan db:seed
 * 
 * 3. Refrescar base de datos y ejecutar seeders:
 *    php artisan migrate:fresh --seed
 * 
 * NOTAS IMPORTANTES:
 * - Este seeder debe ejecutarse DESPUÉS de UsuarioSeeder y EquipoSeeder
 * - Maneja la asignación de números de camiseta únicos por equipo
 * - Distribuye jugadores de forma balanceada entre equipos del mismo deporte
 * - Asigna posiciones específicas según el deporte
 * - Genera estadísticas aleatorias realistas para cada jugador
 */
