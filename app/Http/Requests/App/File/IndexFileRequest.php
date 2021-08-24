<?php

namespace App\Http\Requests\App\File;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\App\AppFormRequest;

class IndexFileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'id' => [
                'required',
                'integer',
            ],
        ];
        return AppFormRequest::rules($rules);
    }

    public function messages()
    {
        $messages = [
            'id.required' => 'El campo :attribute debe ser un número',
            'id.integer' => 'El campo :attribute es obligatorio',
        ];
        return AppFormRequest::messages($messages);
    }

    public function attributes()
    {
        $attributes = [
            'id' => 'ID',
        ];
        return AppFormRequest::attributes($attributes);
    }
}
