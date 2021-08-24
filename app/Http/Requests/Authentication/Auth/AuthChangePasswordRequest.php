<?php

namespace App\Http\Requests\Authentication\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Authentication\AuthenticationFormRequest;

class AuthChangePasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'password_old' => [
                'required'
            ],
            'password' => [
                'required',
                'min:8',
                'max:30'
            ],
            'password_confirmation' => [
                'required',
                'same:password'
            ]
        ];
        return AuthenticationFormRequest::messages($messages);
    }

    public function messages()
    {
        $messages = [
            'password_old.required' => 'El campo :attribute es obligatorio',
            'password.required' => 'El campo :attribute es obligatorio',
            'password.min' => 'El campo :attribute debe tener al menos :min caracteres',
            'password.max' => 'El campo :attribute debe tener maximo :max caracteres',
            'password_confirmation.required' => 'El campo :attribute es obligatorio',
            'password_confirmation.same' => 'El campo :attribute coincide',
        ];
        return $messages;
    }

    public function attributes()
    {
        $attributes = [
            'password_old' => 'Password Old',
            'password' => 'Password',
            'password_confirmation' => 'Password Confirmation',

        ];
        return AuthenticationFormRequest::attributes($attributes);
    }
}
