<?php

/**
 * CONTROLLER: PARTIDOS
 * 
 * Comando para crear: php artisan make:controller PartidoController --api --resource
 * Archivo: app/Http/Controllers/PartidoController.php
 * 
 * Gestiona todas las operaciones relacionadas con partidos
 */

namespace App\Http\Controllers;

use App\Models\Partido;
use App\Models\Torneo;
use App\Models\Equipo;
use App\Models\Arbitro;
use App\Models\Evento;
use App\Models\Clasificacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PartidoController extends Controller
{
  /**
   * Listar todos los partidos con filtros
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    try {
      $query = Partido::with([
        'torneo',
        'equipoLocal',
        'equipoVisitante',
        'arbitro.usuario'
      ]);

      // Filtro por torneo
      if ($request->has('torneo_id')) {
        $query->where('torneo_id', $request->torneo_id);
      }

      // Filtro por equipo (local o visitante)
      if ($request->has('equipo_id')) {
        $equipoId = $request->equipo_id;
        $query->where(function ($q) use ($equipoId) {
          $q->where('equipo_local_id', $equipoId)
            ->orWhere('equipo_visitante_id', $equipoId);
        });
      }

      // Filtro por estado
      if ($request->has('estado')) {
        $query->where('estado', $request->estado);
      }

      // Filtro por fechas
      if ($request->has('fecha_desde')) {
        $query->where('fecha', '>=', $request->fecha_desde);
      }

      if ($request->has('fecha_hasta')) {
        $query->where('fecha', '<=', $request->fecha_hasta);
      }

      // Filtro por árbitro
      if ($request->has('arbitro_id')) {
        $query->where('arbitro_id', $request->arbitro_id);
      }

      // Ordenamiento
      $ordenarPor = $request->get('ordenar_por', 'fecha');
      $direccion = $request->get('direccion', 'desc');
      $query->orderBy($ordenarPor, $direccion);

      // Paginación
      $perPage = $request->get('per_page', 15);
      $partidos = $query->paginate($perPage);

      return response()->json([
        'success' => true,
        'data' => $partidos,
        'message' => 'Partidos obtenidos exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al obtener partidos',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Crear un nuevo partido
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function store(Request $request): JsonResponse
  {
    try {
      // Validaciones
      $validator = Validator::make($request->all(), [
        'torneo_id' => 'required|exists:torneos,id',
        'equipo_local_id' => 'required|exists:equipos,id',
        'equipo_visitante_id' => 'required|exists:equipos,id|different:equipo_local_id',
        'fecha' => 'required|date|after:now',
        'lugar' => 'nullable|string|max:150',
        'arbitro_id' => 'nullable|exists:arbitros,id',
        'observaciones' => 'nullable|string',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Verificar que ambos equipos pertenezcan al torneo
      $torneo = Torneo::findOrFail($request->torneo_id);

      $equipoLocalEnTorneo = $torneo->equipos()->where('equipo_id', $request->equipo_local_id)->exists();
      $equipoVisitanteEnTorneo = $torneo->equipos()->where('equipo_id', $request->equipo_visitante_id)->exists();

      if (!$equipoLocalEnTorneo || !$equipoVisitanteEnTorneo) {
        return response()->json([
          'success' => false,
          'message' => 'Ambos equipos deben estar inscritos en el torneo'
        ], 400);
      }

      // Verificar disponibilidad del árbitro
      if ($request->arbitro_id) {
        $arbitroOcupado = Partido::where('arbitro_id', $request->arbitro_id)
          ->where('fecha', $request->fecha)
          ->where('estado', '!=', 'cancelado')
          ->exists();

        if ($arbitroOcupado) {
          return response()->json([
            'success' => false,
            'message' => 'El árbitro ya tiene un partido asignado en esa fecha y hora'
          ], 400);
        }
      }

      // Crear partido
      $partido = Partido::create([
        'torneo_id' => $request->torneo_id,
        'equipo_local_id' => $request->equipo_local_id,
        'equipo_visitante_id' => $request->equipo_visitante_id,
        'fecha' => $request->fecha,
        'lugar' => $request->lugar,
        'estado' => 'programado',
        'arbitro_id' => $request->arbitro_id,
        'goles_local' => 0,
        'goles_visitante' => 0,
        'observaciones' => $request->observaciones,
      ]);

      // Cargar relaciones
      $partido->load([
        'torneo',
        'equipoLocal',
        'equipoVisitante',
        'arbitro.usuario'
      ]);

      return response()->json([
        'success' => true,
        'data' => $partido,
        'message' => 'Partido creado exitosamente'
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al crear partido',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Mostrar un partido específico
   * 
   * @param int $id
   * @return JsonResponse
   */
  public function show($id): JsonResponse
  {
    try {
      $partido = Partido::with([
        'torneo',
        'equipoLocal.jugadores.usuario',
        'equipoVisitante.jugadores.usuario',
        'arbitro.usuario',
        'eventos.jugador.usuario'
      ])->findOrFail($id);

      return response()->json([
        'success' => true,
        'data' => $partido,
        'message' => 'Partido obtenido exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Partido no encontrado',
        'error' => $e->getMessage()
      ], 404);
    }
  }

  /**
   * Actualizar un partido
   * 
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function update(Request $request, $id): JsonResponse
  {
    try {
      $partido = Partido::findOrFail($id);

      // Validaciones
      $validator = Validator::make($request->all(), [
        'fecha' => 'sometimes|date',
        'lugar' => 'nullable|string|max:150',
        'estado' => 'sometimes|in:programado,en_curso,finalizado,suspendido,cancelado',
        'arbitro_id' => 'nullable|exists:arbitros,id',
        'goles_local' => 'sometimes|integer|min:0',
        'goles_visitante' => 'sometimes|integer|min:0',
        'observaciones' => 'nullable|string',
        'estadisticas' => 'nullable|array',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Verificar si el partido puede ser modificado
      if ($partido->estado === 'finalizado' && !$request->has('observaciones')) {
        return response()->json([
          'success' => false,
          'message' => 'No se puede modificar un partido finalizado'
        ], 400);
      }

      // Verificar disponibilidad del árbitro si se cambia
      if ($request->has('arbitro_id') && $request->arbitro_id) {
        $fechaPartido = $request->get('fecha', $partido->fecha);
        $arbitroOcupado = Partido::where('arbitro_id', $request->arbitro_id)
          ->where('fecha', $fechaPartido)
          ->where('id', '!=', $id)
          ->where('estado', '!=', 'cancelado')
          ->exists();

        if ($arbitroOcupado) {
          return response()->json([
            'success' => false,
            'message' => 'El árbitro ya tiene un partido asignado en esa fecha y hora'
          ], 400);
        }
      }

      // Si se actualiza el resultado, generar resultado automáticamente
      if ($request->has(['goles_local', 'goles_visitante'])) {
        $request->merge([
          'resultado' => $request->goles_local . '-' . $request->goles_visitante
        ]);

        // Si se finaliza el partido, actualizar clasificaciones
        if ($request->get('estado') === 'finalizado') {
          $this->actualizarClasificaciones($partido, $request->goles_local, $request->goles_visitante);
        }
      }

      // Actualizar partido
      $partido->update($request->all());
      $partido->load([
        'torneo',
        'equipoLocal',
        'equipoVisitante',
        'arbitro.usuario'
      ]);

      return response()->json([
        'success' => true,
        'data' => $partido,
        'message' => 'Partido actualizado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al actualizar partido',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Eliminar un partido
   * 
   * @param int $id
   * @return JsonResponse
   */
  public function destroy($id): JsonResponse
  {
    try {
      $partido = Partido::findOrFail($id);

      // Verificar si el partido puede ser eliminado
      if ($partido->estado === 'finalizado') {
        return response()->json([
          'success' => false,
          'message' => 'No se puede eliminar un partido finalizado'
        ], 400);
      }

      // Soft delete
      $partido->delete();

      return response()->json([
        'success' => true,
        'message' => 'Partido eliminado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al eliminar partido',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Iniciar partido
   * 
   * @param int $id
   * @return JsonResponse
   */
  public function iniciarPartido($id): JsonResponse
  {
    try {
      $partido = Partido::findOrFail($id);

      if ($partido->estado !== 'programado') {
        return response()->json([
          'success' => false,
          'message' => 'Solo se pueden iniciar partidos programados'
        ], 400);
      }

      $partido->update(['estado' => 'en_curso']);

      // Registrar evento de inicio
      Evento::create([
        'partido_id' => $partido->id,
        'tipo' => 'otro',
        'minuto' => 0,
        'descripcion' => 'Inicio del partido',
      ]);

      return response()->json([
        'success' => true,
        'data' => $partido,
        'message' => 'Partido iniciado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al iniciar partido',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Finalizar partido
   * 
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function finalizarPartido(Request $request, $id): JsonResponse
  {
    try {
      $partido = Partido::findOrFail($id);

      if ($partido->estado !== 'en_curso') {
        return response()->json([
          'success' => false,
          'message' => 'Solo se pueden finalizar partidos en curso'
        ], 400);
      }

      $validator = Validator::make($request->all(), [
        'goles_local' => 'required|integer|min:0',
        'goles_visitante' => 'required|integer|min:0',
        'observaciones' => 'nullable|string',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Actualizar partido
      $partido->update([
        'estado' => 'finalizado',
        'goles_local' => $request->goles_local,
        'goles_visitante' => $request->goles_visitante,
        'resultado' => $request->goles_local . '-' . $request->goles_visitante,
        'observaciones' => $request->observaciones,
      ]);

      // Registrar evento de finalización
      Evento::create([
        'partido_id' => $partido->id,
        'tipo' => 'otro',
        'minuto' => 90, // Asumiendo 90 minutos
        'descripcion' => 'Final del partido',
      ]);

      // Actualizar clasificaciones
      $this->actualizarClasificaciones($partido, $request->goles_local, $request->goles_visitante);

      // Actualizar estadísticas del árbitro
      if ($partido->arbitro) {
        $partido->arbitro->increment('partidos_arbitrados');
      }

      return response()->json([
        'success' => true,
        'data' => $partido,
        'message' => 'Partido finalizado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al finalizar partido',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Agregar evento al partido
   * 
   * @param Request $request
   * @param int $partidoId
   * @return JsonResponse
   */
  public function agregarEvento(Request $request, $partidoId): JsonResponse
  {
    try {
      $partido = Partido::findOrFail($partidoId);

      if ($partido->estado !== 'en_curso') {
        return response()->json([
          'success' => false,
          'message' => 'Solo se pueden agregar eventos a partidos en curso'
        ], 400);
      }

      $validator = Validator::make($request->all(), [
        'tipo' => 'required|in:gol,tarjeta_amarilla,tarjeta_roja,sustitucion,otro',
        'minuto' => 'required|integer|min:0|max:120',
        'descripcion' => 'required|string',
        'jugador_id' => 'nullable|exists:jugadores,id',
        'valor' => 'nullable|numeric',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Crear evento
      $evento = Evento::create([
        'partido_id' => $partidoId,
        'tipo' => $request->tipo,
        'minuto' => $request->minuto,
        'descripcion' => $request->descripcion,
        'jugador_id' => $request->jugador_id,
        'valor' => $request->valor,
      ]);

      // Si es gol, actualizar contador
      if ($request->tipo === 'gol') {
        $this->actualizarGolesPartido($partido, $request->jugador_id);
      }

      $evento->load('jugador.usuario');

      return response()->json([
        'success' => true,
        'data' => $evento,
        'message' => 'Evento agregado exitosamente'
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al agregar evento',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Obtener eventos del partido
   * 
   * @param int $partidoId
   * @return JsonResponse
   */
  public function eventos($partidoId): JsonResponse
  {
    try {
      $eventos = Evento::where('partido_id', $partidoId)
        ->with('jugador.usuario')
        ->orderBy('minuto')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $eventos,
        'message' => 'Eventos obtenidos exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al obtener eventos',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Actualizar clasificaciones después de un partido
   * 
   * @param Partido $partido
   * @param int $golesLocal
   * @param int $golesVisitante
   * @return void
   */
  private function actualizarClasificaciones(Partido $partido, int $golesLocal, int $golesVisitante): void
  {
    try {
      // Obtener o crear clasificación para equipo local
      $clasificacionLocal = Clasificacion::firstOrCreate(
        [
          'torneo_id' => $partido->torneo_id,
          'equipo_id' => $partido->equipo_local_id,
        ],
        [
          'posicion' => 0,
          'puntos' => 0,
          'partidos_jugados' => 0,
          'ganados' => 0,
          'empatados' => 0,
          'perdidos' => 0,
          'goles_favor' => 0,
          'goles_contra' => 0,
        ]
      );

      // Obtener o crear clasificación para equipo visitante
      $clasificacionVisitante = Clasificacion::firstOrCreate(
        [
          'torneo_id' => $partido->torneo_id,
          'equipo_id' => $partido->equipo_visitante_id,
        ],
        [
          'posicion' => 0,
          'puntos' => 0,
          'partidos_jugados' => 0,
          'ganados' => 0,
          'empatados' => 0,
          'perdidos' => 0,
          'goles_favor' => 0,
          'goles_contra' => 0,
        ]
      );

      // Actualizar estadísticas
      $clasificacionLocal->increment('partidos_jugados');
      $clasificacionLocal->increment('goles_favor', $golesLocal);
      $clasificacionLocal->increment('goles_contra', $golesVisitante);

      $clasificacionVisitante->increment('partidos_jugados');
      $clasificacionVisitante->increment('goles_favor', $golesVisitante);
      $clasificacionVisitante->increment('goles_contra', $golesLocal);

      // Determinar resultado y puntos
      if ($golesLocal > $golesVisitante) {
        // Victoria local
        $clasificacionLocal->increment('ganados');
        $clasificacionLocal->increment('puntos', 3);
        $clasificacionVisitante->increment('perdidos');
      } elseif ($golesLocal < $golesVisitante) {
        // Victoria visitante
        $clasificacionVisitante->increment('ganados');
        $clasificacionVisitante->increment('puntos', 3);
        $clasificacionLocal->increment('perdidos');
      } else {
        // Empate
        $clasificacionLocal->increment('empatados');
        $clasificacionLocal->increment('puntos', 1);
        $clasificacionVisitante->increment('empatados');
        $clasificacionVisitante->increment('puntos', 1);
      }
    } catch (\Exception $e) {
      // Log error but don't fail the main operation
      Log::error('Error actualizando clasificaciones: ' . $e->getMessage());
    }
  }

  /**
   * Actualizar goles del partido cuando se marca un gol
   * 
   * @param Partido $partido
   * @param int|null $jugadorId
   * @return void
   */
  private function actualizarGolesPartido(Partido $partido, ?int $jugadorId): void
  {
    if (!$jugadorId) return;

    // Determinar si el jugador pertenece al equipo local o visitante
    $equipoLocal = $partido->equipoLocal->jugadores()->where('jugador_id', $jugadorId)->exists();

    if ($equipoLocal) {
      $partido->increment('goles_local');
    } else {
      $partido->increment('goles_visitante');
    }

    // Actualizar resultado
    $partido->update([
      'resultado' => $partido->goles_local . '-' . $partido->goles_visitante
    ]);
  }
}
