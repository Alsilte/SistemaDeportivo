<?php

/**
 * COLLECTION: USUARIOS
 * 
 * Comando para crear: php artisan make:resource UserCollection
 * Archivo: app/Http/Resources/UserCollection.php
 * 
 * Formatea la respuesta JSON para colecciones de usuarios
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($user) use ($request) {
                return [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                    'tipo_usuario' => $user->tipo_usuario,
                    'tipo_usuario_label' => $this->getTipoUsuarioLabel($user->tipo_usuario),
                    'activo' => $user->activo,
                    'estado_label' => $user->activo ? 'Activo' : 'Inactivo',
                    'fecha_registro' => $user->created_at?->format('Y-m-d'),
                    'dias_registrado' => $user->created_at ? $user->created_at->diffInDays(now()) : 0,

                    // Información específica del perfil (resumida)
                    'perfil_info' => $this->getPerfilInfo($user),

                    // Estadísticas rápidas
                    'stats' => $this->getStatsRapidas($user, $request),
                ];
            }),
        ];
    }

    /**
     * Obtener etiqueta del tipo de usuario
     *
     * @param string $tipo
     * @return string
     */
    private function getTipoUsuarioLabel(string $tipo): string
    {
        return match ($tipo) {
            'jugador' => 'Jugador',
            'arbitro' => 'Árbitro',
            'administrador' => 'Administrador',
            default => ucfirst($tipo),
        };
    }

    /**
     * Obtener información resumida del perfil
     *
     * @param $user
     * @return array|null
     */
    private function getPerfilInfo($user): ?array
    {
        switch ($user->tipo_usuario) {
            case 'jugador':
                if ($user->relationLoaded('jugador') && $user->jugador) {
                    return [
                        'posicion' => $user->jugador->posicion,
                        'equipos_actuales' => $user->jugador->equipos ? $user->jugador->equipos->where('pivot.estado', 'activo')->count() : 0,
                        'partidos_jugados' => $user->jugador->partidos_jugados,
                        'goles' => $user->jugador->goles_favor,
                    ];
                }
                break;

            case 'arbitro':
                if ($user->relationLoaded('arbitro') && $user->arbitro) {
                    return [
                        'licencia' => $user->arbitro->licencia,
                        'posicion' => $user->arbitro->posicion,
                        'partidos_arbitrados' => $user->arbitro->partidos_arbitrados,
                    ];
                }
                break;

            case 'administrador':
                if ($user->relationLoaded('administrador') && $user->administrador) {
                    return [
                        'nivel_acceso' => $user->administrador->nivel_acceso,
                        'equipos_gestionados' => $user->equiposAdministrados ? $user->equiposAdministrados->count() : 0,
                    ];
                }
                break;
        }

        return null;
    }

    /**
     * Obtener estadísticas rápidas
     *
     * @param $user
     * @param Request $request
     * @return array
     */
    private function getStatsRapidas($user, Request $request): array
    {
        // Solo incluir stats si se solicita explícitamente
        if ($request->query('include_stats') !== 'true') {
            return [];
        }

        $stats = [];

        switch ($user->tipo_usuario) {
            case 'jugador':
                if ($user->jugador) {
                    $stats = [
                        'efectividad' => $user->jugador->partidos_jugados > 0
                            ? round(($user->jugador->ganados / $user->jugador->partidos_jugados) * 100, 2)
                            : 0,
                        'diferencia_goles' => $user->jugador->goles_favor - $user->jugador->goles_contra,
                    ];
                }
                break;

            case 'arbitro':
                if ($user->arbitro) {
                    $stats = [
                        'experiencia_meses' => $user->created_at ? $user->created_at->diffInMonths(now()) : 0,
                        'promedio_partidos_mes' => $user->arbitro->partidos_arbitrados > 0 && $user->created_at
                            ? round($user->arbitro->partidos_arbitrados / max(1, $user->created_at->diffInMonths(now())), 2)
                            : 0,
                    ];
                }
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
                'total' => $this->collection->count(),
                'tipos' => $this->getTiposDistribucion(),
                'activos' => $this->collection->where('activo', true)->count(),
                'inactivos' => $this->collection->where('activo', false)->count(),
                'include_stats' => $request->query('include_stats') === 'true',
                'generated_at' => now()->toISOString(),
            ],
            'links' => [
                'self' => $request->url(),
            ],
        ];
    }

    /**
     * Obtener distribución por tipos de usuario
     *
     * @return array
     */
    private function getTiposDistribucion(): array
    {
        return [
            'jugadores' => $this->collection->where('tipo_usuario', 'jugador')->count(),
            'arbitros' => $this->collection->where('tipo_usuario', 'arbitro')->count(),
            'administradores' => $this->collection->where('tipo_usuario', 'administrador')->count(),
        ];
    }
}
