<?php

/**
 * REQUEST: LOGIN
 * 
 * Comando para crear: php artisan make:request LoginRequest
 * Archivo: app/Http/Requests/LoginRequest.php
 * 
 * Validaciones para el proceso de login
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Todos pueden intentar hacer login
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'exists:usuarios,email'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255'
            ],
            'remember' => [
                'sometimes',
                'boolean'
            ],
            'device_name' => [
                'sometimes',
                'string',
                'max:255'
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
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.exists' => 'No existe una cuenta con este email.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La contraseña no puede exceder 255 caracteres.',
            'remember.boolean' => 'El campo recordar debe ser verdadero o falso.',
            'device_name.string' => 'El nombre del dispositivo debe ser texto.',
            'device_name.max' => 'El nombre del dispositivo no puede exceder 255 caracteres.',
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
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'remember' => 'recordar sesión',
            'device_name' => 'nombre del dispositivo',
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
                'message' => 'Errores de validación en el login',
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
            // Validaciones adicionales personalizadas
            if ($this->filled('email')) {
                $user = \App\Models\Usuario::where('email', $this->email)->first();

                if ($user && !$user->activo) {
                    $validator->errors()->add('email', 'Tu cuenta está desactivada. Contacta al administrador.');
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

        // Limpiar email
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        // Establecer device_name por defecto
        if (!isset($data['device_name'])) {
            $data['device_name'] = 'web_browser';
        }

        // Establecer remember por defecto
        if (!isset($data['remember'])) {
            $data['remember'] = false;
        }

        return $data;
    }
}
