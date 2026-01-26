<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChapterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'          => 'required|string|max:255',
            'type'           => 'required|string|in:video,text,task,terms,presentation',
            'content'        => 'nullable',
            'video_url'      => 'nullable|string',
            'correct_answer' => 'nullable|string',
            'points'         => 'nullable|integer|min:0',
            'file'           => 'nullable|file|max:20480',
            'files'          => 'nullable|array',
            'files.*'        => 'file|max:20480',
            'retain_files'   => 'nullable',
        ];
    }
}
