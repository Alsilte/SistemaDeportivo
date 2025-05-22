<?php

/**
 * SEEDER: EQUIPO-TORNEO (Relaciones)
 * 
 * Comando: php artisan make:seeder EquipoTorneoSeeder
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipo;
use App\Models\Torneo;
use Carbon\Carbon;

class EquipoTorneoSeeder extends Seeder
{
  public function run(): void
  {
    // Inscribir equipos en torneos
    $this->inscribirEquiposFutbol();
    $this->inscribirEquiposBaloncesto();
    $this->inscribirEquiposVoleibol();
  }

  private function inscribirEquiposFutbol(): void
  {
    $liga = Torneo::where('nombre', 'Liga Española 2024-2025')->first();
    $copa = Torneo::where('nombre', 'Copa del Rey 2024')->first();

    $equiposFutbol = Equipo::whereHas('deporte', function ($q) {
      $q->where('nombre', 'Fútbol');
    })->get();

    // Inscribir en Liga (todos los equipos)
    foreach ($equiposFutbol as $equipo) {
      $liga->equipos()->attach($equipo->id, [
        'fecha_inscripcion' => now()->subMonths(3),
        'estado_participacion' => 'confirmado',
        'email_contacto' => $equipo->email,
        'telefono_contacto' => $equipo->telefono,
      ]);
    }

    // Inscribir en Copa (primeros 8 equipos)
    foreach ($equiposFutbol->take(8) as $equipo) {
      $copa->equipos()->attach($equipo->id, [
        'fecha_inscripcion' => now()->subWeeks(3),
        'estado_participacion' => 'inscrito',
        'email_contacto' => $equipo->email,
      ]);
    }
  }

  private function inscribirEquiposBaloncesto(): void
  {
    $liga = Torneo::where('nombre', 'Liga ACB 2024-2025')->first();

    $equiposBaloncesto = Equipo::whereHas('deporte', function ($q) {
      $q->where('nombre', 'Baloncesto');
    })->get();

    foreach ($equiposBaloncesto as $equipo) {
      $liga->equipos()->attach($equipo->id, [
        'fecha_inscripcion' => now()->subMonths(2),
        'estado_participacion' => 'confirmado',
        'email_contacto' => $equipo->email,
        'telefono_contacto' => $equipo->telefono,
      ]);
    }
  }

  private function inscribirEquiposVoleibol(): void
  {
    $liga = Torneo::where('nombre', 'Liga Nacional de Voleibol 2024')->first();

    $equiposVoleibol = Equipo::whereHas('deporte', function ($q) {
      $q->where('nombre', 'Voleibol');
    })->get();

    foreach ($equiposVoleibol as $equipo) {
      $liga->equipos()->attach($equipo->id, [
        'fecha_inscripcion' => now()->subMonths(2),
        'estado_participacion' => 'confirmado',
        'email_contacto' => $equipo->email,
      ]);
    }
  }
}
