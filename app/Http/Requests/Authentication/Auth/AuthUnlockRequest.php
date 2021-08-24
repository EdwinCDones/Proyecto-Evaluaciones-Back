<?php

namespace App\Http\Requests\Authentication\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Authentication\AuthenticationFormRequest;

class AuthUnlockRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'token' => [
                'required'
            ],
            'password' => [
                'required',
                'min:6',
                'max:50'
            ],
            'password_confirmation' => [
                'required',
                'same:password'
            ],
        ];
    }

    public function messages()
    {
        $messages = [
            'token.required' => 'El campo :attribute es obligatorio',
            'password.required' => 'El campo :attribute es obligatorio',
            'password.min' => 'El campo :attribute debe tener al menos :min caracteres',
            'password.max' => 'El campo :attribute debe tener maximo :max caracteres',
            'password_confirmation.required' => 'El campo :attribute es obligatorio',
            'password_confirmation.same' => 'El campo :attribute no coincide',
        ];
        return AuthenticationFormRequest::messages($messages);
    }

    public function attributes()
    {
        $attributes = [
            'token' => 'token',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
        ];
        return AuthenticationFormRequest::attributes($attributes);
    }
}
