<?php

/**
 * CONTROLLER: EQUIPOS
 * 
 * Comando para crear: php artisan make:controller EquipoController --api --resource
 * Archivo: app/Http/Controllers/EquipoController.php
 * 
 * Gestiona todas las operaciones relacionadas con equipos
 */

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Jugador;
use App\Models\Deporte;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EquipoController extends Controller
{
  /**
   * Listar todos los equipos con filtros
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    try {
      $query = Equipo::with(['deporte', 'administrador', 'jugadores.usuario']);

      // Filtro por deporte
      if ($request->has('deporte_id')) {
        $query->where('deporte_id', $request->deporte_id);
      }

      // Filtro por estado
      if ($request->has('activo')) {
        $query->where('activo', $request->boolean('activo'));
      }

      // Búsqueda por nombre
      if ($request->has('buscar')) {
        $query->where('nombre', 'like', '%' . $request->buscar . '%');
      }

      // Filtro por administrador
      if ($request->has('administrador_id')) {
        $query->where('administrador_id', $request->administrador_id);
      }

      // Ordenamiento
      $ordenarPor = $request->get('ordenar_por', 'nombre');
      $direccion = $request->get('direccion', 'asc');
      $query->orderBy($ordenarPor, $direccion);

      // Paginación
      $perPage = $request->get('per_page', 15);
      $equipos = $query->paginate($perPage);

      // Agregar estadísticas a cada equipo
      $equipos->getCollection()->transform(function ($equipo) {
        $equipo->estadisticas = $this->obtenerEstadisticasEquipo($equipo->id);
        return $equipo;
      });

      return response()->json([
        'success' => true,
        'data' => $equipos,
        'message' => 'Equipos obtenidos exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al obtener equipos',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Crear un nuevo equipo
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function store(Request $request): JsonResponse
  {
    try {
      // Validaciones
      $validator = Validator::make($request->all(), [
        'nombre' => 'required|string|max:100',
        'email' => 'nullable|email|max:100',
        'telefono' => 'nullable|string|max:20',
        'deporte_id' => 'required|exists:deportes,id',
        'administrador_id' => 'nullable|exists:usuarios,id',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Verificar nombre único por deporte
      $existeNombre = Equipo::where('nombre', $request->nombre)
        ->where('deporte_id', $request->deporte_id)
        ->exists();

      if ($existeNombre) {
        return response()->json([
          'success' => false,
          'message' => 'Ya existe un equipo con ese nombre en este deporte'
        ], 422);
      }

      // Subir logo si existe
      $logoPath = null;
      if ($request->hasFile('logo')) {
        $logoPath = $this->subirLogo($request->file('logo'));
      }

      // Crear equipo
      $equipo = Equipo::create([
        'nombre' => $request->nombre,
        'email' => $request->email,
        'telefono' => $request->telefono,
        'deporte_id' => $request->deporte_id,
        'administrador_id' => $request->administrador_id,
        'logo' => $logoPath,
        'activo' => true,
      ]);

      // Cargar relaciones
      $equipo->load(['deporte', 'administrador', 'jugadores']);

      return response()->json([
        'success' => true,
        'data' => $equipo,
        'message' => 'Equipo creado exitosamente'
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al crear equipo',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Mostrar un equipo específico
   * 
   * @param int $id
   * @return JsonResponse
   */
  public function show($id): JsonResponse
  {
    try {
      $equipo = Equipo::with([
        'deporte',
        'administrador',
        'jugadores.usuario',
        'torneos',
        'partidosLocal.equipoVisitante',
        'partidosVisitante.equipoLocal',
        'clasificaciones.torneo'
      ])->findOrFail($id);

      // Agregar estadísticas detalladas
      $equipo->estadisticas = $this->obtenerEstadisticasDetalladasEquipo($id);

      return response()->json([
        'success' => true,
        'data' => $equipo,
        'message' => 'Equipo obtenido exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Equipo no encontrado',
        'error' => $e->getMessage()
      ], 404);
    }
  }

  /**
   * Actualizar un equipo
   * 
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function update(Request $request, $id): JsonResponse
  {
    try {
      $equipo = Equipo::findOrFail($id);

      // Validaciones
      $validator = Validator::make($request->all(), [
        'nombre' => 'sometimes|string|max:100',
        'email' => 'nullable|email|max:100',
        'telefono' => 'nullable|string|max:20',
        'deporte_id' => 'sometimes|exists:deportes,id',
        'administrador_id' => 'nullable|exists:usuarios,id',
        'activo' => 'sometimes|boolean',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Verificar nombre único por deporte (excluyendo el actual)
      if ($request->has('nombre') && $request->has('deporte_id')) {
        $existeNombre = Equipo::where('nombre', $request->nombre)
          ->where('deporte_id', $request->deporte_id)
          ->where('id', '!=', $id)
          ->exists();

        if ($existeNombre) {
          return response()->json([
            'success' => false,
            'message' => 'Ya existe un equipo con ese nombre en este deporte'
          ], 422);
        }
      }

      // Subir nuevo logo si existe
      if ($request->hasFile('logo')) {
        // Eliminar logo anterior
        if ($equipo->logo) {
          Storage::delete($equipo->logo);
        }
        $request->merge(['logo' => $this->subirLogo($request->file('logo'))]);
      }

      // Actualizar equipo
      $equipo->update($request->all());
      $equipo->load(['deporte', 'administrador', 'jugadores']);

      return response()->json([
        'success' => true,
        'data' => $equipo,
        'message' => 'Equipo actualizado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al actualizar equipo',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Eliminar un equipo
   * 
   * @param int $id
   * @return JsonResponse
   */
  public function destroy($id): JsonResponse
  {
    try {
      $equipo = Equipo::findOrFail($id);

      // Verificar si el equipo puede ser eliminado
      if ($equipo->torneos()->count() > 0) {
        return response()->json([
          'success' => false,
          'message' => 'No se puede eliminar un equipo que participa en torneos'
        ], 400);
      }

      // Eliminar logo
      if ($equipo->logo) {
        Storage::delete($equipo->logo);
      }

      // Soft delete
      $equipo->delete();

      return response()->json([
        'success' => true,
        'message' => 'Equipo eliminado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al eliminar equipo',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Agregar jugador al equipo
   * 
   * @param Request $request
   * @param int $equipoId
   * @return JsonResponse
   */
  public function agregarJugador(Request $request, $equipoId): JsonResponse
  {
    try {
      $equipo = Equipo::findOrFail($equipoId);

      $validator = Validator::make($request->all(), [
        'jugador_id' => 'required|exists:jugadores,id',
        'numero_camiseta' => 'required|integer|min:1|max:99',
        'posicion' => 'required|string|max:50',
        'es_capitan' => 'boolean',
        'es_titular' => 'boolean',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Verificar si el jugador ya está en el equipo
      if ($equipo->jugadores()->where('jugador_id', $request->jugador_id)->exists()) {
        return response()->json([
          'success' => false,
          'message' => 'El jugador ya pertenece a este equipo'
        ], 400);
      }

      // Verificar número de camiseta único
      if ($equipo->jugadores()->wherePivot('numero_camiseta', $request->numero_camiseta)->exists()) {
        return response()->json([
          'success' => false,
          'message' => 'El número de camiseta ya está en uso'
        ], 400);
      }

      // Si es capitán, quitar capitanía a otros
      if ($request->boolean('es_capitan')) {
        $equipo->jugadores()->updateExistingPivot(
          $equipo->jugadores()->wherePivot('es_capitan', true)->pluck('jugadores.id'),
          ['es_capitan' => false]
        );
      }

      // Agregar jugador al equipo
      $equipo->jugadores()->attach($request->jugador_id, [
        'numero_camiseta' => $request->numero_camiseta,
        'posicion' => $request->posicion,
        'estado' => 'activo',
        'es_capitan' => $request->boolean('es_capitan'),
        'es_titular' => $request->boolean('es_titular'),
        'fecha_incorporacion' => now(),
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Jugador agregado al equipo exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al agregar jugador',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Remover jugador del equipo
   * 
   * @param int $equipoId
   * @param int $jugadorId
   * @return JsonResponse
   */
  public function removerJugador($equipoId, $jugadorId): JsonResponse
  {
    try {
      $equipo = Equipo::findOrFail($equipoId);

      if (!$equipo->jugadores()->where('jugador_id', $jugadorId)->exists()) {
        return response()->json([
          'success' => false,
          'message' => 'El jugador no pertenece a este equipo'
        ], 400);
      }

      // Remover jugador del equipo
      $equipo->jugadores()->detach($jugadorId);

      return response()->json([
        'success' => true,
        'message' => 'Jugador removido del equipo exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al remover jugador',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Actualizar información del jugador en el equipo
   * 
   * @param Request $request
   * @param int $equipoId
   * @param int $jugadorId
   * @return JsonResponse
   */
  public function actualizarJugador(Request $request, $equipoId, $jugadorId): JsonResponse
  {
    try {
      $equipo = Equipo::findOrFail($equipoId);

      $validator = Validator::make($request->all(), [
        'numero_camiseta' => 'sometimes|integer|min:1|max:99',
        'posicion' => 'sometimes|string|max:50',
        'estado' => 'sometimes|in:activo,inactivo,lesionado,suspendido',
        'es_capitan' => 'sometimes|boolean',
        'es_titular' => 'sometimes|boolean',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Verificar si el jugador pertenece al equipo
      if (!$equipo->jugadores()->where('jugador_id', $jugadorId)->exists()) {
        return response()->json([
          'success' => false,
          'message' => 'El jugador no pertenece a este equipo'
        ], 400);
      }

      // Verificar número de camiseta único (excluyendo el jugador actual)
      if ($request->has('numero_camiseta')) {
        $existe = $equipo->jugadores()
          ->wherePivot('numero_camiseta', $request->numero_camiseta)
          ->where('jugador_id', '!=', $jugadorId)
          ->exists();

        if ($existe) {
          return response()->json([
            'success' => false,
            'message' => 'El número de camiseta ya está en uso'
          ], 400);
        }
      }

      // Si es capitán, quitar capitanía a otros
      if ($request->boolean('es_capitan')) {
        $equipo->jugadores()->updateExistingPivot(
          $equipo->jugadores()->wherePivot('es_capitan', true)->where('jugador_id', '!=', $jugadorId)->pluck('jugadores.id'),
          ['es_capitan' => false]
        );
      }

      // Actualizar información del jugador
      $equipo->jugadores()->updateExistingPivot($jugadorId, $request->all());

      return response()->json([
        'success' => true,
        'message' => 'Información del jugador actualizada exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al actualizar jugador',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Obtener jugadores del equipo
   * 
   * @param int $equipoId
   * @return JsonResponse
   */
  public function jugadores($equipoId): JsonResponse
  {
    try {
      $equipo = Equipo::with(['jugadores.usuario'])->findOrFail($equipoId);

      // Agregar información de la relación pivot
      $jugadores = $equipo->jugadores->map(function ($jugador) {
        return [
          'id' => $jugador->id,
          'usuario' => $jugador->usuario,
          'numero_camiseta' => $jugador->pivot->numero_camiseta,
          'posicion' => $jugador->pivot->posicion,
          'estado' => $jugador->pivot->estado,
          'es_capitan' => $jugador->pivot->es_capitan,
          'es_titular' => $jugador->pivot->es_titular,
          'fecha_incorporacion' => $jugador->pivot->fecha_incorporacion,
          'partidos_jugados' => $jugador->pivot->partidos_jugados,
          'goles_marcados' => $jugador->pivot->goles_marcados,
          'asistencias' => $jugador->pivot->asistencias,
          'tarjetas_amarillas' => $jugador->pivot->tarjetas_amarillas,
          'tarjetas_rojas' => $jugador->pivot->tarjetas_rojas,
        ];
      });

      return response()->json([
        'success' => true,
        'data' => $jugadores,
        'message' => 'Jugadores del equipo obtenidos exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al obtener jugadores',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Obtener estadísticas básicas del equipo
   * 
   * @param int $equipoId
   * @return array
   */
  private function obtenerEstadisticasEquipo($equipoId): array
  {
    $equipo = Equipo::find($equipoId);

    return [
      'jugadores_total' => $equipo->jugadores()->count(),
      'jugadores_activos' => $equipo->jugadores()->wherePivot('estado', 'activo')->count(),
      'torneos_participando' => $equipo->torneos()->count(),
      'partidos_totales' => $equipo->partidosLocal()->count() + $equipo->partidosVisitante()->count(),
    ];
  }

  /**
   * Obtener estadísticas detalladas del equipo
   * 
   * @param int $equipoId
   * @return array
   */
  private function obtenerEstadisticasDetalladasEquipo($equipoId): array
  {
    $equipo = Equipo::find($equipoId);

    // Estadísticas de partidos
    $partidosLocal = $equipo->partidosLocal()->where('estado', 'finalizado')->get();
    $partidosVisitante = $equipo->partidosVisitante()->where('estado', 'finalizado')->get();
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

    return [
      'jugadores' => [
        'total' => $equipo->jugadores()->count(),
        'activos' => $equipo->jugadores()->wherePivot('estado', 'activo')->count(),
        'lesionados' => $equipo->jugadores()->wherePivot('estado', 'lesionado')->count(),
        'suspendidos' => $equipo->jugadores()->wherePivot('estado', 'suspendido')->count(),
      ],
      'partidos' => [
        'jugados' => $todosPartidos->count(),
        'ganados' => $ganados,
        'empatados' => $empatados,
        'perdidos' => $perdidos,
        'goles_favor' => $golesFavor,
        'goles_contra' => $golesContra,
        'diferencia_goles' => $golesFavor - $golesContra,
      ],
      'torneos' => [
        'participando' => $equipo->torneos()->count(),
        'activos' => $equipo->torneos()->where('estado', 'activo')->count(),
      ]
    ];
  }

  /**
   * Subir logo del equipo
   * 
   * @param $file
   * @return string
   */
  private function subirLogo($file): string
  {
    $nombreArchivo = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
    return $file->storeAs('logos/equipos', $nombreArchivo, 'public');
  }
}
