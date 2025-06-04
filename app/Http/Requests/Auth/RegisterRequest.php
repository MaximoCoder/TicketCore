<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

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
            //
            'name' => ['required', 'min:3', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                'min:6',
                Password::min(6)->letters()->symbols()->numbers()
            ],
            'phone' => ['required', 'string', 'min:10', 'max:10'],
            'department_id' => ['required', 'exists:departments,id', 'min:1'],
        ];
    }

    // Mensajes de validacion
    public function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'name.min' => 'El nombre debe tener al menos 3 caracteres',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email no es valido',
            'email.unique' => 'El email ya esta registrado',
            'password.required' => 'El password es obligatorio',
            'password.confirmed' => 'El password no coincide',
            'password.min' => 'El password debe tener al menos 6 caracteres',
            'password.letters' => 'El password debe contener al menos una letra',
            'password.symbols' => 'El password debe contener al menos un simbolo',
            'password.numbers' => 'El password debe contener al menos un numero',
            'phone.required' => 'El telefono es obligatorio',
            'phone.string' => 'El telefono no es valido',
            'phone.min' => 'El telefono debe tener al menos 10 caracteres',
            'phone.max' => 'El telefono debe tener maximo 10 caracteres',
            'phone.unique' => 'El telefono ya esta registrado',
            'department_id.required' => 'El departamento es obligatorio',
        ];
    }
}
