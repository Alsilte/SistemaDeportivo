<?php

/**
 * CONTROLLER: TORNEOS
 * 
 * Comando para crear: php artisan make:controller TorneoController --api --resource
 * Archivo: app/Http/Controllers/TorneoController.php
 * 
 * Gestiona todas las operaciones relacionadas con torneos
 */

namespace App\Http\Controllers;

use App\Models\Torneo;
use App\Models\Deporte;
use App\Models\Equipo;
use App\Models\Clasificacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TorneoController extends Controller
{
  /**
   * Listar todos los torneos con filtros opcionales
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    try {
      $query = Torneo::query();

      // Filtro por deporte
      if ($request->has('deporte_id')) {
        $query->where('deporte_id', $request->deporte_id);
      }

      // Filtro por estado
      if ($request->has('estado')) {
        $query->where('estado', $request->estado);
      }

      // Filtro por fecha
      if ($request->has('fecha_desde')) {
        $query->where('fecha_inicio', '>=', $request->fecha_desde);
      }

      if ($request->has('fecha_hasta')) {
        $query->where('fecha_fin', '<=', $request->fecha_hasta);
      }

      // Búsqueda por nombre
      if ($request->has('buscar')) {
        $query->where('nombre', 'like', '%' . $request->buscar . '%');
      }

      // Ordenamiento
      $ordenarPor = $request->get('ordenar_por', 'fecha_inicio');
      $direccion = $request->get('direccion', 'desc');
      $query->orderBy($ordenarPor, $direccion);

      // Paginación
      $perPage = $request->get('per_page', 15);

      $torneos = $query->get();

      return response()->json([
        'data' => $torneos
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al obtener torneos',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Crear un nuevo torneo
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function store(Request $request): JsonResponse
  {
    try {
      // Validaciones
      $validator = Validator::make($request->all(), [
        'nombre' => 'required|string|max:150|unique:torneos,nombre',
        'descripcion' => 'nullable|string',
        'formato' => 'required|in:liga,eliminacion,grupos',
        'fecha_inicio' => 'required|date|after:today',
        'fecha_fin' => 'required|date|after:fecha_inicio',
        'fecha_inscripcion_limite' => 'nullable|date|before:fecha_inicio',
        'deporte_id' => 'required|exists:deportes,id',
        'configuracion' => 'nullable|array',
        'premios' => 'nullable|array',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Crear torneo
      $torneo = Torneo::create([
        'nombre' => $request->nombre,
        'descripcion' => $request->descripcion,
        'formato' => $request->formato,
        'estado' => 'planificacion',
        'fecha_inicio' => $request->fecha_inicio,
        'fecha_fin' => $request->fecha_fin,
        'fecha_inscripcion_limite' => $request->fecha_inscripcion_limite,
        'deporte_id' => $request->deporte_id,
        'configuracion' => $request->configuracion ?? [],
        'premios' => $request->premios ?? [],
      ]);

      // Cargar relaciones
      $torneo->load(['deporte', 'equipos']);

      return response()->json([
        'success' => true,
        'data' => $torneo,
        'message' => 'Torneo creado exitosamente'
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al crear torneo',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Mostrar un torneo específico
   * 
   * @param int $id
   * @return JsonResponse
   */
  public function show($id): JsonResponse
  {
    try {
      $torneo = Torneo::with([
        'deporte',
        'equipos.jugadores.usuario',
        'partidos.equipoLocal',
        'partidos.equipoVisitante',
        'partidos.arbitro.usuario',
        'clasificacion.equipo'
      ])->findOrFail($id);

      return response()->json([
        'success' => true,
        'data' => $torneo,
        'message' => 'Torneo obtenido exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Torneo no encontrado',
        'error' => $e->getMessage()
      ], 404);
    }
  }

  /**
   * Actualizar un torneo
   * 
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function update(Request $request, $id): JsonResponse
  {
    try {
      $torneo = Torneo::findOrFail($id);

      // Validaciones
      $validator = Validator::make($request->all(), [
        'nombre' => 'sometimes|string|max:150|unique:torneos,nombre,' . $id,
        'descripcion' => 'nullable|string',
        'formato' => 'sometimes|in:liga,eliminacion,grupos',
        'estado' => 'sometimes|in:planificacion,activo,finalizado,cancelado',
        'fecha_inicio' => 'sometimes|date',
        'fecha_fin' => 'sometimes|date|after:fecha_inicio',
        'fecha_inscripcion_limite' => 'nullable|date',
        'deporte_id' => 'sometimes|exists:deportes,id',
        'configuracion' => 'nullable|array',
        'premios' => 'nullable|array',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Actualizar torneo
      $torneo->update($request->all());
      $torneo->load(['deporte', 'equipos']);

      return response()->json([
        'success' => true,
        'data' => $torneo,
        'message' => 'Torneo actualizado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al actualizar torneo',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Eliminar un torneo
   * 
   * @param int $id
   * @return JsonResponse
   */
  public function destroy($id): JsonResponse
  {
    try {
      $torneo = Torneo::findOrFail($id);

      // Verificar si el torneo puede ser eliminado
      if ($torneo->estado === 'activo') {
        return response()->json([
          'success' => false,
          'message' => 'No se puede eliminar un torneo activo'
        ], 400);
      }

      // Soft delete
      $torneo->delete();

      return response()->json([
        'success' => true,
        'message' => 'Torneo eliminado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al eliminar torneo',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Inscribir equipo en torneo
   * 
   * @param Request $request
   * @param int $torneoId
   * @return JsonResponse
   */
  public function inscribirEquipo(Request $request, $torneoId): JsonResponse
  {
    try {
      $torneo = Torneo::findOrFail($torneoId);

      $validator = Validator::make($request->all(), [
        'equipo_id' => 'required|exists:equipos,id',
        'telefono_contacto' => 'nullable|string|max:20',
        'email_contacto' => 'nullable|email|max:100',
        'observaciones' => 'nullable|string',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Verificar si el equipo ya está inscrito
      if ($torneo->equipos()->where('equipo_id', $request->equipo_id)->exists()) {
        return response()->json([
          'success' => false,
          'message' => 'El equipo ya está inscrito en este torneo'
        ], 400);
      }

      // Verificar límite de inscripciones
      if ($torneo->fecha_inscripcion_limite && Carbon::now() > $torneo->fecha_inscripcion_limite) {
        return response()->json([
          'success' => false,
          'message' => 'El período de inscripciones ha terminado'
        ], 400);
      }

      // Inscribir equipo
      $torneo->equipos()->attach($request->equipo_id, [
        'fecha_inscripcion' => now(),
        'estado_participacion' => 'inscrito',
        'telefono_contacto' => $request->telefono_contacto,
        'email_contacto' => $request->email_contacto,
        'observaciones' => $request->observaciones,
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Equipo inscrito exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al inscribir equipo',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Obtener clasificación del torneo
   * 
   * @param int $torneoId
   * @return JsonResponse
   */
  public function clasificacion($torneoId): JsonResponse
  {
    try {
      $clasificacion = Clasificacion::where('torneo_id', $torneoId)
        ->with('equipo')
        ->orderBy('puntos', 'desc')
        ->orderBy('diferencia_goles', 'desc')
        ->orderBy('goles_favor', 'desc')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $clasificacion,
        'message' => 'Clasificación obtenida exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al obtener clasificación',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Generar fixture/calendario del torneo
   * 
   * @param int $torneoId
   * @return JsonResponse
   */
  public function generarFixture($torneoId): JsonResponse
  {
    try {
      $torneo = Torneo::with('equipos')->findOrFail($torneoId);

      if ($torneo->equipos->count() < 2) {
        return response()->json([
          'success' => false,
          'message' => 'Se necesitan al menos 2 equipos para generar el fixture'
        ], 400);
      }

      // Lógica para generar partidos según el formato
      $partidosCreados = $this->generarPartidos($torneo);

      return response()->json([
        'success' => true,
        'data' => $partidosCreados,
        'message' => 'Fixture generado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al generar fixture',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Método privado para generar partidos
   * 
   * @param Torneo $torneo
   * @return array
   */
  private function generarPartidos(Torneo $torneo): array
  {
    $equipos = $torneo->equipos->toArray();
    $partidos = [];

    switch ($torneo->formato) {
      case 'liga':
        // Todos contra todos
        for ($i = 0; $i < count($equipos); $i++) {
          for ($j = $i + 1; $j < count($equipos); $j++) {
            $partido = [
              'torneo_id' => $torneo->id,
              'equipo_local_id' => $equipos[$i]['id'],
              'equipo_visitante_id' => $equipos[$j]['id'],
              'fecha' => $this->calcularFechaPartido($torneo, count($partidos)),
              'estado' => 'programado',
            ];

            \App\Models\Partido::create($partido);
            $partidos[] = $partido;
          }
        }
        break;

      case 'eliminacion':
        // Eliminación directa
        $equiposShuffled = collect($equipos)->shuffle();
        for ($i = 0; $i < count($equiposShuffled); $i += 2) {
          if (isset($equiposShuffled[$i + 1])) {
            $partido = [
              'torneo_id' => $torneo->id,
              'equipo_local_id' => $equiposShuffled[$i]['id'],
              'equipo_visitante_id' => $equiposShuffled[$i + 1]['id'],
              'fecha' => $this->calcularFechaPartido($torneo, intval($i / 2)),
              'estado' => 'programado',
            ];

            \App\Models\Partido::create($partido);
            $partidos[] = $partido;
          }
        }
        break;
    }

    return $partidos;
  }

  /**
   * Calcular fecha del partido
   * 
   * @param Torneo $torneo
   * @param int $numeroPartido
   * @return string
   */
  private function calcularFechaPartido(Torneo $torneo, int $numeroPartido): string
  {
    $fechaInicio = Carbon::parse($torneo->fecha_inicio);
    $fechaFin = Carbon::parse($torneo->fecha_fin);

    $diasTotales = $fechaInicio->diffInDays($fechaFin);
    $diasPorPartido = max(1, intval($diasTotales / 10)); // Aproximadamente 10 jornadas

    return $fechaInicio->addDays($numeroPartido * $diasPorPartido)->format('Y-m-d H:i:s');
  }
}
