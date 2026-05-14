<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'login',
        'email',
        'birthday',
        'role',
        'phone',
        'country',
        'password',
        'photo',
        'inn',
        'position',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function purchases()
    {
        return $this->hasMany(\App\Models\Purchase::class, 'user_id');
    }
    // Пользователь может иметь много записей прогресса
    public function chapterProgress()
    {
        return $this->hasMany(UserChapterProgress::class);
    }
}
