<?php

/**
 * REQUEST: EQUIPOS
 * 
 * Comando para crear: php artisan make:request EquipoRequest
 * Archivo: app/Http/Requests/EquipoRequest.php
 * 
 * Validaciones para crear y actualizar equipos
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class EquipoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        // Solo administradores pueden crear/editar equipos
        // O el administrador específico del equipo puede editarlo
        if (!$user) {
            return false;
        }

        if ($user->tipo_usuario === 'administrador') {
            return true;
        }

        // Si es una actualización, verificar si es el administrador del equipo
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $equipoId = $this->route('equipo');
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
        $equipoId = $this->route('equipo'); // Para updates
        $deporteId = $this->input('deporte_id');

        return [
            'nombre' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-ZÀ-ÿ\u00f1\u00d1\s\d\-\.]+$/', // Letras, números, espacios, guiones y puntos
                // Nombre único por deporte
                Rule::unique('equipos')->where(function ($query) use ($deporteId) {
                    return $query->where('deporte_id', $deporteId);
                })->ignore($equipoId)
            ],
            'email' => [
                'nullable',
                'email:rfc,dns',
                'max:100',
                Rule::unique('equipos')->ignore($equipoId)
            ],
            'telefono' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[+]?[\d\s\-\(\)]+$/' // Formato de teléfono flexible
            ],
            'deporte_id' => [
                'required',
                'exists:deportes,id',
                function ($attribute, $value, $fail) {
                    $deporte = \App\Models\Deporte::find($value);
                    if ($deporte && !$deporte->activo) {
                        $fail('El deporte seleccionado no está activo.');
                    }
                },
            ],
            'administrador_id' => [
                'nullable',
                'exists:usuarios,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $usuario = \App\Models\Usuario::find($value);
                        if ($usuario && $usuario->tipo_usuario !== 'administrador') {
                            $fail('El usuario seleccionado debe ser un administrador.');
                        }
                        if ($usuario && !$usuario->activo) {
                            $fail('El administrador seleccionado no está activo.');
                        }
                    }
                },
            ],
            'activo' => [
                'sometimes',
                'boolean'
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048', // 2MB máximo
                'dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000'
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
            'nombre.required' => 'El nombre del equipo es obligatorio.',
            'nombre.min' => 'El nombre del equipo debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre del equipo no puede exceder 100 caracteres.',
            'nombre.regex' => 'El nombre solo puede contener letras, números, espacios, guiones y puntos.',
            'nombre.unique' => 'Ya existe un equipo con este nombre en el deporte seleccionado.',

            'email.email' => 'El email debe tener un formato válido.',
            'email.max' => 'El email no puede exceder 100 caracteres.',
            'email.unique' => 'Ya existe un equipo con este email.',

            'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
            'telefono.regex' => 'El formato del teléfono no es válido.',

            'deporte_id.required' => 'El deporte es obligatorio.',
            'deporte_id.exists' => 'El deporte seleccionado no existe.',

            'administrador_id.exists' => 'El administrador seleccionado no existe.',

            'logo.image' => 'El logo debe ser una imagen.',
            'logo.mimes' => 'El logo debe ser un archivo JPEG, PNG, JPG, GIF o SVG.',
            'logo.max' => 'El logo no puede ser mayor a 2MB.',
            'logo.dimensions' => 'El logo debe tener entre 100x100 y 1000x1000 píxeles.',
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
            'nombre' => 'nombre del equipo',
            'email' => 'correo electrónico',
            'telefono' => 'teléfono',
            'deporte_id' => 'deporte',
            'administrador_id' => 'administrador',
            'activo' => 'estado activo',
            'logo' => 'logo del equipo',
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
                'message' => 'No tienes permisos para gestionar este equipo',
                'errors' => ['authorization' => ['Solo los administradores pueden crear o editar equipos.']],
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
                'message' => 'Errores de validación en el equipo',
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
            // Validar que el administrador no tenga demasiados equipos
            if ($this->filled('administrador_id')) {
                $equiposDelAdmin = \App\Models\Equipo::where('administrador_id', $this->administrador_id)
                    ->when($this->route('equipo'), function ($query) {
                        return $query->where('id', '!=', $this->route('equipo'));
                    })
                    ->count();

                if ($equiposDelAdmin >= 5) { // Máximo 5 equipos por administrador
                    $validator->errors()->add(
                        'administrador_id',
                        'El administrador ya gestiona el máximo de equipos permitidos (5).'
                    );
                }
            }

            // Validar nombres específicos no permitidos
            if ($this->filled('nombre')) {
                $nombresProhibidos = ['admin', 'administrador', 'sistema', 'test', 'prueba'];
                if (in_array(strtolower($this->nombre), $nombresProhibidos)) {
                    $validator->errors()->add(
                        'nombre',
                        'El nombre del equipo no puede ser: ' . implode(', ', $nombresProhibidos)
                    );
                }
            }

            // Validar que el email del equipo no sea igual al del administrador
            if ($this->filled(['email', 'administrador_id'])) {
                $administrador = \App\Models\Usuario::find($this->administrador_id);
                if ($administrador && $administrador->email === $this->email) {
                    $validator->errors()->add(
                        'email',
                        'El email del equipo no puede ser igual al email del administrador.'
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

        // Limpiar y formatear datos
        if (isset($data['nombre'])) {
            $data['nombre'] = trim($data['nombre']);
            // Capitalizar palabras importantes
            $data['nombre'] = ucwords(strtolower($data['nombre']));
        }

        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        if (isset($data['telefono'])) {
            // Limpiar teléfono manteniendo solo números, +, -, (, ), espacios
            $data['telefono'] = preg_replace('/[^\d+\-\(\)\s]/', '', $data['telefono']);
            $data['telefono'] = trim($data['telefono']);
        }

        return $data;
    }
}
