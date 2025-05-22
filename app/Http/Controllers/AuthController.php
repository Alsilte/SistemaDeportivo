<?php

/**
 * CONTROLLER: AUTENTICACIÓN
 * 
 * Comando para crear: php artisan make:controller AuthController
 * Archivo: app/Http/Controllers/AuthController.php
 * 
 * Gestiona todas las operaciones de autenticación con Laravel Sanctum
 */

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Jugador;
use App\Models\Arbitro;
use App\Models\Administrador;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
  /**
   * Registro de nuevo usuario
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function register(Request $request): JsonResponse
  {
    try {
      // Validaciones
      $validator = Validator::make($request->all(), [
        'nombre' => 'required|string|max:100',
        'email' => 'required|email|unique:usuarios,email',
        'password' => 'required|string|min:8|confirmed',
        'telefono' => 'nullable|string|max:20',
        'fecha_nacimiento' => 'nullable|date|before:today',
        'tipo_usuario' => 'required|in:jugador,arbitro,administrador',

        // Campos específicos para jugadores
        'posicion' => 'required_if:tipo_usuario,jugador|string|max:50',

        // Campos específicos para árbitros
        'licencia' => 'required_if:tipo_usuario,arbitro|string|max:50|unique:arbitros,licencia',
        'posicion_arbitro' => 'required_if:tipo_usuario,arbitro|in:principal,asistente,cuarto_arbitro',

        // Campos específicos para administradores
        'nivel_acceso' => 'required_if:tipo_usuario,administrador|in:super_admin,admin,moderador',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Crear usuario
      $usuario = Usuario::create([
        'nombre' => $request->nombre,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'telefono' => $request->telefono,
        'fecha_nacimiento' => $request->fecha_nacimiento,
        'tipo_usuario' => $request->tipo_usuario,
        'activo' => true,
      ]);

      // Crear perfil específico según tipo de usuario
      $this->crearPerfilEspecifico($usuario, $request);

      // Crear token de acceso
      $token = $usuario->createToken('auth_token')->plainTextToken;

      // Cargar relaciones
      $usuario->load(['jugador', 'arbitro', 'administrador'])
        ->makeHidden(['password', 'remember_token']);

      return response()->json([
        'success' => true,
        'data' => [
          'user' => $usuario,
          'token' => $token,
          'token_type' => 'Bearer',
        ],
        'message' => 'Usuario registrado exitosamente'
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error en el registro',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Login de usuario
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function login(Request $request): JsonResponse
  {
    try {
      $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string',
        'remember' => 'boolean',
        'device_name' => 'string|max:255',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Buscar usuario
      $usuario = Usuario::where('email', $request->email)->first();

      if (!$usuario || !Hash::check($request->password, $usuario->password)) {
        return response()->json([
          'success' => false,
          'message' => 'Credenciales incorrectas'
        ], 401);
      }

      if (!$usuario->activo) {
        return response()->json([
          'success' => false,
          'message' => 'Usuario inactivo. Contacte al administrador.'
        ], 401);
      }

      // Revocar tokens anteriores si no es "recordar sesión"
      if (!$request->boolean('remember')) {
        $usuario->tokens()->delete();
      }

      // Crear token
      $deviceName = $request->get('device_name', 'web_browser');
      $tokenName = $deviceName . '_' . $usuario->id . '_' . time();
      $expiresAt = $request->boolean('remember') ? null : now()->addHours(24);

      $token = $usuario->createToken($tokenName, ['*'], $expiresAt);

      // Cargar relaciones
      $usuario->load(['jugador', 'arbitro', 'administrador'])
        ->makeHidden(['password', 'remember_token']);

      return response()->json([
        'success' => true,
        'data' => [
          'user' => $usuario,
          'token' => $token->plainTextToken,
          'token_type' => 'Bearer',
          'expires_at' => $expiresAt?->toISOString(),
        ],
        'message' => 'Login exitoso'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error en el login',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Logout de usuario
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function logout(Request $request): JsonResponse
  {
    try {
      // Revocar el token actual
      $request->user()->currentAccessToken()->delete();

      return response()->json([
        'success' => true,
        'message' => 'Logout exitoso'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error en el logout',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Logout de todos los dispositivos
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function logoutAll(Request $request): JsonResponse
  {
    try {
      // Revocar todos los tokens del usuario
      $request->user()->tokens()->delete();

      return response()->json([
        'success' => true,
        'message' => 'Logout de todos los dispositivos exitoso'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error en el logout',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Refrescar token
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function refresh(Request $request): JsonResponse
  {
    try {
      $usuario = $request->user();

      // Revocar token actual
      $request->user()->currentAccessToken()->delete();

      // Crear nuevo token
      $token = $usuario->createToken('auth_token_refresh')->plainTextToken;

      $usuario->load(['jugador', 'arbitro', 'administrador'])
        ->makeHidden(['password', 'remember_token']);

      return response()->json([
        'success' => true,
        'data' => [
          'user' => $usuario,
          'token' => $token,
          'token_type' => 'Bearer',
        ],
        'message' => 'Token refrescado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al refrescar token',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Obtener usuario autenticado
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function me(Request $request): JsonResponse
  {
    try {
      $usuario = $request->user();
      $usuario->load(['jugador.equipos', 'arbitro.partidos', 'administrador.equipos'])
        ->makeHidden(['password', 'remember_token']);

      return response()->json([
        'success' => true,
        'data' => $usuario,
        'message' => 'Usuario obtenido exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al obtener usuario',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Solicitar reset de contraseña
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function forgotPassword(Request $request): JsonResponse
  {
    try {
      $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:usuarios,email',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Enviar email de reset
      $status = Password::sendResetLink(
        $request->only('email')
      );

      if ($status === Password::RESET_LINK_SENT) {
        return response()->json([
          'success' => true,
          'message' => 'Enlace de restablecimiento enviado a su email'
        ]);
      }

      return response()->json([
        'success' => false,
        'message' => 'Error al enviar enlace de restablecimiento'
      ], 400);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al solicitar reset de contraseña',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Restablecer contraseña
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function resetPassword(Request $request): JsonResponse
  {
    try {
      $validator = Validator::make($request->all(), [
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|string|min:8|confirmed',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Restablecer contraseña
      $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
          $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
          ])->save();

          // Revocar todos los tokens existentes
          $user->tokens()->delete();

          event(new PasswordReset($user));
        }
      );

      if ($status === Password::PASSWORD_RESET) {
        return response()->json([
          'success' => true,
          'message' => 'Contraseña restablecida exitosamente'
        ]);
      }

      return response()->json([
        'success' => false,
        'message' => 'Error al restablecer contraseña. Token inválido o expirado.'
      ], 400);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al restablecer contraseña',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Cambiar contraseña (usuario autenticado)
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function changePassword(Request $request): JsonResponse
  {
    try {
      $validator = Validator::make($request->all(), [
        'current_password' => 'required|string',
        'new_password' => 'required|string|min:8|confirmed',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      $usuario = $request->user();

      // Verificar contraseña actual
      if (!Hash::check($request->current_password, $usuario->password)) {
        return response()->json([
          'success' => false,
          'message' => 'Contraseña actual incorrecta'
        ], 400);
      }

      // Actualizar contraseña
      $usuario->update([
        'password' => Hash::make($request->new_password)
      ]);

      // Revocar todos los tokens excepto el actual
      $currentTokenId = $request->user()->currentAccessToken()->id;
      $usuario->tokens()->where('id', '!=', $currentTokenId)->delete();

      return response()->json([
        'success' => true,
        'message' => 'Contraseña cambiada exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al cambiar contraseña',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Verificar si el token es válido
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function verifyToken(Request $request): JsonResponse
  {
    try {
      $usuario = $request->user();

      if (!$usuario) {
        return response()->json([
          'success' => false,
          'message' => 'Token inválido'
        ], 401);
      }

      return response()->json([
        'success' => true,
        'data' => [
          'valid' => true,
          'user_id' => $usuario->id,
          'expires_at' => $request->user()->currentAccessToken()->expires_at,
        ],
        'message' => 'Token válido'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al verificar token',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Obtener tokens activos del usuario
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function activeTokens(Request $request): JsonResponse
  {
    try {
      $usuario = $request->user();
      $tokens = $usuario->tokens()->get(['id', 'name', 'created_at', 'last_used_at', 'expires_at']);

      return response()->json([
        'success' => true,
        'data' => $tokens,
        'message' => 'Tokens activos obtenidos exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al obtener tokens',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Revocar token específico
   * 
   * @param Request $request
   * @param int $tokenId
   * @return JsonResponse
   */
  public function revokeToken(Request $request, $tokenId): JsonResponse
  {
    try {
      $usuario = $request->user();
      $token = $usuario->tokens()->where('id', $tokenId)->first();

      if (!$token) {
        return response()->json([
          'success' => false,
          'message' => 'Token no encontrado'
        ], 404);
      }

      $token->delete();

      return response()->json([
        'success' => true,
        'message' => 'Token revocado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al revocar token',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Crear perfil específico según tipo de usuario
   * 
   * @param Usuario $usuario
   * @param Request $request
   * @return void
   */
  private function crearPerfilEspecifico(Usuario $usuario, Request $request): void
  {
    switch ($usuario->tipo_usuario) {
      case 'jugador':
        Jugador::create([
          'usuario_id' => $usuario->id,
          'posicion' => $request->posicion,
          'numero_camiseta' => $request->numero_camiseta,
        ]);
        break;

      case 'arbitro':
        Arbitro::create([
          'usuario_id' => $usuario->id,
          'licencia' => $request->licencia,
          'posicion' => $request->posicion_arbitro,
          'fecha_nacimiento' => $request->fecha_nacimiento,
        ]);
        break;

      case 'administrador':
        Administrador::create([
          'usuario_id' => $usuario->id,
          'nivel_acceso' => $request->nivel_acceso ?? 'admin',
          'permisos' => $request->permisos ?? [],
        ]);
        break;
    }
  }
}
