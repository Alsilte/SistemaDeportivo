<?php

/**
 * COLLECTION: EQUIPOS
 * 
 * Comando para crear: php artisan make:resource EquipoCollection
 * Archivo: app/Http/Resources/EquipoCollection.php
 * 
 * Formatea la respuesta JSON para colecciones de equipos
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EquipoCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($equipo) use ($request) {
                return [
                    'id' => $equipo->id,
                    'nombre' => $equipo->nombre,
                    'logo' => $equipo->logo ? asset('storage/' . $equipo->logo) : null,
                    'email' => $equipo->email,
                    'telefono' => $equipo->telefono,
                    'activo' => $equipo->activo,
                    'estado_label' => $equipo->activo ? 'Activo' : 'Inactivo',
                    'estado_color' => $equipo->activo ? 'green' : 'red',

                    // Información del deporte
                    'deporte' => $equipo->relationLoaded('deporte') && $equipo->deporte ? [
                        'id' => $equipo->deporte->id,
                        'nombre' => $equipo->deporte->nombre,
                        'imagen' => $equipo->deporte->imagen ? asset('storage/' . $equipo->deporte->imagen) : null,
                    ] : null,

                    // Administrador
                    'administrador' => $equipo->relationLoaded('administrador') && $equipo->administrador ? [
                        'id' => $equipo->administrador->id,
                        'nombre' => $equipo->administrador->nombre,
                        'email' => $equipo->administrador->email,
                    ] : null,

                    // Información de jugadores (resumida)
                    'jugadores_info' => [
                        'total' => $equipo->relationLoaded('jugadores') ? $equipo->jugadores->count() : 0,
                        'activos' => $equipo->relationLoaded('jugadores')
                            ? $equipo->jugadores->where('pivot.estado', 'activo')->count()
                            : 0,
                        'lesionados' => $equipo->relationLoaded('jugadores')
                            ? $equipo->jugadores->where('pivot.estado', 'lesionado')->count()
                            : 0,
                        'suspendidos' => $equipo->relationLoaded('jugadores')
                            ? $equipo->jugadores->where('pivot.estado', 'suspendido')->count()
                            : 0,
                        'capitan' => $this->getCapitan($equipo),
                    ],

                    // Información de torneos
                    'torneos_info' => [
                        'total' => $equipo->relationLoaded('torneos') ? $equipo->torneos->count() : 0,
                        'activos' => $equipo->relationLoaded('torneos')
                            ? $equipo->torneos->where('estado', 'activo')->count()
                            : 0,
                        'finalizados' => $equipo->relationLoaded('torneos')
                            ? $equipo->torneos->where('estado', 'finalizado')->count()
                            : 0,
                    ],

                    // Estadísticas básicas
                    'estadisticas_basicas' => $this->getEstadisticasBasicas($equipo),

                    // Rendimiento general
                    'rendimiento' => $this->getRendimientoGeneral($equipo),

                    // Próximo partido
                    'proximo_partido' => $this->getProximoPartido($equipo),

                    // Último resultado
                    'ultimo_resultado' => $this->getUltimoResultado($equipo),

                    // Información adicional
                    'es_reciente' => $equipo->created_at && $equipo->created_at->isAfter(now()->subDays(30)),
                    'dias_desde_creacion' => $equipo->created_at ? $equipo->created_at->diffInDays(now()) : 0,
                    'creado_en' => $equipo->created_at?->format('Y-m-d'),
                ];
            }),
        ];
    }

    /**
     * Obtener información del capitán
     *
     * @param $equipo
     * @return array|null
     */
    protected function getCapitan($equipo): ?array
    {
        if (!$equipo->relationLoaded('jugadores')) {
            return null;
        }

        $capitan = $equipo->jugadores->where('pivot.es_capitan', true)->first();

        if (!$capitan) {
            return null;
        }

        return [
            'id' => $capitan->id,
            'nombre' => $capitan->usuario->nombre ?? 'N/A',
            'numero_camiseta' => $capitan->pivot->numero_camiseta,
        ];
    }

    /**
     * Obtener estadísticas básicas
     *
     * @param $equipo
     * @return array
     */
    protected function getEstadisticasBasicas($equipo): array
    {
        if (!$equipo->relationLoaded('partidosLocal') || !$equipo->relationLoaded('partidosVisitante')) {
            return [
                'partidos_jugados' => 0,
                'victorias' => 0,
                'empates' => 0,
                'derrotas' => 0,
            ];
        }

        $partidosLocal = $equipo->partidosLocal->where('estado', 'finalizado');
        $partidosVisitante = $equipo->partidosVisitante->where('estado', 'finalizado');

        $victorias = 0;
        $empates = 0;
        $derrotas = 0;

        // Contar resultados como local
        foreach ($partidosLocal as $partido) {
            if ($partido->goles_local > $partido->goles_visitante) {
                $victorias++;
            } elseif ($partido->goles_local == $partido->goles_visitante) {
                $empates++;
            } else {
                $derrotas++;
            }
        }

        // Contar resultados como visitante
        foreach ($partidosVisitante as $partido) {
            if ($partido->goles_visitante > $partido->goles_local) {
                $victorias++;
            } elseif ($partido->goles_visitante == $partido->goles_local) {
                $empates++;
            } else {
                $derrotas++;
            }
        }

        $totalPartidos = $partidosLocal->count() + $partidosVisitante->count();

        return [
            'partidos_jugados' => $totalPartidos,
            'victorias' => $victorias,
            'empates' => $empates,
            'derrotas' => $derrotas,
        ];
    }

    /**
     * Obtener rendimiento general
     *
     * @param $equipo
     * @return array
     */
    protected function getRendimientoGeneral($equipo): array
    {
        $stats = $this->getEstadisticasBasicas($equipo);

        if ($stats['partidos_jugados'] === 0) {
            return [
                'efectividad' => 0,
                'puntos_estimados' => 0,
                'racha' => 'Sin partidos',
            ];
        }

        $efectividad = round(($stats['victorias'] / $stats['partidos_jugados']) * 100, 1);
        $puntosEstimados = ($stats['victorias'] * 3) + ($stats['empates'] * 1);

        return [
            'efectividad' => $efectividad,
            'puntos_estimados' => $puntosEstimados,
            'puntos_promedio' => round($puntosEstimados / $stats['partidos_jugados'], 2),
            'racha' => $this->getRachaActual($equipo),
        ];
    }

    /**
     * Obtener racha actual
     *
     * @param $equipo
     * @return string
     */
    protected function getRachaActual($equipo): string
    {
        if (!$equipo->relationLoaded('partidosLocal') || !$equipo->relationLoaded('partidosVisitante')) {
            return 'Sin datos';
        }

        $partidosLocal = $equipo->partidosLocal->where('estado', 'finalizado');
        $partidosVisitante = $equipo->partidosVisitante->where('estado', 'finalizado');
        $todosPartidos = $partidosLocal->concat($partidosVisitante)->sortByDesc('fecha');

        if ($todosPartidos->isEmpty()) {
            return 'Sin partidos';
        }

        $ultimoPartido = $todosPartidos->first();
        $esLocal = $ultimoPartido->equipo_local_id === $equipo->id;

        if ($esLocal) {
            if ($ultimoPartido->goles_local > $ultimoPartido->goles_visitante) {
                return 'Victoria';
            } elseif ($ultimoPartido->goles_local == $ultimoPartido->goles_visitante) {
                return 'Empate';
            } else {
                return 'Derrota';
            }
        } else {
            if ($ultimoPartido->goles_visitante > $ultimoPartido->goles_local) {
                return 'Victoria';
            } elseif ($ultimoPartido->goles_visitante == $ultimoPartido->goles_local) {
                return 'Empate';
            } else {
                return 'Derrota';
            }
        }
    }

    /**
     * Obtener próximo partido
     *
     * @param $equipo
     * @return array|null
     */
    protected function getProximoPartido($equipo): ?array
    {
        if (!$equipo->relationLoaded('partidosLocal') || !$equipo->relationLoaded('partidosVisitante')) {
            return null;
        }

        $partidosLocal = $equipo->partidosLocal
            ->where('estado', 'programado')
            ->where('fecha', '>', now());

        $partidosVisitante = $equipo->partidosVisitante
            ->where('estado', 'programado')
            ->where('fecha', '>', now());

        $proximosPartidos = $partidosLocal->concat($partidosVisitante)->sortBy('fecha');

        if ($proximosPartidos->isEmpty()) {
            return null;
        }

        $proximoPartido = $proximosPartidos->first();
        $esLocal = $proximoPartido->equipo_local_id === $equipo->id;
        $rival = $esLocal ? $proximoPartido->equipoVisitante : $proximoPartido->equipoLocal;

        return [
            'id' => $proximoPartido->id,
            'fecha' => $proximoPartido->fecha->format('Y-m-d H:i'),
            'es_local' => $esLocal,
            'rival' => [
                'id' => $rival->id,
                'nombre' => $rival->nombre,
                'logo' => $rival->logo ? asset('storage/' . $rival->logo) : null,
            ],
            'torneo' => $proximoPartido->torneo->nombre ?? 'N/A',
            'lugar' => $proximoPartido->lugar,
            'dias_restantes' => $proximoPartido->fecha->diffInDays(now()),
        ];
    }

    /**
     * Obtener último resultado
     *
     * @param $equipo
     * @return array|null
     */
    protected function getUltimoResultado($equipo): ?array
    {
        if (!$equipo->relationLoaded('partidosLocal') || !$equipo->relationLoaded('partidosVisitante')) {
            return null;
        }

        $partidosLocal = $equipo->partidosLocal->where('estado', 'finalizado');
        $partidosVisitante = $equipo->partidosVisitante->where('estado', 'finalizado');
        $todosPartidos = $partidosLocal->concat($partidosVisitante)->sortByDesc('fecha');

        if ($todosPartidos->isEmpty()) {
            return null;
        }

        $ultimoPartido = $todosPartidos->first();
        $esLocal = $ultimoPartido->equipo_local_id === $equipo->id;
        $rival = $esLocal ? $ultimoPartido->equipoVisitante : $ultimoPartido->equipoLocal;

        $golesPropio = $esLocal ? $ultimoPartido->goles_local : $ultimoPartido->goles_visitante;
        $golesRival = $esLocal ? $ultimoPartido->goles_visitante : $ultimoPartido->goles_local;

        $resultado = 'Empate';
        if ($golesPropio > $golesRival) {
            $resultado = 'Victoria';
        } elseif ($golesPropio < $golesRival) {
            $resultado = 'Derrota';
        }

        return [
            'id' => $ultimoPartido->id,
            'fecha' => $ultimoPartido->fecha->format('Y-m-d'),
            'rival' => [
                'id' => $rival->id,
                'nombre' => $rival->nombre,
                'logo' => $rival->logo ? asset('storage/' . $rival->logo) : null,
            ],
            'resultado' => $resultado,
            'goles_propio' => $golesPropio,
            'goles_rival' => $golesRival,
            'torneo' => $ultimoPartido->torneo->nombre ?? 'N/A',
            'hace_dias' => $ultimoPartido->fecha->diffInDays(now()),
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
                'por_deporte' => $this->getDistribucionDeportes(),
                'por_estado' => [
                    'activos' => $this->collection->where('activo', true)->count(),
                    'inactivos' => $this->collection->where('activo', false)->count(),
                ],
                'estadisticas_generales' => [
                    'total_jugadores' => $this->getTotalJugadores(),
                    'equipos_con_jugadores' => $this->getEquiposConJugadores(),
                    'equipos_en_torneos' => $this->getEquiposEnTorneos(),
                    'promedio_jugadores_por_equipo' => $this->getPromedioJugadoresPorEquipo(),
                ],
                'rendimiento_global' => [
                    'equipos_con_victorias' => $this->getEquiposConVictorias(),
                    'total_partidos_jugados' => $this->getTotalPartidosJugados(),
                    'promedio_efectividad' => $this->getPromedioEfectividad(),
                ],
                'filtros_aplicados' => [
                    'deporte' => $request->query('deporte_id'),
                    'activo' => $request->query('activo'),
                    'administrador' => $request->query('administrador_id'),
                ],
                'generated_at' => now()->toISOString(),
            ],
            'links' => [
                'self' => $request->url(),
            ],
        ];
    }

    /**
     * Obtener distribución por deportes
     *
     * @return array
     */
    protected function getDistribucionDeportes(): array
    {
        return $this->collection
            ->filter(function ($equipo) {
                return $equipo->relationLoaded('deporte') && $equipo->deporte;
            })
            ->groupBy('deporte.nombre')
            ->map(function ($group) {
                return $group->count();
            })
            ->toArray();
    }

    /**
     * Obtener total de jugadores
     *
     * @return int
     */
    protected function getTotalJugadores(): int
    {
        return $this->collection->sum(function ($equipo) {
            return $equipo->relationLoaded('jugadores') ? $equipo->jugadores->count() : 0;
        });
    }

    /**
     * Obtener equipos con jugadores
     *
     * @return int
     */
    protected function getEquiposConJugadores(): int
    {
        return $this->collection->filter(function ($equipo) {
            return $equipo->relationLoaded('jugadores') && $equipo->jugadores->count() > 0;
        })->count();
    }

    /**
     * Obtener equipos en torneos
     *
     * @return int
     */
    protected function getEquiposEnTorneos(): int
    {
        return $this->collection->filter(function ($equipo) {
            return $equipo->relationLoaded('torneos') && $equipo->torneos->count() > 0;
        })->count();
    }

    /**
     * Obtener promedio de jugadores por equipo
     *
     * @return float
     */
    protected function getPromedioJugadoresPorEquipo(): float
    {
        $totalEquipos = $this->collection->count();
        if ($totalEquipos === 0) {
            return 0;
        }

        $totalJugadores = $this->getTotalJugadores();
        return round($totalJugadores / $totalEquipos, 1);
    }

    /**
     * Obtener equipos con victorias
     *
     * @return int
     */
    protected function getEquiposConVictorias(): int
    {
        return $this->collection->filter(function ($equipo) {
            $stats = $this->getEstadisticasBasicas($equipo);
            return $stats['victorias'] > 0;
        })->count();
    }

    /**
     * Obtener total de partidos jugados
     *
     * @return int
     */
    protected function getTotalPartidosJugados(): int
    {
        return $this->collection->sum(function ($equipo) {
            $stats = $this->getEstadisticasBasicas($equipo);
            return $stats['partidos_jugados'];
        });
    }

    /**
     * Obtener promedio de efectividad
     *
     * @return float
     */
    protected function getPromedioEfectividad(): float
    {
        $equiposConPartidos = $this->collection->filter(function ($equipo) {
            $stats = $this->getEstadisticasBasicas($equipo);
            return $stats['partidos_jugados'] > 0;
        });

        if ($equiposConPartidos->count() === 0) {
            return 0;
        }

        $sumaEfectividad = $equiposConPartidos->sum(function ($equipo) {
            $rendimiento = $this->getRendimientoGeneral($equipo);
            return $rendimiento['efectividad'];
        });

        return round($sumaEfectividad / $equiposConPartidos->count(), 1);
    }
}
