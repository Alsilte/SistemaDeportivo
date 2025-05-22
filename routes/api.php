<?php

/**
 * RUTAS API - SISTEMA DE GESTIÓN DEPORTIVA
 * 
 * Archivo: routes/api.php
 * 
 * Define todas las rutas API para el sistema de gestión deportiva
 * Incluye autenticación con Laravel Sanctum
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TorneoController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\PartidoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí es donde puedes registrar las rutas API para tu aplicación.
| Estas rutas son cargadas por el RouteServiceProvider dentro de un grupo
| que tiene el middleware "api" aplicado.
|
*/

// ============================================================================
// RUTAS PÚBLICAS (Sin autenticación)
// ============================================================================

Route::prefix('auth')->group(function () {
  // Autenticación básica
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/login', [AuthController::class, 'login']);

  // Reset de contraseña
  Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
  Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Rutas públicas de consulta (solo lectura)
Route::prefix('public')->group(function () {
  // Torneos públicos
  Route::get('/torneos', [TorneoController::class, 'index']);
  Route::get('/torneos/{id}', [TorneoController::class, 'show']);
  Route::get('/torneos/{id}/clasificacion', [TorneoController::class, 'clasificacion']);

  // Equipos públicos
  Route::get('/equipos', [EquipoController::class, 'index']);
  Route::get('/equipos/{id}', [EquipoController::class, 'show']);
  Route::get('/equipos/{id}/jugadores', [EquipoController::class, 'jugadores']);

  // Partidos públicos
  Route::get('/partidos', [PartidoController::class, 'index']);
  Route::get('/partidos/{id}', [PartidoController::class, 'show']);
  Route::get('/partidos/{id}/eventos', [PartidoController::class, 'eventos']);
});

// ============================================================================
// RUTAS PROTEGIDAS (Con autenticación Sanctum)
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {

  // ========================================================================
  // AUTENTICACIÓN - Rutas autenticadas
  // ========================================================================
  Route::prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/verify-token', [AuthController::class, 'verifyToken']);
    Route::get('/tokens', [AuthController::class, 'activeTokens']);
    Route::delete('/tokens/{tokenId}', [AuthController::class, 'revokeToken']);
  });

  // ========================================================================
  // USUARIOS - Gestión completa
  // ========================================================================
  Route::apiResource('users', UserController::class);
  Route::prefix('users')->group(function () {
    // Perfil del usuario autenticado
    Route::get('/profile/me', [UserController::class, 'profile']);
    Route::put('/profile/me', [UserController::class, 'updateProfile']);
    Route::post('/profile/change-password', [UserController::class, 'changePassword']);
  });

  // ========================================================================
  // TORNEOS - Gestión completa
  // ========================================================================
  Route::apiResource('torneos', TorneoController::class);
  Route::prefix('torneos')->group(function () {
    // Gestión de equipos en torneos
    Route::post('/{torneo}/inscribir-equipo', [TorneoController::class, 'inscribirEquipo']);
    Route::get('/{torneo}/clasificacion', [TorneoController::class, 'clasificacion']);

    // Generación de fixture
    Route::post('/{torneo}/generar-fixture', [TorneoController::class, 'generarFixture']);

    // Gestión de estados
    Route::patch('/{torneo}/iniciar', function ($torneoId) {
      $torneo = \App\Models\Torneo::findOrFail($torneoId);
      $torneo->update(['estado' => 'activo']);
      return response()->json(['success' => true, 'message' => 'Torneo iniciado']);
    });

    Route::patch('/{torneo}/finalizar', function ($torneoId) {
      $torneo = \App\Models\Torneo::findOrFail($torneoId);
      $torneo->update(['estado' => 'finalizado']);
      return response()->json(['success' => true, 'message' => 'Torneo finalizado']);
    });

    Route::patch('/{torneo}/cancelar', function ($torneoId) {
      $torneo = \App\Models\Torneo::findOrFail($torneoId);
      $torneo->update(['estado' => 'cancelado']);
      return response()->json(['success' => true, 'message' => 'Torneo cancelado']);
    });
  });

  // ========================================================================
  // EQUIPOS - Gestión completa
  // ========================================================================
  Route::apiResource('equipos', EquipoController::class);
  Route::prefix('equipos')->group(function () {
    // Gestión de jugadores
    Route::get('/{equipo}/jugadores', [EquipoController::class, 'jugadores']);
    Route::post('/{equipo}/jugadores', [EquipoController::class, 'agregarJugador']);
    Route::put('/{equipo}/jugadores/{jugador}', [EquipoController::class, 'actualizarJugador']);
    Route::delete('/{equipo}/jugadores/{jugador}', [EquipoController::class, 'removerJugador']);

    // Estadísticas del equipo
    Route::get('/{equipo}/estadisticas', function ($equipoId) {
      $equipo = \App\Models\Equipo::with([
        'jugadores',
        'torneos',
        'partidosLocal',
        'partidosVisitante',
        'clasificaciones'
      ])->findOrFail($equipoId);

      // Calcular estadísticas aquí o usar un método del controller
      return response()->json([
        'success' => true,
        'data' => $equipo,
        'message' => 'Estadísticas obtenidas'
      ]);
    });
  });

  // ========================================================================
  // PARTIDOS - Gestión completa
  // ========================================================================
  Route::apiResource('partidos', PartidoController::class);
  Route::prefix('partidos')->group(function () {
    // Control del partido
    Route::patch('/{partido}/iniciar', [PartidoController::class, 'iniciarPartido']);
    Route::patch('/{partido}/finalizar', [PartidoController::class, 'finalizarPartido']);

    // Gestión de eventos
    Route::get('/{partido}/eventos', [PartidoController::class, 'eventos']);
    Route::post('/{partido}/eventos', [PartidoController::class, 'agregarEvento']);

    // Estados del partido
    Route::patch('/{partido}/suspender', function ($partidoId) {
      $partido = \App\Models\Partido::findOrFail($partidoId);
      $partido->update(['estado' => 'suspendido']);
      return response()->json(['success' => true, 'message' => 'Partido suspendido']);
    });

    Route::patch('/{partido}/cancelar', function ($partidoId) {
      $partido = \App\Models\Partido::findOrFail($partidoId);
      $partido->update(['estado' => 'cancelado']);
      return response()->json(['success' => true, 'message' => 'Partido cancelado']);
    });
  });

  // ========================================================================
  // RUTAS ESPECÍFICAS POR ROLES
  // ========================================================================

  // Solo para ADMINISTRADORES
  Route::middleware(['role:administrador'])->group(function () {
    // Gestión avanzada de usuarios
    Route::prefix('admin')->group(function () {
      Route::get('/users/stats', function () {
        $stats = [
          'total_usuarios' => \App\Models\Usuario::count(),
          'jugadores' => \App\Models\Jugador::count(),
          'arbitros' => \App\Models\Arbitro::count(),
          'administradores' => \App\Models\Administrador::count(),
          'usuarios_activos' => \App\Models\Usuario::where('activo', true)->count(),
        ];
        return response()->json(['success' => true, 'data' => $stats]);
      });

      Route::get('/torneos/stats', function () {
        $stats = [
          'total_torneos' => \App\Models\Torneo::count(),
          'activos' => \App\Models\Torneo::where('estado', 'activo')->count(),
          'finalizados' => \App\Models\Torneo::where('estado', 'finalizado')->count(),
          'planificacion' => \App\Models\Torneo::where('estado', 'planificacion')->count(),
        ];
        return response()->json(['success' => true, 'data' => $stats]);
      });

      Route::get('/equipos/stats', function () {
        $stats = [
          'total_equipos' => \App\Models\Equipo::count(),
          'activos' => \App\Models\Equipo::where('activo', true)->count(),
          'por_deporte' => \App\Models\Equipo::join('deportes', 'equipos.deporte_id', '=', 'deportes.id')
            ->selectRaw('deportes.nombre, COUNT(*) as total')
            ->groupBy('deportes.nombre')
            ->get(),
        ];
        return response()->json(['success' => true, 'data' => $stats]);
      });
    });
  });

  // Solo para ÁRBITROS
  Route::middleware(['role:arbitro'])->group(function () {
    Route::prefix('arbitro')->group(function () {
      Route::get('/mis-partidos', function (Request $request) {
        $usuario = $request->user();
        $arbitro = $usuario->arbitro;

        if (!$arbitro) {
          return response()->json(['success' => false, 'message' => 'Usuario no es árbitro'], 403);
        }

        $partidos = \App\Models\Partido::where('arbitro_id', $arbitro->id)
          ->with(['torneo', 'equipoLocal', 'equipoVisitante'])
          ->orderBy('fecha', 'desc')
          ->paginate(15);

        return response()->json(['success' => true, 'data' => $partidos]);
      });

      Route::get('/disponibilidad', function (Request $request) {
        $usuario = $request->user();
        $arbitro = $usuario->arbitro;

        $fecha = $request->get('fecha', now()->format('Y-m-d'));

        $partidosAsignados = \App\Models\Partido::where('arbitro_id', $arbitro->id)
          ->whereDate('fecha', $fecha)
          ->where('estado', '!=', 'cancelado')
          ->get();

        return response()->json([
          'success' => true,
          'data' => [
            'fecha' => $fecha,
            'disponible' => $partidosAsignados->isEmpty(),
            'partidos_asignados' => $partidosAsignados
          ]
        ]);
      });
    });
  });

  // Solo para JUGADORES
  Route::middleware(['role:jugador'])->group(function () {
    Route::prefix('jugador')->group(function () {
      Route::get('/mis-equipos', function (Request $request) {
        $usuario = $request->user();
        $jugador = $usuario->jugador;

        if (!$jugador) {
          return response()->json(['success' => false, 'message' => 'Usuario no es jugador'], 403);
        }

        $equipos = $jugador->equipos()
          ->with(['deporte', 'torneos'])
          ->get();

        return response()->json(['success' => true, 'data' => $equipos]);
      });

      Route::get('/mis-partidos', function (Request $request) {
        $usuario = $request->user();
        $jugador = $usuario->jugador;

        $equipos = $jugador->equipos->pluck('id');

        $partidos = \App\Models\Partido::where(function ($query) use ($equipos) {
          $query->whereIn('equipo_local_id', $equipos)
            ->orWhereIn('equipo_visitante_id', $equipos);
        })
          ->with(['torneo', 'equipoLocal', 'equipoVisitante'])
          ->orderBy('fecha', 'desc')
          ->paginate(15);

        return response()->json(['success' => true, 'data' => $partidos]);
      });

      Route::get('/estadisticas', function (Request $request) {
        $usuario = $request->user();
        $jugador = $usuario->jugador;

        return response()->json([
          'success' => true,
          'data' => [
            'puntos' => $jugador->puntos,
            'partidos_jugados' => $jugador->partidos_jugados,
            'goles_favor' => $jugador->goles_favor,
            'goles_contra' => $jugador->goles_contra,
            'ganados' => $jugador->ganados,
            'empatados' => $jugador->empatados,
            'perdidos' => $jugador->perdidos,
          ]
        ]);
      });
    });
  });

  // ========================================================================
  // RUTAS DE UTILIDAD
  // ========================================================================

  // Dashboard general
  Route::get('/dashboard', function (Request $request) {
    $usuario = $request->user();

    $dashboard = [
      'usuario' => $usuario->load(['jugador', 'arbitro', 'administrador']),
      'estadisticas_generales' => [
        'torneos_activos' => \App\Models\Torneo::where('estado', 'activo')->count(),
        'partidos_hoy' => \App\Models\Partido::whereDate('fecha', today())->count(),
        'equipos_totales' => \App\Models\Equipo::count(),
        'usuarios_activos' => \App\Models\Usuario::where('activo', true)->count(),
      ],
    ];

    // Agregar datos específicos según el tipo de usuario
    switch ($usuario->tipo_usuario) {
      case 'jugador':
        $jugador = $usuario->jugador;
        $dashboard['mis_equipos'] = $jugador->equipos()->count();
        $dashboard['proximos_partidos'] = \App\Models\Partido::whereIn('equipo_local_id', $jugador->equipos->pluck('id'))
          ->orWhereIn('equipo_visitante_id', $jugador->equipos->pluck('id'))
          ->where('fecha', '>', now())
          ->count();
        break;

      case 'arbitro':
        $arbitro = $usuario->arbitro;
        $dashboard['partidos_arbitrados'] = $arbitro->partidos_arbitrados;
        $dashboard['proximos_partidos'] = \App\Models\Partido::where('arbitro_id', $arbitro->id)
          ->where('fecha', '>', now())
          ->count();
        break;

      case 'administrador':
        $dashboard['equipos_administrados'] = $usuario->equiposAdministrados()->count();
        $dashboard['torneos_creados'] = \App\Models\Torneo::count(); // Podrías agregar un campo created_by
        break;
    }

    return response()->json([
      'success' => true,
      'data' => $dashboard,
      'message' => 'Dashboard obtenido exitosamente'
    ]);
  });

  // Búsqueda global
  Route::get('/search', function (Request $request) {
    $query = $request->get('q', '');

    if (strlen($query) < 2) {
      return response()->json([
        'success' => false,
        'message' => 'Query debe tener al menos 2 caracteres'
      ], 400);
    }

    $resultados = [
      'usuarios' => \App\Models\Usuario::where('nombre', 'like', "%{$query}%")
        ->orWhere('email', 'like', "%{$query}%")
        ->limit(5)->get(['id', 'nombre', 'email', 'tipo_usuario']),
      'equipos' => \App\Models\Equipo::where('nombre', 'like', "%{$query}%")
        ->with('deporte')
        ->limit(5)->get(['id', 'nombre', 'deporte_id']),
      'torneos' => \App\Models\Torneo::where('nombre', 'like', "%{$query}%")
        ->with('deporte')
        ->limit(5)->get(['id', 'nombre', 'estado', 'deporte_id']),
    ];

    return response()->json([
      'success' => true,
      'data' => $resultados,
      'message' => 'Búsqueda realizada exitosamente'
    ]);
  });
});

// ============================================================================
// RUTA FALLBACK PARA API
// ============================================================================
Route::fallback(function () {
  return response()->json([
    'success' => false,
    'message' => 'Ruta API no encontrada'
  ], 404);
});
