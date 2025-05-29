<?php

/**
 * REQUEST: REGISTRO
 * 
 * Comando para crear: php artisan make:request RegisterRequest
 * Archivo: app/Http/Requests/RegisterRequest.php
 * 
 * Validaciones para el proceso de registro de usuarios
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      // Datos básicos del usuario
      'nombre' => [
        'required',
        'string',
        'min:2',
        'max:100',
        'regex:/^[a-zA-ZÀ-ÿ\u00f1\u00d1\s]+$/' // Solo letras y espacios
      ],
      'email' => [
        'required',
        'string',
        'email:rfc,dns',
        'max:255',
        'unique:usuarios,email'
      ],
      'password' => [
        'required',
        'string',
        'min:8',
        'max:255',
        'confirmed',
        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/' // Al menos una mayúscula, minúscula y número
      ],
      'telefono' => [
        'nullable',
        'string',
        'max:20',
        'regex:/^[+]?[\d\s\-\(\)]+$/' // Formato de teléfono flexible
      ],
      'fecha_nacimiento' => [
        'nullable',
        'date',
        'before:today',
        'after:1900-01-01'
      ],
      'tipo_usuario' => [
        'required',
        Rule::in(['jugador', 'arbitro', 'administrador'])
      ],
      'avatar' => [
        'nullable',
        'image',
        'mimes:jpeg,png,jpg,gif',
        'max:2048' // 2MB máximo
      ],

      // Campos específicos para jugadores
      'posicion' => [
        'required_if:tipo_usuario,jugador',
        'string',
        'max:50'
      ],
      'numero_camiseta' => [
        'nullable',
        'integer',
        'min:1',
        'max:99'
      ],

      // Campos específicos para árbitros
      'licencia' => [
        'required_if:tipo_usuario,arbitro',
        'string',
        'max:50',
        'unique:arbitros,licencia'
      ],
      'posicion_arbitro' => [
        'required_if:tipo_usuario,arbitro',
        Rule::in(['principal', 'asistente', 'cuarto_arbitro'])
      ],

      // Campos específicos para administradores
      'nivel_acceso' => [
        'required_if:tipo_usuario,administrador',
        Rule::in(['super_admin', 'admin', 'moderador'])
      ],
      'permisos' => [
        'nullable',
        'array'
      ],
      'permisos.*' => [
        'string',
        'max:100'
      ]
    ];
  }

  /**
   * Get custom messages for validator errors.
   *
   * @return array<string, string>
   */
  public function messages(): array
  {
    return [
      // Mensajes para campos básicos
      'nombre.required' => 'El nombre es obligatorio.',
      'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
      'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
      'nombre.regex' => 'El nombre solo puede contener letras y espacios.',

      'email.required' => 'El email es obligatorio.',
      'email.email' => 'El email debe tener un formato válido.',
      'email.unique' => 'Ya existe una cuenta con este email.',

      'password.required' => 'La contraseña es obligatoria.',
      'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
      'password.confirmed' => 'La confirmación de contraseña no coincide.',
      'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número.',

      'telefono.regex' => 'El formato del teléfono no es válido.',
      'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
      'fecha_nacimiento.after' => 'La fecha de nacimiento debe ser posterior a 1900.',

      'tipo_usuario.required' => 'El tipo de usuario es obligatorio.',
      'tipo_usuario.in' => 'El tipo de usuario debe ser: jugador, árbitro o administrador.',

      'avatar.image' => 'El avatar debe ser una imagen.',
      'avatar.mimes' => 'El avatar debe ser un archivo JPEG, PNG, JPG o GIF.',
      'avatar.max' => 'El avatar no puede ser mayor a 2MB.',

      // Mensajes para campos específicos de jugadores
      'posicion.required_if' => 'La posición es obligatoria para jugadores.',
      'numero_camiseta.min' => 'El número de camiseta debe ser mayor a 0.',
      'numero_camiseta.max' => 'El número de camiseta debe ser menor a 100.',

      // Mensajes para campos específicos de árbitros
      'licencia.required_if' => 'La licencia es obligatoria para árbitros.',
      'licencia.unique' => 'Ya existe un árbitro con esta licencia.',
      'posicion_arbitro.required_if' => 'La posición es obligatoria para árbitros.',
      'posicion_arbitro.in' => 'La posición del árbitro debe ser: principal, asistente o cuarto árbitro.',

      // Mensajes para campos específicos de administradores
      'nivel_acceso.required_if' => 'El nivel de acceso es obligatorio para administradores.',
      'nivel_acceso.in' => 'El nivel de acceso debe ser: super_admin, admin o moderador.',
    ];
  }

  /**
   * Get custom attributes for validator errors.
   *
   * @return array<string, string>
   */
  public function attributes(): array
  {
    return [
      'nombre' => 'nombre',
      'email' => 'correo electrónico',
      'password' => 'contraseña',
      'telefono' => 'teléfono',
      'fecha_nacimiento' => 'fecha de nacimiento',
      'tipo_usuario' => 'tipo de usuario',
      'avatar' => 'foto de perfil',
      'posicion' => 'posición',
      'numero_camiseta' => 'número de camiseta',
      'licencia' => 'número de licencia',
      'posicion_arbitro' => 'posición del árbitro',
      'nivel_acceso' => 'nivel de acceso',
      'permisos' => 'permisos',
    ];
  }

  /**
   * Handle a failed validation attempt.
   *
   * @param  \Illuminate\Contracts\Validation\Validator  $validator
   * @return void
   *
   * @throws \Illuminate\Http\Exceptions\HttpResponseException
   */
  protected function failedValidation(Validator $validator)
  {
    throw new HttpResponseException(
      response()->json([
        'success' => false,
        'message' => 'Errores de validación en el registro',
        'errors' => $validator->errors(),
        'data' => null
      ], 422)
    );
  }

  /**
   * Configure the validator instance.
   *
   * @param  \Illuminate\Validation\Validator  $validator
   * @return void
   */
  public function withValidator(Validator $validator)
  {
    $validator->after(function ($validator) {
      // Validar edad mínima según tipo de usuario
      if ($this->filled('fecha_nacimiento') && $this->filled('tipo_usuario')) {
        $edad = now()->diffInYears($this->fecha_nacimiento);

        switch ($this->tipo_usuario) {
          case 'jugador':
            if ($edad < 16) {
              $validator->errors()->add('fecha_nacimiento', 'Los jugadores deben tener al menos 16 años.');
            }
            break;
          case 'arbitro':
            if ($edad < 18) {
              $validator->errors()->add('fecha_nacimiento', 'Los árbitros deben tener al menos 18 años.');
            }
            break;
          case 'administrador':
            if ($edad < 21) {
              $validator->errors()->add('fecha_nacimiento', 'Los administradores deben tener al menos 21 años.');
            }
            break;
        }
      }

      // Validar que la licencia tenga un formato específico (ejemplo)
      if ($this->filled('licencia')) {
        if (!preg_match('/^[A-Z]{3}[0-9]{3,4}$/', $this->licencia)) {
          $validator->errors()->add('licencia', 'La licencia debe tener el formato: ABC123 o ABC1234.');
        }
      }
    });
  }

  /**
   * Get data to be validated from the request.
   *
   * @return array
   */
  public function validationData()
  {
    $data = $this->all();

    // Limpiar y formatear datos antes de la validación
    if (isset($data['nombre'])) {
      $data['nombre'] = trim($data['nombre']);
    }

    if (isset($data['email'])) {
      $data['email'] = strtolower(trim($data['email']));
    }

    if (isset($data['telefono'])) {
      $data['telefono'] = preg_replace('/[^\d+\-\(\)\s]/', '', $data['telefono']);
    }

    return $data;
  }
}
