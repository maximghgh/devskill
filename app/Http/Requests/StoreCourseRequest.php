<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'cardTitle'            => 'nullable|string|max:255',
            'courseName'           => 'required|string|max:255',
            'price'                => 'required|numeric',
            'duration'             => 'nullable|string|max:255',
            'description'          => 'nullable|string',
            'hours'                => 'required|integer',
            'simulators'           => 'nullable|integer|min:0',
            'difficulty'           => 'required|string',
            'editorData'           => 'required',
            'teachers'             => 'nullable|json',
            'language'             => 'required|string',
            'direction'            => 'nullable|string',
            'upgradequalification' => 'required|in:0,1',
            'cardImage'            => 'nullable|file|image|max:5120',
            'descriptionImage'     => 'nullable|file|image|max:5120',
            'pdf'                  => 'nullable|file|mimes:pdf|max:20480',
            'start_date'           => 'nullable|date',
            'end_date'             => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
