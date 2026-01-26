<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'editor_data' => 'nullable',
            'image'       => 'nullable|image|max:102400',
        ];
    }
}
