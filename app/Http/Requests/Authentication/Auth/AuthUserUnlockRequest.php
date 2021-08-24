<?php

namespace App\Http\Requests\Authentication\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Authentication\AuthenticationFormRequest;

class AuthUserUnlockRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username' => 'required'
        ];
    }

    public function messages()
    {
        $messages = [
            'username.required' => 'El campo :attribute es obligatorio',
        ];
        return AuthenticationFormRequest::messages($messages);
    }

    public function attributes()
    {
        $attributes = [
            'username' => 'username',
        ];
        return AuthenticationFormRequest::attributes($attributes);
    }
}
