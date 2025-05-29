<?php

/**
 * REQUEST: PARTIDOS
 * 
 * Comando para crear: php artisan make:request PartidoRequest
 * Archivo: app/Http/Requests/PartidoRequest.php
 * 
 * Validaciones para crear y actualizar partidos
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PartidoRequest extends FormRequest
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

        // Administradores pueden gestionar partidos
        if ($user->tipo_usuario === 'administrador') {
            return true;
        }

        // Árbitros pueden actualizar partidos asignados a ellos
        if ($user->tipo_usuario === 'arbitro' && ($this->isMethod('PUT') || $this->isMethod('PATCH'))) {
            $partidoId = $this->route('partido');
            if ($partidoId) {
                $partido = \App\Models\Partido::find($partidoId);
                return $partido && $partido->arbitro_id === $user->arbitro->id;
            }
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
        $partidoId = $this->route('partido');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'torneo_id' => [
                'required',
                'exists:torneos,id',
                function ($attribute, $value, $fail) use ($partidoId) {
                    $torneo = \App\Models\Torneo::find($value);
                    if ($torneo && !in_array($torneo->estado, ['planificacion', 'activo'])) {
                        $fail('Solo se pueden crear partidos en torneos en planificación o activos.');
                    }
                },
            ],
            'equipo_local_id' => [
                'required',
                'exists:equipos,id',
                'different:equipo_visitante_id',
                function ($attribute, $value, $fail) {
                    if ($this->filled('torneo_id')) {
                        $torneo = \App\Models\Torneo::find($this->torneo_id);
                        if ($torneo && !$torneo->equipos()->where('equipo_id', $value)->exists()) {
                            $fail('El equipo local debe estar inscrito en el torneo.');
                        }
                    }
                },
            ],
            'equipo_visitante_id' => [
                'required',
                'exists:equipos,id',
                'different:equipo_local_id',
                function ($attribute, $value, $fail) {
                    if ($this->filled('torneo_id')) {
                        $torneo = \App\Models\Torneo::find($this->torneo_id);
                        if ($torneo && !$torneo->equipos()->where('equipo_id', $value)->exists()) {
                            $fail('El equipo visitante debe estar inscrito en el torneo.');
                        }
                    }
                },
            ],
            'fecha' => [
                'required',
                'date',
                'after:now',
                function ($attribute, $value, $fail) {
                    if ($this->filled('torneo_id')) {
                        $torneo = \App\Models\Torneo::find($this->torneo_id);
                        if ($torneo) {
                            $fechaPartido = Carbon::parse($value);
                            $fechaInicio = Carbon::parse($torneo->fecha_inicio);
                            $fechaFin = Carbon::parse($torneo->fecha_fin);

                            if ($fechaPartido->lt($fechaInicio) || $fechaPartido->gt($fechaFin)) {
                                $fail('La fecha del partido debe estar dentro del período del torneo.');
                            }
                        }
                    }
                },
            ],
            'lugar' => [
                'nullable',
                'string',
                'max:150',
                'min:3'
            ],
            'estado' => [
                'sometimes',
                Rule::in(['programado', 'en_curso', 'finalizado', 'suspendido', 'cancelado']),
                function ($attribute, $value, $fail) use ($isUpdate, $partidoId) {
                    if ($isUpdate && $partidoId) {
                        $partido = \App\Models\Partido::find($partidoId);
                        if ($partido) {
                            // Validar transiciones de estado válidas
                            $transicionesValidas = [
                                'programado' => ['en_curso', 'suspendido', 'cancelado'],
                                'en_curso' => ['finalizado', 'suspendido'],
                                'suspendido' => ['programado', 'en_curso', 'cancelado'],
                                'finalizado' => [], // No se puede cambiar desde finalizado
                                'cancelado' => [], // No se puede cambiar desde cancelado
                            ];

                            $estadoActual = $partido->estado;
                            if (!in_array($value, $transicionesValidas[$estadoActual] ?? [])) {
                                $fail("No se puede cambiar el estado de '{$estadoActual}' a '{$value}'.");
                            }
                        }
                    }
                },
            ],
            'arbitro_id' => [
                'nullable',
                'exists:arbitros,id',
                function ($attribute, $value, $fail) use ($partidoId) {
                    if ($value && $this->filled('fecha')) {
                        $fechaPartido = Carbon::parse($this->fecha);
                        $conflicto = \App\Models\Partido::where('arbitro_id', $value)
                            ->where('fecha', $fechaPartido)
                            ->whereNotIn('estado', ['cancelado'])
                            ->when($partidoId, function ($query) use ($partidoId) {
                                return $query->where('id', '!=', $partidoId);
                            })
                            ->exists();

                        if ($conflicto) {
                            $fail('El árbitro ya tiene un partido asignado en esa fecha y hora.');
                        }
                    }
                },
            ],

            'goles_local' => [
                'sometimes',
                'integer',
                'min:0',
                'max:50' // Máximo realista
            ],
            'goles_visitante' => [
                'sometimes',
                'integer',
                'min:0',
                'max:50'
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'estadisticas' => [
                'nullable',
                'array'
            ],
            'estadisticas.posesion_local' => [
                'sometimes',
                'integer',
                'min:0',
                'max:100'
            ],
            'estadisticas.posesion_visitante' => [
                'sometimes',
                'integer',
                'min:0',
                'max:100'
            ],
            'estadisticas.tarjetas_amarillas' => [
                'sometimes',
                'integer',
                'min:0',
                'max:20'
            ],
            'estadisticas.tarjetas_rojas' => [
                'sometimes',
                'integer',
                'min:0',
                'max:10'
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
            'torneo_id.required' => 'El torneo es obligatorio.',
            'torneo_id.exists' => 'El torneo seleccionado no existe.',

            'equipo_local_id.required' => 'El equipo local es obligatorio.',
            'equipo_local_id.exists' => 'El equipo local seleccionado no existe.',
            'equipo_local_id.different' => 'El equipo local debe ser diferente al equipo visitante.',

            'equipo_visitante_id.required' => 'El equipo visitante es obligatorio.',
            'equipo_visitante_id.exists' => 'El equipo visitante seleccionado no existe.',
            'equipo_visitante_id.different' => 'El equipo visitante debe ser diferente al equipo local.',

            'fecha.required' => 'La fecha del partido es obligatoria.',
            'fecha.date' => 'La fecha debe ser una fecha válida.',
            'fecha.after' => 'La fecha del partido debe ser futura.',

            'lugar.string' => 'El lugar debe ser texto.',
            'lugar.max' => 'El lugar no puede exceder 150 caracteres.',
            'lugar.min' => 'El lugar debe tener al menos 3 caracteres.',

            'estado.in' => 'El estado debe ser: programado, en curso, finalizado, suspendido o cancelado.',

            'arbitro_id.exists' => 'El árbitro seleccionado no existe.',

            'goles_local.integer' => 'Los goles del equipo local deben ser un número entero.',
            'goles_local.min' => 'Los goles no pueden ser negativos.',
            'goles_local.max' => 'Los goles no pueden exceder 50.',

            'goles_visitante.integer' => 'Los goles del equipo visitante deben ser un número entero.',
            'goles_visitante.min' => 'Los goles no pueden ser negativos.',
            'goles_visitante.max' => 'Los goles no pueden exceder 50.',

            'observaciones.max' => 'Las observaciones no pueden exceder 1000 caracteres.',

            'estadisticas.posesion_local.max' => 'La posesión no puede exceder 100%.',
            'estadisticas.posesion_visitante.max' => 'La posesión no puede exceder 100%.',
            'estadisticas.tarjetas_amarillas.max' => 'Las tarjetas amarillas no pueden exceder 20.',
            'estadisticas.tarjetas_rojas.max' => 'Las tarjetas rojas no pueden exceder 10.',
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
            'torneo_id' => 'torneo',
            'equipo_local_id' => 'equipo local',
            'equipo_visitante_id' => 'equipo visitante',
            'fecha' => 'fecha del partido',
            'lugar' => 'lugar del partido',
            'estado' => 'estado del partido',
            'arbitro_id' => 'árbitro',
            'goles_local' => 'goles del equipo local',
            'goles_visitante' => 'goles del equipo visitante',
            'observaciones' => 'observaciones',
            'estadisticas' => 'estadísticas',
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
                'message' => 'No tienes permisos para gestionar partidos',
                'errors' => ['authorization' => ['Solo los administradores pueden crear partidos, y los árbitros pueden actualizar sus partidos asignados.']],
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
                'message' => 'Errores de validación en el partido',
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
        $partidoId = $this->route('partido'); // Definir aquí para usar en las validaciones

        $validator->after(function ($validator) use ($partidoId) {
            // Validar que los equipos sean del mismo deporte que el torneo
            if ($this->filled(['torneo_id', 'equipo_local_id', 'equipo_visitante_id'])) {
                $torneo = \App\Models\Torneo::with('deporte')->find($this->torneo_id);
                $equipoLocal = \App\Models\Equipo::find($this->equipo_local_id);
                $equipoVisitante = \App\Models\Equipo::find($this->equipo_visitante_id);

                if ($torneo && $equipoLocal && $equipoLocal->deporte_id !== $torneo->deporte_id) {
                    $validator->errors()->add(
                        'equipo_local_id',
                        'El equipo local debe pertenecer al mismo deporte que el torneo.'
                    );
                }

                if ($torneo && $equipoVisitante && $equipoVisitante->deporte_id !== $torneo->deporte_id) {
                    $validator->errors()->add(
                        'equipo_visitante_id',
                        'El equipo visitante debe pertenecer al mismo deporte que el torneo.'
                    );
                }
            }

            // Validar posesión total si se proporcionan ambos valores
            if ($this->filled(['estadisticas.posesion_local', 'estadisticas.posesion_visitante'])) {
                $posesionTotal = $this->input('estadisticas.posesion_local') + $this->input('estadisticas.posesion_visitante');
                if ($posesionTotal !== 100) {
                    $validator->errors()->add(
                        'estadisticas.posesion_local',
                        'La suma de la posesión de ambos equipos debe ser 100%.'
                    );
                }
            }

            // Validar que no se programen demasiados partidos el mismo día en el mismo lugar
            if ($this->filled(['fecha', 'lugar'])) {
                $fecha = Carbon::parse($this->fecha)->format('Y-m-d');
                $partidosMismoDia = \App\Models\Partido::whereDate('fecha', $fecha)
                    ->where('lugar', $this->lugar)
                    ->whereNotIn('estado', ['cancelado'])
                    ->when($partidoId, function ($query) use ($partidoId) {
                        return $query->where('id', '!=', $partidoId);
                    })
                    ->count();

                if ($partidosMismoDia >= 3) { // Máximo 3 partidos por día en el mismo lugar
                    $validator->errors()->add(
                        'fecha',
                        'Ya hay demasiados partidos programados para ese día en el mismo lugar.'
                    );
                }
            }

            // Validar horarios realistas (no muy tarde o muy temprano)
            if ($this->filled('fecha')) {
                $fechaPartido = Carbon::parse($this->fecha);
                $hora = $fechaPartido->hour;

                if ($hora < 8 || $hora > 22) {
                    $validator->errors()->add(
                        'fecha',
                        'Los partidos deben programarse entre las 08:00 y las 22:00 horas.'
                    );
                }
            }

            // Validar que no se repita el enfrentamiento muy pronto
            if ($this->filled(['torneo_id', 'equipo_local_id', 'equipo_visitante_id', 'fecha'])) {
                $fechaPartido = Carbon::parse($this->fecha);
                $enfrentamientoReciente = \App\Models\Partido::where('torneo_id', $this->torneo_id)
                    ->where(function ($query) {
                        $query->where(function ($q) {
                            $q->where('equipo_local_id', $this->equipo_local_id)
                                ->where('equipo_visitante_id', $this->equipo_visitante_id);
                        })->orWhere(function ($q) {
                            $q->where('equipo_local_id', $this->equipo_visitante_id)
                                ->where('equipo_visitante_id', $this->equipo_local_id);
                        });
                    })
                    ->where('fecha', '>', $fechaPartido->copy()->subDays(7))
                    ->where('fecha', '<', $fechaPartido->copy()->addDays(7))
                    ->whereNotIn('estado', ['cancelado'])
                    ->when($partidoId, function ($query) use ($partidoId) {
                        return $query->where('id', '!=', $partidoId);
                    })
                    ->exists();

                if ($enfrentamientoReciente) {
                    $validator->errors()->add(
                        'fecha',
                        'Estos equipos ya tienen un enfrentamiento programado cerca de esta fecha.'
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

        // Formatear fecha si viene como string
        if (isset($data['fecha'])) {
            try {
                $data['fecha'] = Carbon::parse($data['fecha'])->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // Mantener el valor original para que falle la validación
            }
        }

        // Limpiar lugar
        if (isset($data['lugar'])) {
            $data['lugar'] = trim($data['lugar']);
        }

        // Limpiar observaciones
        if (isset($data['observaciones'])) {
            $data['observaciones'] = trim($data['observaciones']);
        }

        // Generar resultado automáticamente si se proporcionan goles
        if (isset($data['goles_local']) && isset($data['goles_visitante'])) {
            $data['resultado'] = $data['goles_local'] . '-' . $data['goles_visitante'];
        }

        return $data;
    }
}
