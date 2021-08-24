<?php

namespace App\Http\Requests\App\Image;

use App\Http\Requests\JobBoard\JobBoardFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'images.*' => [
                'required',
                'mimes:jpg,jpeg,png,jpeg 2000,bmp',
                'file',
                'max:102400',
            ],
        ];
        return JobBoardFormRequest::rules($rules);
    }

    public function messages()
    {
        $messages = [
            'images.*.required' => 'El campo :attribute es obligatorio.',
            'images.*.mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
            'images.*.max' => 'El campo :attribute no puede ser mayor que :maxKB.',
        ];
        return JobBoardFormRequest::messages($messages);
    }

    public function attributes()
    {
        $attributes = [
            'images.*' => 'imagen'
        ];
        return JobBoardFormRequest::attributes($attributes);
    }
}
