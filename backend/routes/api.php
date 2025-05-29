<?php

/**
 * RUTAS API - LARAVEL
 * 
 * Archivo: routes/api.php
 * 
 * Todas las rutas de la API para el sistema de gestión deportiva
 * Estas rutas son consumidas por la SPA de Vue.js
 */

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TorneoController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\PartidoController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API
|--------------------------------------------------------------------------
|
| Aquí se registran todas las rutas de la API. Estas rutas son cargadas
| por el RouteServiceProvider y todas están asignadas al grupo middleware "api"
| que incluye throttling y bindings.
|
*/

// ============================================================================
// RUTAS PÚBLICAS (SIN AUTENTICACIÓN)
// ============================================================================

/**
 * Grupo de rutas de autenticación
 * Estas rutas no requieren autenticación previa
 */
Route::prefix('auth')->group(function () {
    // Registro de usuarios
    Route::post('register', [AuthController::class, 'register']);
    
    // Inicio de sesión
    Route::post('login', [AuthController::class, 'login']);
    
    // Recuperación de contraseña
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    
    // Verificación de token (sin autenticación previa)
    Route::post('verify-token', [AuthController::class, 'verifyToken']);
});

/**
 * Rutas públicas de información
 */
Route::prefix('public')->group(function () {
    // Información básica de la aplicación
    Route::get('app-info', function () {
        return response()->json([
            'name' => config('app.name'),
            'version' => '1.0.0',
            'description' => 'Sistema de Gestión Deportiva',
            'features' => [
                'Gestión de Torneos',
                'Administración de Equipos',
                'Control de Partidos',
                'Estadísticas en Tiempo Real'
            ]
        ]);
    });
    
    // Lista pública de deportes disponibles
    Route::get('deportes', function () {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Deporte::where('activo', true)
                ->select('id', 'nombre', 'descripcion', 'imagen')
                ->get()
        ]);
    });
});

// ============================================================================
// RUTAS PROTEGIDAS (REQUIEREN AUTENTICACIÓN)
// ============================================================================

/**
 * Middleware Sanctum para autenticación con tokens
 * Todas las rutas dentro de este grupo requieren un token válido
 */
Route::middleware('auth:sanctum')->group(function () {
    
    // ========================================================================
    // RUTAS DE AUTENTICACIÓN (USUARIO AUTENTICADO)
    // ========================================================================
    
    Route::prefix('auth')->group(function () {
        // Información del usuario actual
        Route::get('me', [AuthController::class, 'me']);
        
        // Cerrar sesión (token actual)
        Route::post('logout', [AuthController::class, 'logout']);
        
        // Cerrar sesión en todos los dispositivos
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        
        // Refrescar token
        Route::post('refresh', [AuthController::class, 'refresh']);
        
        // Cambiar contraseña
        Route::post('change-password', [AuthController::class, 'changePassword']);
        
        // Gestión de tokens
        Route::get('tokens', [AuthController::class, 'activeTokens']);
        Route::delete('tokens/{tokenId}', [AuthController::class, 'revokeToken']);
        
        // Actualizar perfil
        Route::put('profile', [UserController::class, 'updateProfile']);
    });
    
    // ========================================================================
    // DASHBOARD Y ESTADÍSTICAS
    // ========================================================================
    
    Route::prefix('dashboard')->group(function () {
        // Estadísticas principales del dashboard
        Route::get('stats', function (Request $request) {
            $user = $request->user();
            
            // Estadísticas base
            $stats = [
                'torneos_activos' => \App\Models\Torneo::where('estado', 'activo')->count(),
                'equipos_total' => \App\Models\Equipo::where('activo', true)->count(),
                'partidos_hoy' => \App\Models\Partido::whereDate('fecha', today())->count(),
                'usuarios_activos' => \App\Models\Usuario::where('activo', true)->count(),
            ];
            
            // Estadísticas específicas por rol
            switch ($user->tipo_usuario) {
                case 'jugador':
                    if ($user->jugador) {
                        $stats['mis_equipos'] = $user->jugador->equipos()->count();
                        $stats['partidos_jugados'] = $user->jugador->partidos_jugados;
                        $stats['goles_marcados'] = $user->jugador->goles_favor;
                    }
                    break;
                    
                case 'arbitro':
                    if ($user->arbitro) {
                        $stats['partidos_arbitrados'] = $user->arbitro->partidos_arbitrados;
                        $stats['partidos_pendientes'] = \App\Models\Partido::where('arbitro_id', $user->arbitro->id)
                            ->where('estado', 'programado')
                            ->count();
                    }
                    break;
                    
                case 'administrador':
                    $stats['equipos_administrados'] = $user->equiposAdministrados()->count();
                    $stats['torneos_creados'] = \App\Models\Torneo::count(); // Ajustar según necesidad
                    break;
            }
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        });
        
        // Actividad reciente
        Route::get('activity', function (Request $request) {
            // Aquí puedes implementar la lógica para obtener actividad reciente
            // basada en el tipo de usuario
            
            return response()->json([
                'success' => true,
                'data' => [
                    [
                        'id' => 1,
                        'type' => 'partido',
                        'title' => 'Partido finalizado',
                        'description' => 'Real Madrid 2-1 Barcelona',
                        'timestamp' => now()->subHours(2)->toISOString(),
                    ],
                    [
                        'id' => 2,
                        'type' => 'equipo',
                        'title' => 'Nuevo equipo registrado',
                        'description' => 'Atlético Madrid se unió al sistema',
                        'timestamp' => now()->subHours(5)->toISOString(),
                    ]
                ]
            ]);
        });
    });
    
    // ========================================================================
    // GESTIÓN DE USUARIOS
    // ========================================================================
    
    Route::apiResource('usuarios', UserController::class)
        ->middleware('role:administrador'); // Solo administradores
    
    Route::prefix('usuarios')->group(function () {
        // Perfil del usuario actual
        Route::get('me/profile', [UserController::class, 'profile']);
    });
    
    // ========================================================================
    // GESTIÓN DE TORNEOS
    // ========================================================================
    
    Route::apiResource('torneos', TorneoController::class);
    
    Route::prefix('torneos')->group(function () {
        // Inscribir equipo en torneo
        Route::post('{torneo}/equipos', [TorneoController::class, 'inscribirEquipo']);
        
        // Obtener clasificación del torneo
        Route::get('{torneo}/clasificacion', [TorneoController::class, 'clasificacion']);
        
        // Generar fixture/calendario
        Route::post('{torneo}/fixture', [TorneoController::class, 'generarFixture'])
            ->middleware('role:administrador');
    });
    
    // ========================================================================
    // GESTIÓN DE EQUIPOS
    // ========================================================================
    
    Route::apiResource('equipos', EquipoController::class);
    
    Route::prefix('equipos')->group(function () {
        // Gestión de jugadores en equipos
        Route::get('{equipo}/jugadores', [EquipoController::class, 'jugadores']);
        Route::post('{equipo}/jugadores', [EquipoController::class, 'agregarJugador']);
        Route::put('{equipo}/jugadores/{jugador}', [EquipoController::class, 'actualizarJugador']);
        Route::delete('{equipo}/jugadores/{jugador}', [EquipoController::class, 'removerJugador']);
    });
    
    // ========================================================================
    // GESTIÓN DE PARTIDOS
    // ========================================================================
    
    Route::apiResource('partidos', PartidoController::class);
    
    Route::prefix('partidos')->group(function () {
        // Control de partidos
        Route::post('{partido}/iniciar', [PartidoController::class, 'iniciarPartido']);
        Route::post('{partido}/finalizar', [PartidoController::class, 'finalizarPartido']);
        
        // Eventos del partido
        Route::get('{partido}/eventos', [PartidoController::class, 'eventos']);
        Route::post('{partido}/eventos', [PartidoController::class, 'agregarEvento']);
    });
    
    // ========================================================================
    // RUTAS ESPECÍFICAS POR ROL
    // ========================================================================
    
    /**
     * Rutas solo para administradores
     */
    Route::middleware('role:administrador')->group(function () {
        Route::prefix('admin')->group(function () {
            // Estadísticas administrativas
            Route::get('estadisticas', function () {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'usuarios_por_tipo' => \App\Models\Usuario::selectRaw('tipo_usuario, COUNT(*) as total')
                            ->groupBy('tipo_usuario')
                            ->pluck('total', 'tipo_usuario'),
                        'torneos_por_estado' => \App\Models\Torneo::selectRaw('estado, COUNT(*) as total')
                            ->groupBy('estado')
                            ->pluck('total', 'estado'),
                        'partidos_por_estado' => \App\Models\Partido::selectRaw('estado, COUNT(*) as total')
                            ->groupBy('estado')
                            ->pluck('total', 'estado'),
                    ]
                ]);
            });
            
            // Logs del sistema (simplificado)
            Route::get('logs', function () {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'message' => 'Funcionalidad de logs en desarrollo',
                        'note' => 'Implementar según necesidades específicas'
                    ]
                ]);
            });
        });
    });
    
    /**
     * Rutas específicas para jugadores
     */
    Route::middleware('role:jugador')->group(function () {
        Route::prefix('jugador')->group(function () {
            // Mis equipos
            Route::get('equipos', function (Request $request) {
                $jugador = $request->user()->jugador;
                return response()->json([
                    'success' => true,
                    'data' => $jugador ? $jugador->equipos()->with('deporte')->get() : []
                ]);
            });
            
            // Mis estadísticas
            Route::get('estadisticas', function (Request $request) {
                $jugador = $request->user()->jugador;
                return response()->json([
                    'success' => true,
                    'data' => $jugador ? $jugador->toArray() : null
                ]);
            });
        });
    });
    
    /**
     * Rutas específicas para árbitros
     */
    Route::middleware('role:arbitro')->group(function () {
        Route::prefix('arbitro')->group(function () {
            // Mis partidos asignados
            Route::get('partidos', function (Request $request) {
                $arbitro = $request->user()->arbitro;
                if (!$arbitro) {
                    return response()->json(['success' => false, 'message' => 'Usuario no es árbitro'], 400);
                }
                
                $partidos = \App\Models\Partido::where('arbitro_id', $arbitro->id)
                    ->with(['torneo', 'equipoLocal', 'equipoVisitante'])
                    ->orderBy('fecha', 'desc')
                    ->get();
                
                return response()->json([
                    'success' => true,
                    'data' => $partidos
                ]);
            });
        });
    });
});

// ============================================================================
// MIDDLEWARE PERSONALIZADO
// ============================================================================

/**
 * Middleware para verificar roles específicos
 * Definido en app/Http/Middleware/RoleMiddleware.php
 */

// ============================================================================
// RUTAS DE DESARROLLO Y TESTING
// ============================================================================

if (app()->environment(['local', 'testing'])) {
    Route::prefix('dev')->group(function () {
        // Ruta para generar datos de prueba
        Route::post('seed-data', function () {
            \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--seed' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Base de datos reiniciada con datos de prueba'
            ]);
        });
        
        // Ruta para obtener usuarios de prueba
        Route::get('test-users', function () {
            return response()->json([
                'success' => true,
                'data' => [
                    'admin' => [
                        'email' => 'admin@sistema.com',
                        'password' => 'password',
                        'role' => 'administrador'
                    ],
                    'jugador' => [
                        'email' => 'messi@sistema.com',
                        'password' => 'password',
                        'role' => 'jugador'
                    ],
                    'arbitro' => [
                        'email' => 'pedro.arbitro@sistema.com',
                        'password' => 'password',
                        'role' => 'arbitro'
                    ]
                ]
            ]);
        });
    });
}