<?php

/**
 * RESOURCE: TORNEO
 * 
 * Comando para crear: php artisan make:resource TorneoResource
 * Archivo: app/Http/Resources/TorneoResource.php
 * 
 * Formatea la respuesta JSON para torneos
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TorneoResource extends JsonResource
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
      'descripcion' => $this->descripcion,
      'formato' => $this->formato,
      'estado' => $this->estado,
      'estado_label' => $this->getEstadoLabel(),

      // Fechas
      'fechas' => [
        'inicio' => $this->fecha_inicio?->format('Y-m-d'),
        'fin' => $this->fecha_fin?->format('Y-m-d'),
        'inscripcion_limite' => $this->fecha_inscripcion_limite?->format('Y-m-d H:i:s'),
        'duracion_dias' => $this->fecha_inicio && $this->fecha_fin
          ? $this->fecha_inicio->diffInDays($this->fecha_fin) + 1
          : null,
        'dias_para_inicio' => $this->fecha_inicio
          ? max(0, now()->diffInDays($this->fecha_inicio, false))
          : null,
        'dias_restantes' => $this->fecha_fin
          ? max(0, now()->diffInDays($this->fecha_fin, false))
          : null,
      ],

      // Información del deporte
      'deporte' => $this->when(
        $this->relationLoaded('deporte'),
        function () {
          return [
            'id' => $this->deporte->id,
            'nombre' => $this->deporte->nombre,
            'descripcion' => $this->deporte->descripcion,
            'imagen' => $this->deporte->imagen ? asset('storage/' . $this->deporte->imagen) : null,
            'configuracion' => $this->deporte->configuracion_json ?? [],
          ];
        }
      ),

      // Configuración del torneo
      'configuracion' => $this->configuracion ?? [],
      'configuracion_formateada' => $this->getConfiguracionFormateada(),

      // Premios
      'premios' => $this->premios ?? [],
      'tiene_premios' => !empty($this->premios),

      // Información de participación
      'participacion' => [
        'equipos_inscritos' => $this->when(
          $this->relationLoaded('equipos'),
          $this->equipos->count()
        ),
        'equipos_confirmados' => $this->when(
          $this->relationLoaded('equipos'),
          $this->equipos->where('pivot.estado_participacion', 'confirmado')->count()
        ),
        'max_equipos' => $this->configuracion['max_equipos'] ?? null,
        'min_equipos' => $this->configuracion['min_equipos'] ?? null,
        'inscripciones_abiertas' => $this->inscripcionesAbiertas(),
        'puede_inscribirse' => $this->puedeInscribirse(),
      ],

      // Equipos participantes
      'equipos' => $this->when(
        $this->relationLoaded('equipos'),
        function () {
          return $this->equipos->map(function ($equipo) {
            return [
              'id' => $equipo->id,
              'nombre' => $equipo->nombre,
              'logo' => $equipo->logo ? asset('storage/' . $equipo->logo) : null,
              'estado_participacion' => $equipo->pivot->estado_participacion,
              'fecha_inscripcion' => $equipo->pivot->fecha_inscripcion,
              'telefono_contacto' => $equipo->pivot->telefono_contacto,
              'email_contacto' => $equipo->pivot->email_contacto,
            ];
          });
        }
      ),

      // Estadísticas de partidos
      'estadisticas_partidos' => $this->when(
        $this->relationLoaded('partidos'),
        function () {
          return [
            'total_partidos' => $this->partidos->count(),
            'partidos_jugados' => $this->partidos->where('estado', 'finalizado')->count(),
            'partidos_pendientes' => $this->partidos->where('estado', 'programado')->count(),
            'partidos_en_curso' => $this->partidos->where('estado', 'en_curso')->count(),
            'partidos_cancelados' => $this->partidos->where('estado', 'cancelado')->count(),
            'progreso_porcentaje' => $this->partidos->count() > 0
              ? round(($this->partidos->where('estado', 'finalizado')->count() / $this->partidos->count()) * 100, 2)
              : 0,
          ];
        }
      ),

      // Clasificación (top 3)
      'clasificacion_top' => $this->when(
        $this->relationLoaded('clasificacion'),
        function () {
          return $this->clasificacion
            ->sortByDesc('puntos')
            ->take(3)
            ->map(function ($clasificacion, $index) {
              return [
                'posicion' => $index + 1,
                'equipo' => [
                  'id' => $clasificacion->equipo->id,
                  'nombre' => $clasificacion->equipo->nombre,
                  'logo' => $clasificacion->equipo->logo ? asset('storage/' . $clasificacion->equipo->logo) : null,
                ],
                'puntos' => $clasificacion->puntos,
                'partidos_jugados' => $clasificacion->partidos_jugados,
                'diferencia_goles' => $clasificacion->diferencia_goles,
              ];
            })->values();
        }
      ),

      // Próximos partidos
      'proximos_partidos' => $this->when(
        $this->relationLoaded('partidos'),
        function () {
          return $this->partidos
            ->where('estado', 'programado')
            ->where('fecha', '>', now())
            ->sortBy('fecha')
            ->take(3)
            ->map(function ($partido) {
              return [
                'id' => $partido->id,
                'fecha' => $partido->fecha->format('Y-m-d H:i:s'),
                'equipo_local' => [
                  'id' => $partido->equipoLocal->id,
                  'nombre' => $partido->equipoLocal->nombre,
                  'logo' => $partido->equipoLocal->logo ? asset('storage/' . $partido->equipoLocal->logo) : null,
                ],
                'equipo_visitante' => [
                  'id' => $partido->equipoVisitante->id,
                  'nombre' => $partido->equipoVisitante->nombre,
                  'logo' => $partido->equipoVisitante->logo ? asset('storage/' . $partido->equipoVisitante->logo) : null,
                ],
                'lugar' => $partido->lugar,
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
   * Obtener etiqueta del estado
   *
   * @return string
   */
  private function getEstadoLabel(): string
  {
    return match ($this->estado) {
      'planificacion' => 'En Planificación',
      'activo' => 'Activo',
      'finalizado' => 'Finalizado',
      'cancelado' => 'Cancelado',
      default => 'Desconocido',
    };
  }

  /**
   * Obtener configuración formateada
   *
   * @return array
   */
  private function getConfiguracionFormateada(): array
  {
    $config = $this->configuracion ?? [];

    return [
      'equipos' => [
        'minimo' => $config['min_equipos'] ?? 2,
        'maximo' => $config['max_equipos'] ?? 20,
      ],
      'puntuacion' => [
        'victoria' => $config['puntos_victoria'] ?? 3,
        'empate' => $config['puntos_empate'] ?? 1,
        'derrota' => $config['puntos_derrota'] ?? 0,
      ],
      'reglas' => [
        'permite_empates' => $config['permite_empates'] ?? true,
        'ida_y_vuelta' => $config['partidos_ida_vuelta'] ?? false,
        'desempate_penales' => $config['desempate_penales'] ?? false,
      ],
      'formato_descripcion' => $this->getFormatoDescripcion(),
    ];
  }

  /**
   * Obtener descripción del formato
   *
   * @return string
   */
  private function getFormatoDescripcion(): string
  {
    return match ($this->formato) {
      'liga' => 'Todos los equipos juegan entre sí. Gana el equipo con más puntos.',
      'eliminacion' => 'Eliminación directa. Quien pierde queda eliminado.',
      'grupos' => 'Fase de grupos seguida de eliminación directa.',
      default => 'Formato personalizado',
    };
  }

  /**
   * Verificar si las inscripciones están abiertas
   *
   * @return bool
   */
  private function inscripcionesAbiertas(): bool
  {
    if ($this->estado !== 'planificacion') {
      return false;
    }

    if ($this->fecha_inscripcion_limite && now() > $this->fecha_inscripcion_limite) {
      return false;
    }

    return true;
  }

  /**
   * Verificar si se puede inscribir más equipos
   *
   * @return bool
   */
  private function puedeInscribirse(): bool
  {
    if (!$this->inscripcionesAbiertas()) {
      return false;
    }

    $maxEquipos = $this->configuracion['max_equipos'] ?? null;
    if ($maxEquipos && $this->relationLoaded('equipos')) {
      return $this->equipos->count() < $maxEquipos;
    }

    return true;
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
        'timezone' => config('app.timezone'),
      ],
    ];
  }
}
