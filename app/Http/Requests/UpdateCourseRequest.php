<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
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
            'duration'             => 'nullable|string',
            'description'          => 'nullable|string',
            'hours'                => 'nullable|integer',
            'simulators'           => 'nullable|integer',
            'difficulty'           => 'required|string',
            'teachers'             => 'nullable|json',
            'language'             => 'nullable|json',
            'selectedDirection'    => 'nullable|integer',
            'upgradequalification' => 'required|in:0,1',
            'cardImage'            => 'nullable|file|image|max:5120',
            'descriptionImage'     => 'nullable|image|max:2048',
            'editorData'           => 'required',
            'pdf'                  => 'nullable|file|mimes:pdf|max:20480',
            'start_date'           => 'nullable|date',
            'end_date'             => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
