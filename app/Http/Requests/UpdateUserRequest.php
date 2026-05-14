<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->route('id');

        return [
            'name'     => 'nullable|string|max:255',
            'login'    => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'login')->ignore($userId),
            ],
            'email'    => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone'    => 'nullable|string|max:50',
            'birthday' => 'nullable|date',
            'country'  => 'nullable|string|max:100',
            'role'     => 'nullable|in:1,2,3,4',
            'position' => 'nullable|string|max:255',
        ];
    }
}
