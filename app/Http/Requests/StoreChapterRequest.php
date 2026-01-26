<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChapterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'          => 'required|string|max:255',
            'type'           => 'required|in:video,text,task,terms,presentation',
            'content'        => 'nullable',
            'correct_answer' => 'nullable|string',
            'file'           => 'nullable|file|max:20480',
            'files'          => 'nullable|array',
            'files.*'        => 'file|max:20480',
            'video_url'      => 'nullable|string',
            'points'         => 'nullable|integer|min:0',
        ];
    }
}
