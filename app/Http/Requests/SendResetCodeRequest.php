<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendResetCodeRequest extends FormRequest
{
    public function authorize()
    {
        return true; // или добавить логику авторизации
    }

    public function rules()
    {
        return [
            'login' => 'required|email',
        ];
    }
}
