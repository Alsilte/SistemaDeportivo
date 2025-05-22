<?php

/**
 * SEEDER: USUARIOS
 * 
 * Comando para crear: php artisan make:seeder UsuarioSeeder
 * Archivo: database/seeders/UsuarioSeeder.php
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Jugador;
use App\Models\Arbitro;
use App\Models\Administrador;

class UsuarioSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // ================================================================
    // CREAR ADMINISTRADORES
    // ================================================================

    // Super Admin
    $superAdmin = Usuario::create([
      'nombre' => 'Super Administrador',
      'email' => 'admin@sistema.com',
      'password' => Hash::make('password'),
      'telefono' => '+34 600 000 001',
      'tipo_usuario' => 'administrador',
      'activo' => true,
    ]);

    Administrador::create([
      'usuario_id' => $superAdmin->id,
      'nivel_acceso' => 'super_admin',
      'permisos' => ['*'], // Todos los permisos
    ]);

    // Admin Normal
    $admin = Usuario::create([
      'nombre' => 'Carlos Administrador',
      'email' => 'carlos.admin@sistema.com',
      'password' => Hash::make('password'),
      'telefono' => '+34 600 000 002',
      'tipo_usuario' => 'administrador',
      'activo' => true,
    ]);

    Administrador::create([
      'usuario_id' => $admin->id,
      'nivel_acceso' => 'admin',
      'permisos' => ['create_torneo', 'edit_torneo', 'create_equipo', 'edit_equipo'],
    ]);

    // ================================================================
    // CREAR ÁRBITROS
    // ================================================================

    $arbitros = [
      ['nombre' => 'Pedro Árbitro', 'email' => 'pedro.arbitro@sistema.com', 'licencia' => 'ARB001'],
      ['nombre' => 'Ana Arbitro', 'email' => 'ana.arbitro@sistema.com', 'licencia' => 'ARB002'],
      ['nombre' => 'Luis Arbitro', 'email' => 'luis.arbitro@sistema.com', 'licencia' => 'ARB003'],
    ];

    foreach ($arbitros as $arbitroData) {
      $usuario = Usuario::create([
        'nombre' => $arbitroData['nombre'],
        'email' => $arbitroData['email'],
        'password' => Hash::make('password'),
        'telefono' => '+34 600 00' . rand(1000, 9999),
        'fecha_nacimiento' => now()->subYears(rand(25, 45)),
        'tipo_usuario' => 'arbitro',
        'activo' => true,
      ]);

      Arbitro::create([
        'usuario_id' => $usuario->id,
        'licencia' => $arbitroData['licencia'],
        'posicion' => 'principal',
        'fecha_nacimiento' => now()->subYears(rand(25, 45)),
        'partidos_arbitrados' => rand(10, 50),
      ]);
    }

    // ================================================================
    // CREAR JUGADORES
    // ================================================================

    $jugadores = [
      // Jugadores de Fútbol
      ['nombre' => 'Lionel Messi', 'email' => 'messi@sistema.com', 'posicion' => 'Delantero'],
      ['nombre' => 'Cristiano Ronaldo', 'email' => 'cristiano@sistema.com', 'posicion' => 'Delantero'],
      ['nombre' => 'Neymar Jr', 'email' => 'neymar@sistema.com', 'posicion' => 'Extremo'],
      ['nombre' => 'Luka Modric', 'email' => 'modric@sistema.com', 'posicion' => 'Centrocampista'],
      ['nombre' => 'Sergio Ramos', 'email' => 'ramos@sistema.com', 'posicion' => 'Defensa'],
      ['nombre' => 'Manuel Neuer', 'email' => 'neuer@sistema.com', 'posicion' => 'Portero'],

      // Jugadores de Baloncesto
      ['nombre' => 'LeBron James', 'email' => 'lebron@sistema.com', 'posicion' => 'Alero'],
      ['nombre' => 'Stephen Curry', 'email' => 'curry@sistema.com', 'posicion' => 'Base'],
      ['nombre' => 'Kevin Durant', 'email' => 'durant@sistema.com', 'posicion' => 'Alero'],
      ['nombre' => 'Giannis Antetokounmpo', 'email' => 'giannis@sistema.com', 'posicion' => 'Ala-Pívot'],

      // Más jugadores genéricos
      ['nombre' => 'Alex García', 'email' => 'alex.garcia@sistema.com', 'posicion' => 'Centrocampista'],
      ['nombre' => 'María López', 'email' => 'maria.lopez@sistema.com', 'posicion' => 'Delantera'],
      ['nombre' => 'David Martín', 'email' => 'david.martin@sistema.com', 'posicion' => 'Defensa'],
      ['nombre' => 'Sofia Rodríguez', 'email' => 'sofia.rodriguez@sistema.com', 'posicion' => 'Base'],
      ['nombre' => 'Miguel Sánchez', 'email' => 'miguel.sanchez@sistema.com', 'posicion' => 'Portero'],
    ];

    foreach ($jugadores as $jugadorData) {
      $usuario = Usuario::create([
        'nombre' => $jugadorData['nombre'],
        'email' => $jugadorData['email'],
        'password' => Hash::make('password'),
        'telefono' => '+34 600 00' . rand(1000, 9999),
        'fecha_nacimiento' => now()->subYears(rand(18, 35)),
        'tipo_usuario' => 'jugador',
        'activo' => true,
      ]);

      Jugador::create([
        'usuario_id' => $usuario->id,
        'posicion' => $jugadorData['posicion'],
        'puntos' => rand(0, 100),
        'partidos_jugados' => rand(0, 50),
        'goles_favor' => rand(0, 30),
        'goles_contra' => rand(0, 20),
        'ganados' => rand(0, 30),
        'empatados' => rand(0, 10),
        'perdidos' => rand(0, 15),
      ]);
    }
  }
}
