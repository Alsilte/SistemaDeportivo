<?php

/**
 * MIDDLEWARE: VERIFICACIÓN DE ROLES
 * 
 * Comando para crear: php artisan make:middleware RoleMiddleware
 * Archivo: app/Http/Middleware/RoleMiddleware.php
 * 
 * Middleware para verificar que el usuario tenga el rol adecuado
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next, string $role): Response
  {
    // Verificar que el usuario esté autenticado
    if (!$request->user()) {
      return response()->json([
        'success' => false,
        'message' => 'No autenticado'
      ], 401);
    }

    $user = $request->user();

    // Verificar que el usuario tenga el rol requerido
    if ($user->tipo_usuario !== $role) {
      return response()->json([
        'success' => false,
        'message' => 'Acceso denegado. Se requiere rol: ' . $role
      ], 403);
    }

    // Si el usuario tiene el rol correcto, continuar
    return $next($request);
  }
}
