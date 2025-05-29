<?php

/**
 * REQUEST: TORNEOS
 * 
 * Comando para crear: php artisan make:request TorneoRequest
 * Archivo: app/Http/Requests/TorneoRequest.php
 * 
 * Validaciones para crear y actualizar torneos
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TorneoRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    // Solo administradores pueden crear/editar torneos
    $user = $this->user();
    return $user && $user->tipo_usuario === 'administrador';
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    $torneoId = $this->route('torneo'); // Para updates

    return [
      'nombre' => [
        'required',
        'string',
        'min:3',
        'max:150',
        Rule::unique('torneos')->ignore($torneoId)
      ],
      'descripcion' => [
        'nullable',
        'string',
        'max:1000'
      ],
      'formato' => [
        'required',
        Rule::in(['liga', 'eliminacion', 'grupos'])
      ],
      'estado' => [
        'sometimes',
        Rule::in(['planificacion', 'activo', 'finalizado', 'cancelado'])
      ],
      'fecha_inicio' => [
        'required',
        'date',
        'after:today'
      ],
      'fecha_fin' => [
        'required',
        'date',
        'after:fecha_inicio'
      ],
      'fecha_inscripcion_limite' => [
        'nullable',
        'date',
        'before:fecha_inicio',
        'after:today'
      ],
      'deporte_id' => [
        'required',
        'exists:deportes,id'
      ],
      'configuracion' => [
        'nullable',
        'array'
      ],
      'configuracion.max_equipos' => [
        'sometimes',
        'integer',
        'min:2',
        'max:64'
      ],
      'configuracion.min_equipos' => [
        'sometimes',
        'integer',
        'min:2',
        'lte:configuracion.max_equipos'
      ],
      'configuracion.permite_empates' => [
        'sometimes',
        'boolean'
      ],
      'configuracion.puntos_victoria' => [
        'sometimes',
        'integer',
        'min:1',
        'max:10'
      ],
      'configuracion.puntos_empate' => [
        'sometimes',
        'integer',
        'min:0',
        'max:5'
      ],
      'configuracion.puntos_derrota' => [
        'sometimes',
        'integer',
        'min:0',
        'max:2'
      ],
      'configuracion.partidos_ida_vuelta' => [
        'sometimes',
        'boolean'
      ],
      'premios' => [
        'nullable',
        'array'
      ],
      'premios.primer_lugar' => [
        'sometimes',
        'string',
        'max:100'
      ],
      'premios.segundo_lugar' => [
        'sometimes',
        'string',
        'max:100'
      ],
      'premios.tercer_lugar' => [
        'sometimes',
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
      'nombre.required' => 'El nombre del torneo es obligatorio.',
      'nombre.min' => 'El nombre del torneo debe tener al menos 3 caracteres.',
      'nombre.max' => 'El nombre del torneo no puede exceder 150 caracteres.',
      'nombre.unique' => 'Ya existe un torneo con este nombre.',

      'descripcion.max' => 'La descripción no puede exceder 1000 caracteres.',

      'formato.required' => 'El formato del torneo es obligatorio.',
      'formato.in' => 'El formato debe ser: liga, eliminación o grupos.',

      'estado.in' => 'El estado debe ser: planificación, activo, finalizado o cancelado.',

      'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
      'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
      'fecha_inicio.after' => 'La fecha de inicio debe ser posterior a hoy.',

      'fecha_fin.required' => 'La fecha de fin es obligatoria.',
      'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
      'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',

      'fecha_inscripcion_limite.date' => 'La fecha límite de inscripción debe ser una fecha válida.',
      'fecha_inscripcion_limite.before' => 'La fecha límite debe ser anterior al inicio del torneo.',
      'fecha_inscripcion_limite.after' => 'La fecha límite debe ser posterior a hoy.',

      'deporte_id.required' => 'El deporte es obligatorio.',
      'deporte_id.exists' => 'El deporte seleccionado no existe.',

      'configuracion.max_equipos.integer' => 'El máximo de equipos debe ser un número.',
      'configuracion.max_equipos.min' => 'Debe permitirse al menos 2 equipos.',
      'configuracion.max_equipos.max' => 'No se pueden permitir más de 64 equipos.',

      'configuracion.min_equipos.integer' => 'El mínimo de equipos debe ser un número.',
      'configuracion.min_equipos.min' => 'Debe requerirse al menos 2 equipos.',
      'configuracion.min_equipos.lte' => 'El mínimo no puede ser mayor al máximo de equipos.',

      'configuracion.puntos_victoria.min' => 'Los puntos por victoria deben ser al menos 1.',
      'configuracion.puntos_victoria.max' => 'Los puntos por victoria no pueden exceder 10.',

      'configuracion.puntos_empate.min' => 'Los puntos por empate no pueden ser negativos.',
      'configuracion.puntos_empate.max' => 'Los puntos por empate no pueden exceder 5.',

      'configuracion.puntos_derrota.min' => 'Los puntos por derrota no pueden ser negativos.',
      'configuracion.puntos_derrota.max' => 'Los puntos por derrota no pueden exceder 2.',
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
      'nombre' => 'nombre del torneo',
      'descripcion' => 'descripción',
      'formato' => 'formato del torneo',
      'estado' => 'estado del torneo',
      'fecha_inicio' => 'fecha de inicio',
      'fecha_fin' => 'fecha de finalización',
      'fecha_inscripcion_limite' => 'fecha límite de inscripción',
      'deporte_id' => 'deporte',
      'configuracion.max_equipos' => 'máximo de equipos',
      'configuracion.min_equipos' => 'mínimo de equipos',
      'configuracion.permite_empates' => 'permite empates',
      'configuracion.puntos_victoria' => 'puntos por victoria',
      'configuracion.puntos_empate' => 'puntos por empate',
      'configuracion.puntos_derrota' => 'puntos por derrota',
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
        'message' => 'No tienes permisos para gestionar torneos',
        'errors' => ['authorization' => ['Solo los administradores pueden crear o editar torneos.']],
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
        'message' => 'Errores de validación en el torneo',
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
      // Validar duración mínima del torneo
      if ($this->filled(['fecha_inicio', 'fecha_fin'])) {
        $inicio = Carbon::parse($this->fecha_inicio);
        $fin = Carbon::parse($this->fecha_fin);
        $duracionDias = $inicio->diffInDays($fin);

        if ($duracionDias < 1) {
          $validator->errors()->add('fecha_fin', 'El torneo debe durar al menos 1 día.');
        }

        if ($duracionDias > 365) {
          $validator->errors()->add('fecha_fin', 'El torneo no puede durar más de 1 año.');
        }
      }

      // Validar configuración según formato
      if ($this->filled('formato')) {
        switch ($this->formato) {
          case 'eliminacion':
            // En eliminación no deberían permitirse empates
            if ($this->input('configuracion.permite_empates', false)) {
              $validator->errors()->add(
                'configuracion.permite_empates',
                'Los torneos de eliminación no pueden permitir empates.'
              );
            }
            break;

          case 'liga':
            // En liga se requiere un número par de equipos para ida y vuelta
            if ($this->input('configuracion.partidos_ida_vuelta', false)) {
              $maxEquipos = $this->input('configuracion.max_equipos');
              if ($maxEquipos && $maxEquipos > 20) {
                $validator->errors()->add(
                  'configuracion.max_equipos',
                  'Para liga ida y vuelta, el máximo recomendado es 20 equipos.'
                );
              }
            }
            break;
        }
      }

      // Validar que los puntos tengan sentido
      if ($this->filled(['configuracion.puntos_victoria', 'configuracion.puntos_empate', 'configuracion.puntos_derrota'])) {
        $pVictoria = $this->input('configuracion.puntos_victoria', 3);
        $pEmpate = $this->input('configuracion.puntos_empate', 1);
        $pDerrota = $this->input('configuracion.puntos_derrota', 0);

        if ($pVictoria <= $pEmpate) {
          $validator->errors()->add(
            'configuracion.puntos_victoria',
            'Los puntos por victoria deben ser mayores que los puntos por empate.'
          );
        }

        if ($pEmpate < $pDerrota) {
          $validator->errors()->add(
            'configuracion.puntos_empate',
            'Los puntos por empate deben ser mayores o iguales que los puntos por derrota.'
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

    // Limpiar nombre
    if (isset($data['nombre'])) {
      $data['nombre'] = trim($data['nombre']);
    }

    // Establecer configuración por defecto según formato
    if (isset($data['formato']) && !isset($data['configuracion'])) {
      $data['configuracion'] = $this->getDefaultConfiguration($data['formato']);
    }

    return $data;
  }

  /**
   * Obtener configuración por defecto según formato
   *
   * @param string $formato
   * @return array
   */
  private function getDefaultConfiguration(string $formato): array
  {
    return match ($formato) {
      'liga' => [
        'max_equipos' => 20,
        'min_equipos' => 4,
        'permite_empates' => true,
        'puntos_victoria' => 3,
        'puntos_empate' => 1,
        'puntos_derrota' => 0,
        'partidos_ida_vuelta' => true,
      ],
      'eliminacion' => [
        'max_equipos' => 32,
        'min_equipos' => 4,
        'permite_empates' => false,
        'puntos_victoria' => 1,
        'puntos_empate' => 0,
        'puntos_derrota' => 0,
        'desempate_penales' => true,
      ],
      'grupos' => [
        'max_equipos' => 16,
        'min_equipos' => 8,
        'permite_empates' => true,
        'puntos_victoria' => 3,
        'puntos_empate' => 1,
        'puntos_derrota' => 0,
        'equipos_por_grupo' => 4,
        'clasifican_por_grupo' => 2,
      ],
      default => []
    };
  }
}
