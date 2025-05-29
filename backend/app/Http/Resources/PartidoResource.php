<?php

/**
 * RESOURCE: PARTIDO
 * 
 * Comando para crear: php artisan make:resource PartidoResource
 * Archivo: app/Http/Resources/PartidoResource.php
 * 
 * Formatea la respuesta JSON para partidos
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PartidoResource extends JsonResource
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
            'fecha' => $this->fecha?->format('Y-m-d H:i:s'),
            'fecha_formateada' => $this->fecha?->format('d/m/Y H:i'),
            'lugar' => $this->lugar,
            'estado' => $this->estado,
            'estado_label' => $this->getEstadoLabel(),
            'resultado' => $this->resultado,

            // Información de fecha y tiempo
            'fecha_info' => [
                'es_hoy' => $this->fecha ? $this->fecha->isToday() : false,
                'es_manana' => $this->fecha ? $this->fecha->isTomorrow() : false,
                'es_pasado' => $this->fecha ? $this->fecha->isPast() : false,
                'dias_restantes' => $this->fecha && $this->fecha->isFuture()
                    ? $this->fecha->diffInDays(now())
                    : null,
                'hace_dias' => $this->fecha && $this->fecha->isPast()
                    ? $this->fecha->diffInDays(now())
                    : null,
                'dia_semana' => $this->fecha?->locale('es')->dayName,
                'hora' => $this->fecha?->format('H:i'),
            ],

            // Información del torneo
            'torneo' => $this->when(
                $this->relationLoaded('torneo'),
                function () {
                    return [
                        'id' => $this->torneo->id,
                        'nombre' => $this->torneo->nombre,
                        'formato' => $this->torneo->formato,
                        'estado' => $this->torneo->estado,
                        'deporte' => $this->torneo->deporte->nombre ?? null,
                    ];
                }
            ),

            // Equipos participantes
            'equipo_local' => $this->when(
                $this->relationLoaded('equipoLocal'),
                function () {
                    return [
                        'id' => $this->equipoLocal->id,
                        'nombre' => $this->equipoLocal->nombre,
                        'logo' => $this->equipoLocal->logo ? asset('storage/' . $this->equipoLocal->logo) : null,
                        'goles' => $this->goles_local,
                    ];
                }
            ),

            'equipo_visitante' => $this->when(
                $this->relationLoaded('equipoVisitante'),
                function () {
                    return [
                        'id' => $this->equipoVisitante->id,
                        'nombre' => $this->equipoVisitante->nombre,
                        'logo' => $this->equipoVisitante->logo ? asset('storage/' . $this->equipoVisitante->logo) : null,
                        'goles' => $this->goles_visitante,
                    ];
                }
            ),

            // Resultado del partido
            'resultado_detalle' => [
                'goles_local' => $this->goles_local,
                'goles_visitante' => $this->goles_visitante,
                'diferencia_goles' => abs($this->goles_local - $this->goles_visitante),
                'ganador' => $this->getGanador(),
                'es_empate' => $this->goles_local === $this->goles_visitante,
                'resultado_texto' => $this->getResultadoTexto(),
            ],

            // Información del árbitro
            'arbitro' => $this->when(
                $this->relationLoaded('arbitro'),
                function () {
                    return $this->arbitro ? [
                        'id' => $this->arbitro->id,
                        'nombre' => $this->arbitro->usuario->nombre ?? null,
                        'licencia' => $this->arbitro->licencia,
                        'posicion' => $this->arbitro->posicion,
                        'experiencia' => $this->arbitro->partidos_arbitrados ?? 0,
                    ] : null;
                }
            ),

            // Eventos del partido
            'eventos_resumen' => $this->when(
                $this->relationLoaded('eventos'),
                function () {
                    return [
                        'total' => $this->eventos->count(),
                        'goles' => $this->eventos->where('tipo', 'gol')->count(),
                        'tarjetas_amarillas' => $this->eventos->where('tipo', 'tarjeta_amarilla')->count(),
                        'tarjetas_rojas' => $this->eventos->where('tipo', 'tarjeta_roja')->count(),
                        'sustituciones' => $this->eventos->where('tipo', 'sustitucion')->count(),
                    ];
                }
            ),

            // Eventos detallados
            'eventos' => $this->when(
                $this->relationLoaded('eventos') && $request->query('include_events') === 'true',
                function () {
                    return $this->eventos->sortBy('minuto')->map(function ($evento) {
                        return [
                            'id' => $evento->id,
                            'tipo' => $evento->tipo,
                            'tipo_label' => $this->getTipoEventoLabel($evento->tipo),
                            'minuto' => $evento->minuto,
                            'descripcion' => $evento->descripcion,
                            'valor' => $evento->valor,
                            'jugador' => $evento->jugador ? [
                                'id' => $evento->jugador->id,
                                'nombre' => $evento->jugador->usuario->nombre ?? null,
                            ] : null,
                        ];
                    })->values();
                }
            ),

            // Estadísticas del partido
            'estadisticas' => $this->estadisticas ?? [],
            'estadisticas_formateadas' => $this->getEstadisticasFormateadas(),

            // Observaciones
            'observaciones' => $this->observaciones,
            'tiene_observaciones' => !empty($this->observaciones),

            // Información de control
            'se_puede_iniciar' => $this->sePuedeIniciar(),
            'se_puede_finalizar' => $this->sePuedeFinalizar(),
            'se_puede_editar' => $this->sePuedeEditar(),
            'se_puede_cancelar' => $this->sePuedeCancelar(),

            // Metadatos
            'creado_en' => $this->created_at?->format('Y-m-d H:i:s'),
            'actualizado_en' => $this->updated_at?->format('Y-m-d H:i:s'),
            'duracion_estimada' => $this->getDuracionEstimada(),
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
            'programado' => 'Programado',
            'en_curso' => 'En Curso',
            'finalizado' => 'Finalizado',
            'suspendido' => 'Suspendido',
            'cancelado' => 'Cancelado',
            default => 'Desconocido',
        };
    }

    /**
     * Obtener ganador del partido
     *
     * @return string|null
     */
    private function getGanador(): ?string
    {
        if ($this->estado !== 'finalizado') {
            return null;
        }

        if ($this->goles_local > $this->goles_visitante) {
            return 'local';
        } elseif ($this->goles_local < $this->goles_visitante) {
            return 'visitante';
        }

        return 'empate';
    }

    /**
     * Obtener texto del resultado
     *
     * @return string
     */
    private function getResultadoTexto(): string
    {
        if ($this->estado !== 'finalizado') {
            return 'Por jugar';
        }

        $ganador = $this->getGanador();

        return match ($ganador) {
            'local' => 'Victoria Local',
            'visitante' => 'Victoria Visitante',
            'empate' => 'Empate',
            default => 'Sin resultado',
        };
    }

    /**
     * Obtener etiqueta del tipo de evento
     *
     * @param string $tipo
     * @return string
     */
    private function getTipoEventoLabel(string $tipo): string
    {
        return match ($tipo) {
            'gol' => 'Gol',
            'tarjeta_amarilla' => 'Tarjeta Amarilla',
            'tarjeta_roja' => 'Tarjeta Roja',
            'sustitucion' => 'Sustitución',
            'otro' => 'Otro',
            default => ucfirst($tipo),
        };
    }

    /**
     * Obtener estadísticas formateadas
     *
     * @return array
     */
    private function getEstadisticasFormateadas(): array
    {
        $stats = $this->estadisticas ?? [];

        if (empty($stats)) {
            return [];
        }

        return [
            'posesion' => [
                'local' => $stats['posesion_local'] ?? null,
                'visitante' => $stats['posesion_visitante'] ?? null,
            ],
            'tarjetas' => [
                'amarillas' => $stats['tarjetas_amarillas'] ?? 0,
                'rojas' => $stats['tarjetas_rojas'] ?? 0,
            ],
            'corners' => [
                'local' => $stats['corners_local'] ?? 0,
                'visitante' => $stats['corners_visitante'] ?? 0,
            ],
            'faltas' => [
                'local' => $stats['faltas_local'] ?? 0,
                'visitante' => $stats['faltas_visitante'] ?? 0,
            ],
        ];
    }

    /**
     * Verificar si se puede iniciar el partido
     *
     * @return bool
     */
    private function sePuedeIniciar(): bool
    {
        return $this->estado === 'programado' &&
            $this->fecha &&
            $this->fecha->isBefore(now()->addHours(2)); // Puede iniciarse hasta 2 horas antes
    }

    /**
     * Verificar si se puede finalizar el partido
     *
     * @return bool
     */
    private function sePuedeFinalizar(): bool
    {
        return $this->estado === 'en_curso';
    }

    /**
     * Verificar si se puede editar el partido
     *
     * @return bool
     */
    private function sePuedeEditar(): bool
    {
        return in_array($this->estado, ['programado', 'suspendido']);
    }

    /**
     * Verificar si se puede cancelar el partido
     *
     * @return bool
     */
    private function sePuedeCancelar(): bool
    {
        return in_array($this->estado, ['programado', 'suspendido']);
    }

    /**
     * Obtener duración estimada del partido
     *
     * @return string
     */
    private function getDuracionEstimada(): string
    {
        if (!$this->relationLoaded('torneo') || !$this->torneo) {
            return '90 minutos';
        }

        $deporte = $this->torneo->deporte->nombre ?? 'Fútbol';

        return match (strtolower($deporte)) {
            'fútbol' => '90 minutos',
            'baloncesto' => '48 minutos (4 cuartos)',
            'voleibol' => 'Variable (por sets)',
            'tenis' => 'Variable (por sets)',
            default => 'Variable',
        };
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
                'include_events' => $request->query('include_events') === 'true',
                'timezone' => config('app.timezone'),
            ],
        ];
    }
}
