<?php

namespace App\Http\Requests\App\File;

use App\Http\Requests\App\AppFormRequest;
use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'files.*' => [
                'required',
                'mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx,txt,zip,rar,7z,tar',
                'file',
                'max:1024000',
            ],
        ];
        return AppFormRequest::rules($rules);
    }

    public function messages()
    {
        $messages = [
            'files.*.required' => 'El campo :attribute es obligatorio.',
            'files.*.mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
            'files.*.max' => 'El campo :attribute no puede ser mayor que :maxKB.',
        ];
        return AppFormRequest::messages($messages);
    }

    public function attributes()
    {
        $attributes = [
            'files.*' => 'archivo'
        ];
        return AppFormRequest::attributes($attributes);
    }
}
