<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DeporteSeeder;

class ApiTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    // Poblar deportes (id=1,…)
    $this->seed(DeporteSeeder::class);
  }

  /** @test */
  public function crud_de_equipos_requiere_autenticacion()
  {
    // Registro
    $registro = $this->postJson('/api/auth/register', [
      'nombre'                 => 'AutoTester',
      'email'                  => 'autotester@example.com',
      'password'               => 'secret123',
      'password_confirmation'  => 'secret123',
      'tipo_usuario'           => 'jugador',
    ]);
    $registro->assertStatus(201)
      ->assertJsonPath('data.email', 'autotester@example.com');

    // Login
    $login = $this->postJson('/api/auth/login', [
      'email'    => 'autotester@example.com',
      'password' => 'secret123',
    ]);
    $login->assertStatus(200)
      ->assertJsonPath('data.user.email', 'autotester@example.com')
      ->assertJsonStructure([
        'data' => [
          'token',
          'token_type',
          'expires_at',
          'user' => ['id', 'email', 'nombre', 'tipo_usuario']
        ],
        'message',
        'success'
      ]);
  }

  /** @test */
  public function crud_de_equipos_funciona_con_autenticacion()
  {
    // Creamos un usuario con factory de Usuario
    $user = Usuario::factory()->create();
    Sanctum::actingAs($user);

    // Crear equipo
    $create = $this->postJson('/api/equipos', [
      'nombre'     => 'Equipo Test',
      'deporte_id' => 1,
    ]);
    $create->assertStatus(201)
      ->assertJsonPath('data.nombre', 'Equipo Test');

    $equipoId = $create->json('data.id');

    // Listar
    // Listar
    $list = $this->getJson('/api/equipos');
    $list->assertStatus(200)
      ->assertJsonFragment([
        'id'     => $equipoId,
        'nombre' => 'Equipo Test',
      ]);


    // Actualizar
    $update = $this->putJson("/api/equipos/{$equipoId}", [
      'nombre' => 'Equipo Modificado',
    ]);
    $update->assertStatus(200)
      ->assertJsonPath('data.nombre', 'Equipo Modificado');

    // Borrar
    $delete = $this->deleteJson("/api/equipos/{$equipoId}");
    $delete->assertStatus(200)
      ->assertJson(['message' => 'Equipo eliminado correctamente', 'success' => true]);
  }
}
