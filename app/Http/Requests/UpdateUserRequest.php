<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'     => 'nullable|string|max:255',
            'email'    => 'nullable|email|max:255',
            'phone'    => 'nullable|string|max:50',
            'birthday' => 'nullable|date',
            'country'  => 'nullable|string|max:100',
            'role'     => 'required|in:1,2,3',
            'position' => 'nullable|string|max:255',
        ];
    }
}
