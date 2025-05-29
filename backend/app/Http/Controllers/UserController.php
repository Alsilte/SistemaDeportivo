<?php

/**
 * CONTROLLER: USUARIOS
 * 
 * Comando para crear: php artisan make:controller UserController --api --resource
 * Archivo: app/Http/Controllers/UserController.php
 * 
 * Gestiona todas las operaciones relacionadas con usuarios y autenticación
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
  /**
   * Listar todos los usuarios con filtros
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    try {
      $query = Usuario::with(['jugador', 'arbitro', 'administrador']);

      // Filtro por tipo de usuario
      if ($request->has('tipo_usuario')) {
        $query->where('tipo_usuario', $request->tipo_usuario);
      }

      // Filtro por estado activo
      if ($request->has('activo')) {
        $query->where('activo', $request->boolean('activo'));
      }

      // Búsqueda por nombre o email
      if ($request->has('buscar')) {
        $buscar = $request->buscar;
        $query->where(function ($q) use ($buscar) {
          $q->where('nombre', 'like', '%' . $buscar . '%')
            ->orWhere('email', 'like', '%' . $buscar . '%');
        });
      }

      // Ordenamiento
      $ordenarPor = $request->get('ordenar_por', 'nombre');
      $direccion = $request->get('direccion', 'asc');
      $query->orderBy($ordenarPor, $direccion);

      // Paginación
      $perPage = $request->get('per_page', 15);
      $usuarios = $query->paginate($perPage);

      // Ocultar información sensible
      $usuarios->getCollection()->makeHidden(['password', 'remember_token']);

      return response()->json([
        'success' => true,
        'data' => $usuarios,
        'message' => 'Usuarios obtenidos exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al obtener usuarios',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Registrar un nuevo usuario
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
        'email' => 'required|email|unique:usuarios,email',
        'password' => 'required|string|min:8|confirmed',
        'telefono' => 'nullable|string|max:20',
        'fecha_nacimiento' => 'nullable|date|before:today',
        'tipo_usuario' => 'required|in:jugador,arbitro,administrador',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

        // Campos específicos para jugadores
        'posicion' => 'required_if:tipo_usuario,jugador|string|max:50',
        'numero_camiseta' => 'nullable|integer|min:1|max:99',

        // Campos específicos para árbitros
        'licencia' => 'required_if:tipo_usuario,arbitro|string|max:50|unique:arbitros,licencia',
        'posicion_arbitro' => 'required_if:tipo_usuario,arbitro|in:principal,asistente,cuarto_arbitro',

        // Campos específicos para administradores
        'nivel_acceso' => 'required_if:tipo_usuario,administrador|in:super_admin,admin,moderador',
        'permisos' => 'nullable|array',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Subir avatar si existe
      $avatarPath = null;
      if ($request->hasFile('avatar')) {
        $avatarPath = $this->subirAvatar($request->file('avatar'));
      }

      // Crear usuario
      $usuario = Usuario::create([
        'nombre' => $request->nombre,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'telefono' => $request->telefono,
        'fecha_nacimiento' => $request->fecha_nacimiento,
        'avatar' => $avatarPath,
        'tipo_usuario' => $request->tipo_usuario,
        'activo' => true,
      ]);

      // Crear perfil específico según tipo de usuario
      $this->crearPerfilEspecifico($usuario, $request);

      // Cargar relaciones
      $usuario->load(['jugador', 'arbitro', 'administrador'])
        ->makeHidden(['password', 'remember_token']);

      return response()->json([
        'success' => true,
        'data' => $usuario,
        'message' => 'Usuario registrado exitosamente'
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al registrar usuario',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Mostrar un usuario específico
   * 
   * @param int $id
   * @return JsonResponse
   */
  public function show($id): JsonResponse
  {
    try {
      $usuario = Usuario::with([
        'jugador.equipos.deporte',
        'arbitro.partidos',
        'administrador.equipos',
        'equiposAdministrados',
        'invitacionesEnviadas'
      ])->findOrFail($id);

      $usuario->makeHidden(['password', 'remember_token']);

      return response()->json([
        'success' => true,
        'data' => $usuario,
        'message' => 'Usuario obtenido exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Usuario no encontrado',
        'error' => $e->getMessage()
      ], 404);
    }
  }

  /**
   * Actualizar un usuario
   * 
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function update(Request $request, $id): JsonResponse
  {
    try {
      $usuario = Usuario::findOrFail($id);

      // Validaciones
      $validator = Validator::make($request->all(), [
        'nombre' => 'sometimes|string|max:100',
        'email' => 'sometimes|email|unique:usuarios,email,' . $id,
        'password' => 'sometimes|string|min:8|confirmed',
        'telefono' => 'nullable|string|max:20',
        'fecha_nacimiento' => 'nullable|date|before:today',
        'activo' => 'sometimes|boolean',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Subir nuevo avatar si existe
      if ($request->hasFile('avatar')) {
        // Eliminar avatar anterior
        if ($usuario->avatar) {
          Storage::delete($usuario->avatar);
        }
        $request->merge(['avatar' => $this->subirAvatar($request->file('avatar'))]);
      }

      // Encriptar password si se proporciona
      if ($request->has('password')) {
        $request->merge(['password' => Hash::make($request->password)]);
      }

      // Actualizar usuario
      $usuario->update($request->all());
      $usuario->load(['jugador', 'arbitro', 'administrador'])
        ->makeHidden(['password', 'remember_token']);

      return response()->json([
        'success' => true,
        'data' => $usuario,
        'message' => 'Usuario actualizado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al actualizar usuario',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Eliminar un usuario
   * 
   * @param int $id
   * @return JsonResponse
   */
  public function destroy($id): JsonResponse
  {
    try {
      $usuario = Usuario::findOrFail($id);

      // Eliminar avatar
      if ($usuario->avatar) {
        Storage::delete($usuario->avatar);
      }

      // Revocar todos los tokens
      $usuario->tokens()->delete();

      // Soft delete
      $usuario->delete();

      return response()->json([
        'success' => true,
        'message' => 'Usuario eliminado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al eliminar usuario',
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
          'message' => 'Usuario inactivo'
        ], 401);
      }

      // Crear token
      $tokenName = 'auth_token_' . $usuario->id;
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
      // Revocar token actual
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
   * Obtener perfil del usuario autenticado
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function profile(Request $request): JsonResponse
  {
    try {
      $usuario = $request->user();
      $usuario->load(['jugador.equipos', 'arbitro.partidos', 'administrador.equipos'])
        ->makeHidden(['password', 'remember_token']);

      return response()->json([
        'success' => true,
        'data' => $usuario,
        'message' => 'Perfil obtenido exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al obtener perfil',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Actualizar perfil del usuario autenticado
   * 
   * @param Request $request
   * @return JsonResponse
   */
  public function updateProfile(Request $request): JsonResponse
  {
    try {
      $usuario = $request->user();

      $validator = Validator::make($request->all(), [
        'nombre' => 'sometimes|string|max:100',
        'telefono' => 'nullable|string|max:20',
        'fecha_nacimiento' => 'nullable|date|before:today',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Errores de validación',
          'errors' => $validator->errors()
        ], 422);
      }

      // Subir nuevo avatar si existe
      if ($request->hasFile('avatar')) {
        if ($usuario->avatar) {
          Storage::delete($usuario->avatar);
        }
        $request->merge(['avatar' => $this->subirAvatar($request->file('avatar'))]);
      }

      $usuario->update($request->all());
      $usuario->load(['jugador', 'arbitro', 'administrador'])
        ->makeHidden(['password', 'remember_token']);

      return response()->json([
        'success' => true,
        'data' => $usuario,
        'message' => 'Perfil actualizado exitosamente'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Error al actualizar perfil',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Cambiar contraseña
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

      if (!Hash::check($request->current_password, $usuario->password)) {
        return response()->json([
          'success' => false,
          'message' => 'Contraseña actual incorrecta'
        ], 400);
      }

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

  /**
   * Subir avatar del usuario
   * 
   * @param $file
   * @return string
   */
  private function subirAvatar($file): string
  {
    $nombreArchivo = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
    return $file->storeAs('avatars', $nombreArchivo, 'public');
  }
}
