<?php

/**
 * RUTAS WEB - LARAVEL PARA SPA
 * 
 * Archivo: routes/web.php
 * 
 * Configuración de rutas para una Single Page Application (SPA) con Vue.js
 * Laravel maneja la API y Vue maneja el frontend
 */

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas Web para SPA
|--------------------------------------------------------------------------
|
| Estas rutas manejan las peticiones web de la aplicación.
| Para una SPA, Laravel solo necesita servir la vista principal de Vue
| y manejar algunas rutas específicas como autenticación.
|
*/

// ============================================================================
// RUTA PRINCIPAL - CATCH ALL PARA SPA
// ============================================================================

/**
 * Ruta catch-all para la SPA
 * Todas las rutas no definidas aquí serán manejadas por Vue Router
 */
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*')->name('spa');

// ============================================================================
// RUTAS DE API PARA AUTENTICACIÓN (SI ES NECESARIO)
// ============================================================================

/**
 * Si necesitas algunas rutas web específicas para Laravel (como OAuth),
 * defínelas ANTES de la ruta catch-all
 */

// Ejemplo: Ruta para obtener CSRF token
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});

// Ejemplo: Ruta para verificar si Laravel está funcionando
Route::get('/api/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Laravel API funcionando correctamente',
        'timestamp' => now()->toISOString(),
        'version' => app()->version(),
    ]);
});

/*
|--------------------------------------------------------------------------
| Rutas de desarrollo (solo en entorno local)
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
    // Ruta para mostrar información de la aplicación
    Route::get('/dev/info', function () {
        return response()->json([
            'environment' => app()->environment(),
            'debug' => config('app.debug'),
            'database' => [
                'connection' => config('database.default'),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'database' => config('database.connections.' . config('database.default') . '.database'),
            ],
            'cache' => config('cache.default'),
            'session' => config('session.driver'),
            'queue' => config('queue.default'),
        ]);
    });
    
    // Ruta para limpiar cache (útil en desarrollo)
    Route::get('/dev/clear-cache', function () {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        
        return response()->json([
            'message' => 'Cache limpiado exitosamente',
            'cleared' => ['cache', 'config', 'routes', 'views']
        ]);
    });
}