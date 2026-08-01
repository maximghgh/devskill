<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Явный whitelist полей пользователя.
     *
     * Чувствительные поля НЕ отдаём:
     *  - inn (ИНН) — нигде на фронте не используется;
     *  - email_verified_at, remember_token, updated_at, schedule — служебные.
     *
     * parent_info / student_info (персональные данные) отдаём только когда
     * они реально загружены в модель — т.е. в ответе на запрос собственного
     * профиля, а не в общих списках пользователей.
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'login'        => $this->login,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'country'      => $this->country,
            'role'         => $this->role,
            'parent_id'    => $this->parent_id,
            'birthday'     => $this->birthday,
            'position'     => $this->position,
            'photo'        => $this->photo,
            'created_at'   => $this->created_at,
            'parent_info'  => $this->whenHas('parent_info'),
            'student_info' => $this->whenHas('student_info'),
        ];
    }
}
