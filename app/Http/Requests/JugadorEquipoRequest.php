<?php

/**
 * REQUEST: JUGADOR-EQUIPO
 * 
 * Comando para crear: php artisan make:request JugadorEquipoRequest
 * Archivo: app/Http/Requests/JugadorEquipoRequest.php
 * 
 * Validaciones para agregar/actualizar jugadores en equipos
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class JugadorEquipoRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    $user = $this->user();

    if (!$user) {
      return false;
    }

    // Administradores pueden gestionar jugadores en equipos
    if ($user->tipo_usuario === 'administrador') {
      return true;
    }

    // El administrador del equipo puede gestionar sus jugadores
    $equipoId = $this->route('equipo');
    if ($equipoId) {
      $equipo = \App\Models\Equipo::find($equipoId);
      return $equipo && $equipo->administrador_id === $user->id;
    }

    return false;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    $equipoId = $this->route('equipo');
    $jugadorId = $this->route('jugador') ?? $this->input('jugador_id');
    $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

    return [
      'jugador_id' => [
        'required_unless:' . $isUpdate,
        'exists:jugadores,id',
        function ($attribute, $value, $fail) use ($equipoId, $isUpdate) {
          if (!$isUpdate && $equipoId && $value) {
            // Verificar que el jugador no esté ya en el equipo
            $yaEnEquipo = \App\Models\Equipo::find($equipoId)
              ->jugadores()
              ->where('jugador_id', $value)
              ->exists();

            if ($yaEnEquipo) {
              $fail('El jugador ya pertenece a este equipo.');
            }

            // Verificar que el jugador no esté en demasiados equipos activos
            $equiposActivos = DB::table('jugador_equipo')
              ->where('jugador_id', $value)
              ->where('estado', 'activo')
              ->count();

            if ($equiposActivos >= 3) { // Máximo 3 equipos activos
              $fail('El jugador ya está en el máximo de equipos activos permitidos (3).');
            }
          }
        },
      ],
      'numero_camiseta' => [
        'required',
        'integer',
        'min:1',
        'max:99',
        function ($attribute, $value, $fail) use ($equipoId, $jugadorId, $isUpdate) {
          if ($equipoId && $value) {
            // Verificar que el número no esté ocupado por otro jugador
            $numeroOcupado = DB::table('jugador_equipo')
              ->where('equipo_id', $equipoId)
              ->where('numero_camiseta', $value)
              ->when($isUpdate && $jugadorId, function ($query) use ($jugadorId) {
                return $query->where('jugador_id', '!=', $jugadorId);
              })
              ->exists();

            if ($numeroOcupado) {
              $fail('El número de camiseta ya está ocupado por otro jugador.');
            }
          }
        },
      ],
      'posicion' => [
        'required',
        'string',
        'max:50',
        function ($attribute, $value, $fail) use ($equipoId) {
          if ($equipoId) {
            $equipo = \App\Models\Equipo::with('deporte')->find($equipoId);
            if ($equipo) {
              $posicionesValidas = $this->getPosicionesValidas($equipo->deporte->nombre);
              if (!empty($posicionesValidas) && !in_array($value, $posicionesValidas)) {
                $fail('La posición no es válida para ' . $equipo->deporte->nombre . '. Posiciones válidas: ' . implode(', ', $posicionesValidas));
              }
            }
          }
        },
      ],
      'estado' => [
        'sometimes',
        Rule::in(['activo', 'inactivo', 'lesionado', 'suspendido'])
      ],
      'es_capitan' => [
        'sometimes',
        'boolean',
        function ($attribute, $value, $fail) use ($equipoId, $jugadorId) {
          if ($value && $equipoId) {
            // Solo puede haber un capitán por equipo
            $yaHayCapitan = DB::table('jugador_equipo')
              ->where('equipo_id', $equipoId)
              ->where('es_capitan', true)
              ->when($jugadorId, function ($query) use ($jugadorId) {
                return $query->where('jugador_id', '!=', $jugadorId);
              })
              ->exists();

            if ($yaHayCapitan) {
              $fail('Ya hay un capitán designado para este equipo. Primero debe remover la capitanía del jugador actual.');
            }
          }
        },
      ],
      'es_titular' => [
        'sometimes',
        'boolean',
      ],
      'fecha_incorporacion' => [
        'sometimes',
        'date',
        'before_or_equal:today',
        'after:2000-01-01'
      ],
      'fecha_salida' => [
        'nullable',
        'date',
        'after:fecha_incorporacion',
        function ($attribute, $value, $fail) {
          if ($value && $this->filled('estado') && $this->estado === 'activo') {
            $fail('No se puede establecer fecha de salida para un jugador activo.');
          }
        },
      ],
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
      'jugador_id.required_unless' => 'El jugador es obligatorio.',
      'jugador_id.exists' => 'El jugador seleccionado no existe.',

      'numero_camiseta.required' => 'El número de camiseta es obligatorio.',
      'numero_camiseta.integer' => 'El número de camiseta debe ser un número entero.',
      'numero_camiseta.min' => 'El número de camiseta debe ser mayor a 0.',
      'numero_camiseta.max' => 'El número de camiseta debe ser menor a 100.',

      'posicion.required' => 'La posición es obligatoria.',
      'posicion.string' => 'La posición debe ser texto.',
      'posicion.max' => 'La posición no puede exceder 50 caracteres.',

      'estado.in' => 'El estado debe ser: activo, inactivo, lesionado o suspendido.',

      'es_capitan.boolean' => 'El campo capitán debe ser verdadero o falso.',
      'es_titular.boolean' => 'El campo titular debe ser verdadero o falso.',

      'fecha_incorporacion.date' => 'La fecha de incorporación debe ser una fecha válida.',
      'fecha_incorporacion.before_or_equal' => 'La fecha de incorporación no puede ser futura.',
      'fecha_incorporacion.after' => 'La fecha de incorporación debe ser posterior al año 2000.',

      'fecha_salida.date' => 'La fecha de salida debe ser una fecha válida.',
      'fecha_salida.after' => 'La fecha de salida debe ser posterior a la fecha de incorporación.',
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
      'jugador_id' => 'jugador',
      'numero_camiseta' => 'número de camiseta',
      'posicion' => 'posición',
      'estado' => 'estado',
      'es_capitan' => 'es capitán',
      'es_titular' => 'es titular',
      'fecha_incorporacion' => 'fecha de incorporación',
      'fecha_salida' => 'fecha de salida',
    ];
  }

  /**
   * Handle a failed authorization attempt.
   *
   * @return void
   *
   * @throws \Illuminate\Http\Exceptions\HttpResponseException
   */
  protected function failedAuthorization()
  {
    throw new HttpResponseException(
      response()->json([
        'success' => false,
        'message' => 'No tienes permisos para gestionar jugadores en este equipo',
        'errors' => ['authorization' => ['Solo los administradores del equipo pueden gestionar jugadores.']],
        'data' => null
      ], 403)
    );
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
        'message' => 'Errores de validación en la asignación de jugador',
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
      // Validar límites de jugadores por posición
      if ($this->filled(['posicion']) && $this->route('equipo')) {
        $equipoId = $this->route('equipo');
        $posicion = $this->posicion;
        $jugadorId = $this->route('jugador') ?? $this->input('jugador_id');

        $jugadoresEnPosicion = DB::table('jugador_equipo')
          ->where('equipo_id', $equipoId)
          ->where('posicion', $posicion)
          ->where('estado', 'activo')
          ->when($jugadorId, function ($query) use ($jugadorId) {
            return $query->where('jugador_id', '!=', $jugadorId);
          })
          ->count();

        $limitesPorPosicion = $this->getLimitesPorPosicion();
        $limite = $limitesPorPosicion[$posicion] ?? null;

        if ($limite && $jugadoresEnPosicion >= $limite) {
          $validator->errors()->add(
            'posicion',
            "Ya hay el máximo de jugadores permitidos en la posición {$posicion} ({$limite})."
          );
        }
      }

      // Validar que el jugador tenga la edad apropiada
      if ($this->filled('jugador_id')) {
        $jugador = \App\Models\Jugador::with('usuario')->find($this->jugador_id);
        if ($jugador && $jugador->usuario->fecha_nacimiento) {
          $edad = now()->diffInYears($jugador->usuario->fecha_nacimiento);

          if ($edad < 16) {
            $validator->errors()->add(
              'jugador_id',
              'El jugador debe tener al menos 16 años para participar.'
            );
          }

          if ($edad > 45) {
            $validator->errors()->add(
              'jugador_id',
              'El jugador no puede tener más de 45 años para participar.'
            );
          }
        }
      }

      // Validar números de camiseta especiales para capitán
      if ($this->filled(['numero_camiseta', 'es_capitan']) && $this->es_capitan) {
        $numerosCapitanes = [1, 10]; // Números tradicionales de capitán
        if (!in_array($this->numero_camiseta, $numerosCapitanes)) {
          $validator->errors()->add(
            'numero_camiseta',
            'Los capitanes tradicionalmente usan los números 1 o 10.'
          );
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

    // Establecer fecha de incorporación por defecto si no se proporciona
    if (!isset($data['fecha_incorporacion'])) {
      $data['fecha_incorporacion'] = now()->format('Y-m-d');
    }

    // Estado por defecto
    if (!isset($data['estado'])) {
      $data['estado'] = 'activo';
    }

    // Valores booleanos por defecto
    if (!isset($data['es_capitan'])) {
      $data['es_capitan'] = false;
    }

    if (!isset($data['es_titular'])) {
      $data['es_titular'] = false;
    }

    // Limpiar posición
    if (isset($data['posicion'])) {
      $data['posicion'] = trim(ucwords(strtolower($data['posicion'])));
    }

    return $data;
  }

  /**
   * Obtener posiciones válidas por deporte
   *
   * @param string $deporte
   * @return array
   */
  private function getPosicionesValidas(string $deporte): array
  {
    return match (strtolower($deporte)) {
      'fútbol' => [
        'Portero',
        'Defensa Central',
        'Lateral Derecho',
        'Lateral Izquierdo',
        'Centrocampista Defensivo',
        'Centrocampista',
        'Centrocampista Ofensivo',
        'Extremo Derecho',
        'Extremo Izquierdo',
        'Delantero Centro',
        'Segundo Delantero'
      ],
      'baloncesto' => [
        'Base',
        'Escolta',
        'Alero',
        'Ala-Pívot',
        'Pívot'
      ],
      'voleibol' => [
        'Colocador',
        'Libero',
        'Atacante',
        'Central',
        'Opuesto',
        'Receptor'
      ],
      default => [] // Sin restricciones para deportes no definidos
    };
  }

  /**
   * Obtener límites de jugadores por posición
   *
   * @return array
   */
  private function getLimitesPorPosicion(): array
  {
    return [
      'Portero' => 3,
      'Defensa Central' => 4,
      'Lateral Derecho' => 2,
      'Lateral Izquierdo' => 2,
      'Centrocampista Defensivo' => 3,
      'Centrocampista' => 4,
      'Centrocampista Ofensivo' => 3,
      'Extremo Derecho' => 2,
      'Extremo Izquierdo' => 2,
      'Delantero Centro' => 3,
      'Segundo Delantero' => 2,
      // Baloncesto
      'Base' => 3,
      'Escolta' => 3,
      'Alero' => 4,
      'Ala-Pívot' => 3,
      'Pívot' => 3,
      // Voleibol
      'Colocador' => 2,
      'Libero' => 2,
      'Atacante' => 4,
      'Central' => 4,
      'Opuesto' => 2,
      'Receptor' => 4,
    ];
  }
}
