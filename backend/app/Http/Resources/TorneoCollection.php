<?php

/**
 * COLLECTION: TORNEOS
 * 
 * Comando para crear: php artisan make:resource TorneoCollection
 * Archivo: app/Http/Resources/TorneoCollection.php
 * 
 * Formatea la respuesta JSON para colecciones de torneos
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;

class TorneoCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($torneo) use ($request) {
                return [
                    'id' => $torneo->id,
                    'nombre' => $torneo->nombre,
                    'descripcion' => $torneo->descripcion,
                    'formato' => $torneo->formato,
                    'formato_label' => $this->getFormatoLabel($torneo->formato),
                    'estado' => $torneo->estado,
                    'estado_label' => $this->getEstadoLabel($torneo->estado),
                    'estado_color' => $this->getEstadoColor($torneo->estado),

                    // Fechas importantes
                    'fecha_inicio' => $torneo->fecha_inicio?->format('Y-m-d'),
                    'fecha_fin' => $torneo->fecha_fin?->format('Y-m-d'),
                    'duracion_dias' => $torneo->fecha_inicio && $torneo->fecha_fin
                        ? $torneo->fecha_inicio->diffInDays($torneo->fecha_fin) + 1
                        : null,
                    'dias_para_inicio' => $torneo->fecha_inicio
                        ? max(0, now()->diffInDays($torneo->fecha_inicio, false))
                        : null,
                    'dias_restantes' => $torneo->fecha_fin
                        ? max(0, now()->diffInDays($torneo->fecha_fin, false))
                        : null,

                    // Información del deporte
                    'deporte' => $torneo->relationLoaded('deporte') && $torneo->deporte ? [
                        'id' => $torneo->deporte->id,
                        'nombre' => $torneo->deporte->nombre,
                        'imagen' => $torneo->deporte->imagen ? asset('storage/' . $torneo->deporte->imagen) : null,
                    ] : null,

                    // Información de participación
                    'participacion' => [
                        'equipos_inscritos' => $torneo->relationLoaded('equipos') ? $torneo->equipos->count() : 0,
                        'equipos_confirmados' => $torneo->relationLoaded('equipos')
                            ? $torneo->equipos->where('pivot.estado_participacion', 'confirmado')->count()
                            : 0,
                        'max_equipos' => $torneo->configuracion['max_equipos'] ?? null,
                        'inscripciones_abiertas' => $this->inscripcionesAbiertas($torneo),
                        'progreso_inscripciones' => $this->getProgresoInscripciones($torneo),
                    ],

                    // Estadísticas de partidos (si están cargados)
                    'partidos_info' => $torneo->relationLoaded('partidos') ? [
                        'total' => $torneo->partidos->count(),
                        'jugados' => $torneo->partidos->where('estado', 'finalizado')->count(),
                        'pendientes' => $torneo->partidos->where('estado', 'programado')->count(),
                        'progreso_porcentaje' => $torneo->partidos->count() > 0
                            ? round(($torneo->partidos->where('estado', 'finalizado')->count() / $torneo->partidos->count()) * 100, 1)
                            : 0,
                    ] : null,

                    // Clasificación actual (top 3)
                    'top_3' => $this->getTop3($torneo),

                    // Próximo partido
                    'proximo_partido' => $this->getProximoPartido($torneo),

                    // Información adicional
                    'tiene_premios' => !empty($torneo->premios),
                    'es_reciente' => $torneo->created_at && $torneo->created_at->isAfter(now()->subDays(7)),
                    'creado_hace' => $torneo->created_at ? $torneo->created_at->diffForHumans() : null,
                ];
            }),
        ];
    }

    /**
     * Obtener etiqueta del formato
     *
     * @param string $formato
     * @return string
     */
    private function getFormatoLabel(string $formato): string
    {
        return match ($formato) {
            'liga' => 'Liga',
            'eliminacion' => 'Eliminación',
            'grupos' => 'Grupos',
            default => ucfirst($formato),
        };
    }

    /**
     * Obtener etiqueta del estado
     *
     * @param string $estado
     * @return string
     */
    private function getEstadoLabel(string $estado): string
    {
        return match ($estado) {
            'planificacion' => 'En Planificación',
            'activo' => 'Activo',
            'finalizado' => 'Finalizado',
            'cancelado' => 'Cancelado',
            default => ucfirst($estado),
        };
    }

    /**
     * Obtener color del estado para UI
     *
     * @param string $estado
     * @return string
     */
    private function getEstadoColor(string $estado): string
    {
        return match ($estado) {
            'planificacion' => 'blue',
            'activo' => 'green',
            'finalizado' => 'gray',
            'cancelado' => 'red',
            default => 'gray',
        };
    }

    /**
     * Verificar si las inscripciones están abiertas
     *
     * @param $torneo
     * @return bool
     */
    private function inscripcionesAbiertas($torneo): bool
    {
        if ($torneo->estado !== 'planificacion') {
            return false;
        }

        if ($torneo->fecha_inscripcion_limite && now() > $torneo->fecha_inscripcion_limite) {
            return false;
        }

        return true;
    }

    /**
     * Obtener progreso de inscripciones
     *
     * @param $torneo
     * @return float
     */
    private function getProgresoInscripciones($torneo): float
    {
        if (!$torneo->relationLoaded('equipos')) {
            return 0;
        }

        $maxEquipos = $torneo->configuracion['max_equipos'] ?? 1;
        $equiposInscritos = $torneo->equipos->count();

        return round(($equiposInscritos / $maxEquipos) * 100, 1);
    }

    /**
     * Obtener top 3 de clasificación
     *
     * @param $torneo
     * @return array
     */
    private function getTop3($torneo): array
    {
        if (!$torneo->relationLoaded('clasificacion')) {
            return [];
        }

        return $torneo->clasificacion
            ->sortByDesc('puntos')
            ->take(3)
            ->map(function ($clasificacion, $index) {
                return [
                    'posicion' => $index + 1,
                    'equipo_nombre' => $clasificacion->equipo->nombre ?? 'N/A',
                    'puntos' => $clasificacion->puntos,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Obtener próximo partido
     *
     * @param $torneo
     * @return array|null
     */
    private function getProximoPartido($torneo): ?array
    {
        if (!$torneo->relationLoaded('partidos')) {
            return null;
        }

        $proximoPartido = $torneo->partidos
            ->where('estado', 'programado')
            ->where('fecha', '>', now())
            ->sortBy('fecha')
            ->first();

        if (!$proximoPartido) {
            return null;
        }

        return [
            'id' => $proximoPartido->id,
            'fecha' => $proximoPartido->fecha->format('Y-m-d H:i'),
            'equipo_local' => $proximoPartido->equipoLocal->nombre ?? 'TBD',
            'equipo_visitante' => $proximoPartido->equipoVisitante->nombre ?? 'TBD',
            'dias_restantes' => $proximoPartido->fecha->diffInDays(now()),
        ];
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
                'total' => $this->collection->count(),
                'por_estado' => $this->getDistribucionEstados(),
                'por_formato' => $this->getDistribucionFormatos(),
                'por_deporte' => $this->getDistribucionDeportes(),
                'estadisticas' => [
                    'activos' => $this->collection->where('estado', 'activo')->count(),
                    'finalizados' => $this->collection->where('estado', 'finalizado')->count(),
                    'en_planificacion' => $this->collection->where('estado', 'planificacion')->count(),
                    'total_equipos_participantes' => $this->getTotalEquiposParticipantes(),
                    'total_partidos' => $this->getTotalPartidos(),
                ],
                'filtros_aplicados' => [
                    'deporte' => $request->query('deporte_id'),
                    'estado' => $request->query('estado'),
                    'formato' => $request->query('formato'),
                ],
                'generated_at' => now()->toISOString(),
            ],
            'links' => [
                'self' => $request->url(),
            ],
        ];
    }

    /**
     * Obtener distribución por estados
     *
     * @return array
     */
    private function getDistribucionEstados(): array
    {
        return [
            'planificacion' => $this->collection->where('estado', 'planificacion')->count(),
            'activo' => $this->collection->where('estado', 'activo')->count(),
            'finalizado' => $this->collection->where('estado', 'finalizado')->count(),
            'cancelado' => $this->collection->where('estado', 'cancelado')->count(),
        ];
    }

    /**
     * Obtener distribución por formatos
     *
     * @return array
     */
    private function getDistribucionFormatos(): array
    {
        return [
            'liga' => $this->collection->where('formato', 'liga')->count(),
            'eliminacion' => $this->collection->where('formato', 'eliminacion')->count(),
            'grupos' => $this->collection->where('formato', 'grupos')->count(),
        ];
    }

    /**
     * Obtener distribución por deportes
     *
     * @return array
     */
    private function getDistribucionDeportes(): array
    {
        return $this->collection
            ->filter(function ($torneo) {
                return $torneo->relationLoaded('deporte') && $torneo->deporte;
            })
            ->groupBy('deporte.nombre')
            ->map(function ($group) {
                return $group->count();
            })
            ->toArray();
    }

    /**
     * Obtener total de equipos participantes
     *
     * @return int
     */
    private function getTotalEquiposParticipantes(): int
    {
        return $this->collection->sum(function ($torneo) {
            return $torneo->relationLoaded('equipos') ? $torneo->equipos->count() : 0;
        });
    }

    /**
     * Obtener total de partidos
     *
     * @return int
     */
    private function getTotalPartidos(): int
    {
        return $this->collection->sum(function ($torneo) {
            return $torneo->relationLoaded('partidos') ? $torneo->partidos->count() : 0;
        });
    }
}
