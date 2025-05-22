<?php

/**
 * RESOURCE: USUARIO
 * 
 * Comando para crear: php artisan make:resource UserResource
 * Archivo: app/Http/Resources/UserResource.php
 * 
 * Formatea la respuesta JSON para usuarios
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'nombre' => $this->nombre,
      'email' => $this->email,
      'telefono' => $this->telefono,
      'fecha_nacimiento' => $this->fecha_nacimiento?->format('Y-m-d'),
      'edad' => $this->fecha_nacimiento ? $this->fecha_nacimiento->age : null,
      'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
      'tipo_usuario' => $this->tipo_usuario,
      'activo' => $this->activo,
      'email_verificado' => $this->email_verified_at !== null,
      'fecha_registro' => $this->created_at?->format('Y-m-d H:i:s'),
      'ultima_actualizacion' => $this->updated_at?->format('Y-m-d H:i:s'),

      // Información específica del perfil
      'perfil' => $this->getPerfilData(),

      // Relaciones condicionales
      'equipos' => $this->when(
        $this->relationLoaded('jugador') && $this->jugador,
        function () {
          return $this->jugador->equipos->map(function ($equipo) {
            return [
              'id' => $equipo->id,
              'nombre' => $equipo->nombre,
              'logo' => $equipo->logo ? asset('storage/' . $equipo->logo) : null,
              'deporte' => $equipo->deporte->nombre ?? null,
              'numero_camiseta' => $equipo->pivot->numero_camiseta ?? null,
              'posicion' => $equipo->pivot->posicion ?? null,
              'es_capitan' => $equipo->pivot->es_capitan ?? false,
              'es_titular' => $equipo->pivot->es_titular ?? false,
              'estado' => $equipo->pivot->estado ?? 'activo',
            ];
          });
        }
      ),

      'partidos_arbitrados' => $this->when(
        $this->relationLoaded('arbitro') && $this->arbitro,
        function () {
          return $this->arbitro->partidos_arbitrados ?? 0;
        }
      ),

      'equipos_administrados' => $this->when(
        $this->relationLoaded('equiposAdministrados'),
        EquipoResource::collection($this->equiposAdministrados)
      ),

      // Estadísticas del usuario
      'estadisticas' => $this->when(
        $request->query('include_stats') === 'true',
        function () {
          return $this->getEstadisticas();
        }
      ),

      // Permisos (solo para administradores)
      'permisos' => $this->when(
        $this->tipo_usuario === 'administrador' && $this->relationLoaded('administrador'),
        function () {
          return $this->administrador->permisos ?? [];
        }
      ),

      'nivel_acceso' => $this->when(
        $this->tipo_usuario === 'administrador' && $this->relationLoaded('administrador'),
        function () {
          return $this->administrador->nivel_acceso ?? 'admin';
        }
      ),
    ];
  }

  /**
   * Obtener datos específicos del perfil según tipo de usuario
   *
   * @return array|null
   */
  private function getPerfilData(): ?array
  {
    switch ($this->tipo_usuario) {
      case 'jugador':
        if ($this->relationLoaded('jugador') && $this->jugador) {
          return [
            'posicion' => $this->jugador->posicion,
            'numero_camiseta' => $this->jugador->numero_camiseta,
            'puntos' => $this->jugador->puntos,
            'partidos_jugados' => $this->jugador->partidos_jugados,
            'goles_favor' => $this->jugador->goles_favor,
            'goles_contra' => $this->jugador->goles_contra,
            'ganados' => $this->jugador->ganados,
            'empatados' => $this->jugador->empatados,
            'perdidos' => $this->jugador->perdidos,
            'efectividad' => $this->jugador->partidos_jugados > 0
              ? round(($this->jugador->ganados / $this->jugador->partidos_jugados) * 100, 2)
              : 0,
          ];
        }
        break;

      case 'arbitro':
        if ($this->relationLoaded('arbitro') && $this->arbitro) {
          return [
            'licencia' => $this->arbitro->licencia,
            'posicion' => $this->arbitro->posicion,
            'partidos_arbitrados' => $this->arbitro->partidos_arbitrados,
            'fecha_nacimiento_arbitro' => $this->arbitro->fecha_nacimiento?->format('Y-m-d'),
            'experiencia_anos' => $this->arbitro->fecha_nacimiento
              ? max(0, now()->diffInYears($this->arbitro->fecha_nacimiento) - 18)
              : 0,
          ];
        }
        break;

      case 'administrador':
        if ($this->relationLoaded('administrador') && $this->administrador) {
          return [
            'nivel_acceso' => $this->administrador->nivel_acceso,
            'permisos' => $this->administrador->permisos ?? [],
            'equipos_gestionados' => $this->equiposAdministrados?->count() ?? 0,
          ];
        }
        break;
    }

    return null;
  }

  /**
   * Obtener estadísticas del usuario
   *
   * @return array
   */
  private function getEstadisticas(): array
  {
    $stats = [
      'fecha_registro' => $this->created_at?->format('Y-m-d'),
      'dias_registrado' => $this->created_at ? $this->created_at->diffInDays(now()) : 0,
    ];

    switch ($this->tipo_usuario) {
      case 'jugador':
        if ($this->jugador) {
          $stats = array_merge($stats, [
            'equipos_actuales' => $this->jugador->equipos?->where('pivot.estado', 'activo')->count() ?? 0,
            'promedio_goles_partido' => $this->jugador->partidos_jugados > 0
              ? round($this->jugador->goles_favor / $this->jugador->partidos_jugados, 2)
              : 0,
            'diferencia_goles' => $this->jugador->goles_favor - $this->jugador->goles_contra,
          ]);
        }
        break;

      case 'arbitro':
        if ($this->arbitro) {
          $stats = array_merge($stats, [
            'partidos_mes_actual' => \App\Models\Partido::where('arbitro_id', $this->arbitro->id)
              ->whereMonth('fecha', now()->month)
              ->whereYear('fecha', now()->year)
              ->count(),
            'promedio_partidos_mes' => $this->arbitro->partidos_arbitrados > 0
              ? round($this->arbitro->partidos_arbitrados / max(1, $this->created_at->diffInMonths(now())), 2)
              : 0,
          ]);
        }
        break;

      case 'administrador':
        $stats = array_merge($stats, [
          'equipos_administrados' => $this->equiposAdministrados?->count() ?? 0,
          'torneos_creados' => 0, // Puedes agregar esta relación si implementas created_by en torneos
        ]);
        break;
    }

    return $stats;
  }

  /**
   * Get additional data that should be returned with the resource array.
   *
   * @return array<string, mixed>
   */
  public function with(Request $request): array
  {
    return [
      'meta' => [
        'version' => '1.0',
        'generated_at' => now()->toISOString(),
        'include_stats' => $request->query('include_stats') === 'true',
      ],
    ];
  }
}
