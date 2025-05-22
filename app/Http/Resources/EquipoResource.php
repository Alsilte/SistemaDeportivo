<?php

/**
 * RESOURCE: EQUIPO
 * 
 * Comando para crear: php artisan make:resource EquipoResource
 * Archivo: app/Http/Resources/EquipoResource.php
 * 
 * Formatea la respuesta JSON para equipos
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipoResource extends JsonResource
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
      'logo' => $this->logo ? asset('storage/' . $this->logo) : null,
      'email' => $this->email,
      'telefono' => $this->telefono,
      'activo' => $this->activo,
      'estado_label' => $this->activo ? 'Activo' : 'Inactivo',

      // Información del deporte
      'deporte' => $this->when(
        $this->relationLoaded('deporte'),
        function () {
          return [
            'id' => $this->deporte->id,
            'nombre' => $this->deporte->nombre,
            'descripcion' => $this->deporte->descripcion,
            'imagen' => $this->deporte->imagen ? asset('storage/' . $this->deporte->imagen) : null,
          ];
        }
      ),

      // Administrador del equipo
      'administrador' => $this->when(
        $this->relationLoaded('administrador'),
        function () {
          return $this->administrador ? [
            'id' => $this->administrador->id,
            'nombre' => $this->administrador->nombre,
            'email' => $this->administrador->email,
            'telefono' => $this->administrador->telefono,
          ] : null;
        }
      ),

      // Información de jugadores
      'jugadores_info' => [
        'total' => $this->when(
          $this->relationLoaded('jugadores'),
          $this->jugadores->count()
        ),
        'activos' => $this->when(
          $this->relationLoaded('jugadores'),
          $this->jugadores->where('pivot.estado', 'activo')->count()
        ),
        'lesionados' => $this->when(
          $this->relationLoaded('jugadores'),
          $this->jugadores->where('pivot.estado', 'lesionado')->count()
        ),
        'suspendidos' => $this->when(
          $this->relationLoaded('jugadores'),
          $this->jugadores->where('pivot.estado', 'suspendido')->count()
        ),
        'capitan' => $this->when(
          $this->relationLoaded('jugadores'),
          function () {
            $capitan = $this->jugadores->where('pivot.es_capitan', true)->first();
            return $capitan ? [
              'id' => $capitan->id,
              'nombre' => $capitan->usuario->nombre,
              'numero_camiseta' => $capitan->pivot->numero_camiseta,
            ] : null;
          }
        ),
      ],

      // Jugadores detallados
      'jugadores' => $this->when(
        $this->relationLoaded('jugadores') && $request->query('include_players') === 'true',
        function () {
          return $this->jugadores->map(function ($jugador) {
            return [
              'id' => $jugador->id,
              'usuario' => [
                'id' => $jugador->usuario->id,
                'nombre' => $jugador->usuario->nombre,
                'email' => $jugador->usuario->email,
                'telefono' => $jugador->usuario->telefono,
                'avatar' => $jugador->usuario->avatar ? asset('storage/' . $jugador->usuario->avatar) : null,
              ],
              'numero_camiseta' => $jugador->pivot->numero_camiseta,
              'posicion' => $jugador->pivot->posicion,
              'estado' => $jugador->pivot->estado,
              'es_capitan' => $jugador->pivot->es_capitan,
              'es_titular' => $jugador->pivot->es_titular,
              'fecha_incorporacion' => $jugador->pivot->fecha_incorporacion,
              'estadisticas' => [
                'partidos_jugados' => $jugador->pivot->partidos_jugados ?? 0,
                'goles_marcados' => $jugador->pivot->goles_marcados ?? 0,
                'asistencias' => $jugador->pivot->asistencias ?? 0,
                'tarjetas_amarillas' => $jugador->pivot->tarjetas_amarillas ?? 0,
                'tarjetas_rojas' => $jugador->pivot->tarjetas_rojas ?? 0,
              ],
            ];
          })->sortBy('numero_camiseta')->values();
        }
      ),

      // Torneos
      'torneos_info' => [
        'total' => $this->when(
          $this->relationLoaded('torneos'),
          $this->torneos->count()
        ),
        'activos' => $this->when(
          $this->relationLoaded('torneos'),
          $this->torneos->where('estado', 'activo')->count()
        ),
        'finalizados' => $this->when(
          $this->relationLoaded('torneos'),
          $this->torneos->where('estado', 'finalizado')->count()
        ),
      ],

      // Torneos detallados
      'torneos' => $this->when(
        $this->relationLoaded('torneos') && $request->query('include_tournaments') === 'true',
        function () {
          return $this->torneos->map(function ($torneo) {
            return [
              'id' => $torneo->id,
              'nombre' => $torneo->nombre,
              'formato' => $torneo->formato,
              'estado' => $torneo->estado,
              'fecha_inicio' => $torneo->fecha_inicio?->format('Y-m-d'),
              'fecha_fin' => $torneo->fecha_fin?->format('Y-m-d'),
              'estado_participacion' => $torneo->pivot->estado_participacion,
              'fecha_inscripcion' => $torneo->pivot->fecha_inscripcion,
            ];
          });
        }
      ),

      // Estadísticas generales
      'estadisticas' => [
        'partidos' => $this->getEstadisticasPartidos(),
        'rendimiento' => $this->getEstadisticasRendimiento(),
        'historial' => $this->when(
          $request->query('include_history') === 'true',
          $this->getHistorialCompleto()
        ),
      ],

      // Clasificaciones actuales
      'clasificaciones_actuales' => $this->when(
        $this->relationLoaded('clasificaciones'),
        function () {
          return $this->clasificaciones
            ->where('torneo.estado', 'activo')
            ->map(function ($clasificacion) {
              return [
                'torneo' => [
                  'id' => $clasificacion->torneo->id,
                  'nombre' => $clasificacion->torneo->nombre,
                ],
                'posicion' => $clasificacion->posicion,
                'puntos' => $clasificacion->puntos,
                'partidos_jugados' => $clasificacion->partidos_jugados,
                'diferencia_goles' => $clasificacion->diferencia_goles,
              ];
            });
        }
      ),

      // Próximos partidos
      'proximos_partidos' => $this->when(
        $this->relationLoaded('partidosLocal') && $this->relationLoaded('partidosVisitante'),
        function () {
          $partidosLocal = $this->partidosLocal
            ->where('estado', 'programado')
            ->where('fecha', '>', now());

          $partidosVisitante = $this->partidosVisitante
            ->where('estado', 'programado')
            ->where('fecha', '>', now());

          return $partidosLocal->concat($partidosVisitante)
            ->sortBy('fecha')
            ->take(3)
            ->map(function ($partido) {
              $esLocal = $partido->equipo_local_id === $this->id;
              $rival = $esLocal ? $partido->equipoVisitante : $partido->equipoLocal;

              return [
                'id' => $partido->id,
                'fecha' => $partido->fecha->format('Y-m-d H:i:s'),
                'torneo' => [
                  'id' => $partido->torneo->id,
                  'nombre' => $partido->torneo->nombre,
                ],
                'es_local' => $esLocal,
                'rival' => [
                  'id' => $rival->id,
                  'nombre' => $rival->nombre,
                  'logo' => $rival->logo ? asset('storage/' . $rival->logo) : null,
                ],
                'lugar' => $partido->lugar,
                'dias_restantes' => now()->diffInDays($partido->fecha, false),
              ];
            })->values();
        }
      ),

      // Últimos partidos
      'ultimos_partidos' => $this->when(
        $this->relationLoaded('partidosLocal') && $this->relationLoaded('partidosVisitante'),
        function () {
          $partidosLocal = $this->partidosLocal
            ->where('estado', 'finalizado')
            ->where('fecha', '<', now());

          $partidosVisitante = $this->partidosVisitante
            ->where('estado', 'finalizado')
            ->where('fecha', '<', now());

          return $partidosLocal->concat($partidosVisitante)
            ->sortByDesc('fecha')
            ->take(5)
            ->map(function ($partido) {
              $esLocal = $partido->equipo_local_id === $this->id;
              $rival = $esLocal ? $partido->equipoVisitante : $partido->equipoLocal;
              $golesPropio = $esLocal ? $partido->goles_local : $partido->goles_visitante;
              $golesRival = $esLocal ? $partido->goles_visitante : $partido->goles_local;

              return [
                'id' => $partido->id,
                'fecha' => $partido->fecha->format('Y-m-d'),
                'torneo' => [
                  'id' => $partido->torneo->id,
                  'nombre' => $partido->torneo->nombre,
                ],
                'rival' => [
                  'id' => $rival->id,
                  'nombre' => $rival->nombre,
                  'logo' => $rival->logo ? asset('storage/' . $rival->logo) : null,
                ],
                'resultado' => [
                  'goles_propio' => $golesPropio,
                  'goles_rival' => $golesRival,
                  'resultado' => $this->getResultadoPartido($golesPropio, $golesRival),
                ],
                'es_local' => $esLocal,
              ];
            })->values();
        }
      ),

      // Metadatos
      'creado_en' => $this->created_at?->format('Y-m-d H:i:s'),
      'actualizado_en' => $this->updated_at?->format('Y-m-d H:i:s'),
      'dias_desde_creacion' => $this->created_at ? $this->created_at->diffInDays(now()) : 0,
    ];
  }

  /**
   * Obtener estadísticas de partidos
   *
   * @return array
   */
  private function getEstadisticasPartidos(): array
  {
    if (!$this->relationLoaded('partidosLocal') || !$this->relationLoaded('partidosVisitante')) {
      return [];
    }

    $partidosLocal = $this->partidosLocal->where('estado', 'finalizado');
    $partidosVisitante = $this->partidosVisitante->where('estado', 'finalizado');
    $todosPartidos = $partidosLocal->concat($partidosVisitante);

    $ganados = 0;
    $empatados = 0;
    $perdidos = 0;
    $golesFavor = 0;
    $golesContra = 0;

    foreach ($partidosLocal as $partido) {
      $golesFavor += $partido->goles_local;
      $golesContra += $partido->goles_visitante;

      if ($partido->goles_local > $partido->goles_visitante) {
        $ganados++;
      } elseif ($partido->goles_local == $partido->goles_visitante) {
        $empatados++;
      } else {
        $perdidos++;
      }
    }

    foreach ($partidosVisitante as $partido) {
      $golesFavor += $partido->goles_visitante;
      $golesContra += $partido->goles_local;

      if ($partido->goles_visitante > $partido->goles_local) {
        $ganados++;
      } elseif ($partido->goles_visitante == $partido->goles_local) {
        $empatados++;
      } else {
        $perdidos++;
      }
    }

    $totalPartidos = $todosPartidos->count();

    return [
      'partidos_jugados' => $totalPartidos,
      'ganados' => $ganados,
      'empatados' => $empatados,
      'perdidos' => $perdidos,
      'goles_favor' => $golesFavor,
      'goles_contra' => $golesContra,
      'diferencia_goles' => $golesFavor - $golesContra,
      'promedio_goles_favor' => $totalPartidos > 0 ? round($golesFavor / $totalPartidos, 2) : 0,
      'promedio_goles_contra' => $totalPartidos > 0 ? round($golesContra / $totalPartidos, 2) : 0,
    ];
  }

  /**
   * Obtener estadísticas de rendimiento
   *
   * @return array
   */
  private function getEstadisticasRendimiento(): array
  {
    $estadisticas = $this->getEstadisticasPartidos();

    if (empty($estadisticas) || $estadisticas['partidos_jugados'] === 0) {
      return [
        'efectividad' => 0,
        'puntos_promedio' => 0,
        'racha_actual' => 'Sin partidos',
        'mejor_racha' => 0,
      ];
    }

    $totalPartidos = $estadisticas['partidos_jugados'];
    $ganados = $estadisticas['ganados'];
    $empatados = $estadisticas['empatados'];

    // Calcular puntos (3 por victoria, 1 por empate)
    $puntosTotales = ($ganados * 3) + ($empatados * 1);

    return [
      'efectividad' => round(($ganados / $totalPartidos) * 100, 2),
      'puntos_totales' => $puntosTotales,
      'puntos_promedio' => round($puntosTotales / $totalPartidos, 2),
      'racha_actual' => $this->getRachaActual(),
      'mejor_racha' => $this->getMejorRacha(),
      'rendimiento_local' => $this->getRendimientoLocal(),
      'rendimiento_visitante' => $this->getRendimientoVisitante(),
    ];
  }

  /**
   * Obtener historial completo
   *
   * @return array
   */
  private function getHistorialCompleto(): array
  {
    return [
      'fecha_fundacion' => $this->created_at?->format('Y-m-d'),
      'anos_activo' => $this->created_at ? $this->created_at->diffInYears(now()) : 0,
      'logros' => $this->getLogros(),
      'estadisticas_por_temporada' => $this->getEstadisticasPorTemporada(),
    ];
  }

  /**
   * Obtener resultado del partido
   *
   * @param int $golesPropio
   * @param int $golesRival
   * @return string
   */
  private function getResultadoPartido(int $golesPropio, int $golesRival): string
  {
    if ($golesPropio > $golesRival) {
      return 'Victoria';
    } elseif ($golesPropio < $golesRival) {
      return 'Derrota';
    } else {
      return 'Empate';
    }
  }

  /**
   * Obtener racha actual
   *
   * @return string
   */
  private function getRachaActual(): string
  {
    if (!$this->relationLoaded('partidosLocal') || !$this->relationLoaded('partidosVisitante')) {
      return 'Sin datos';
    }

    $partidosLocal = $this->partidosLocal->where('estado', 'finalizado');
    $partidosVisitante = $this->partidosVisitante->where('estado', 'finalizado');
    $ultimosPartidos = $partidosLocal->concat($partidosVisitante)
      ->sortByDesc('fecha')
      ->take(5);

    if ($ultimosPartidos->isEmpty()) {
      return 'Sin partidos';
    }

    $ultimoPartido = $ultimosPartidos->first();
    $esLocal = $ultimoPartido->equipo_local_id === $this->id;
    $golesPropio = $esLocal ? $ultimoPartido->goles_local : $ultimoPartido->goles_visitante;
    $golesRival = $esLocal ? $ultimoPartido->goles_visitante : $ultimoPartido->goles_local;

    $resultado = $this->getResultadoPartido($golesPropio, $golesRival);

    return "Última: {$resultado}";
  }

  /**
   * Obtener mejor racha
   *
   * @return int
   */
  private function getMejorRacha(): int
  {
    // Aquí puedes implementar la lógica para calcular la mejor racha
    // Por simplicidad, retornamos 0
    return 0;
  }

  /**
   * Obtener rendimiento local
   *
   * @return array
   */
  private function getRendimientoLocal(): array
  {
    if (!$this->relationLoaded('partidosLocal')) {
      return [];
    }

    $partidosLocal = $this->partidosLocal->where('estado', 'finalizado');
    $total = $partidosLocal->count();

    if ($total === 0) {
      return ['partidos' => 0, 'efectividad' => 0];
    }

    $ganados = $partidosLocal->where('goles_local', '>', 'goles_visitante')->count();

    return [
      'partidos' => $total,
      'efectividad' => round(($ganados / $total) * 100, 2),
    ];
  }

  /**
   * Obtener rendimiento visitante
   *
   * @return array
   */
  private function getRendimientoVisitante(): array
  {
    if (!$this->relationLoaded('partidosVisitante')) {
      return [];
    }

    $partidosVisitante = $this->partidosVisitante->where('estado', 'finalizado');
    $total = $partidosVisitante->count();

    if ($total === 0) {
      return ['partidos' => 0, 'efectividad' => 0];
    }

    $ganados = $partidosVisitante->where('goles_visitante', '>', 'goles_local')->count();

    return [
      'partidos' => $total,
      'efectividad' => round(($ganados / $total) * 100, 2),
    ];
  }

  /**
   * Obtener logros del equipo
   *
   * @return array
   */
  private function getLogros(): array
  {
    // Aquí puedes implementar la lógica para obtener logros
    // Por ejemplo, torneos ganados, etc.
    return [];
  }

  /**
   * Obtener estadísticas por temporada
   *
   * @return array
   */
  private function getEstadisticasPorTemporada(): array
  {
    // Aquí puedes implementar la lógica para estadísticas por temporada
    return [];
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
        'include_players' => $request->query('include_players') === 'true',
        'include_tournaments' => $request->query('include_tournaments') === 'true',
        'include_history' => $request->query('include_history') === 'true',
      ],
    ];
  }
}
