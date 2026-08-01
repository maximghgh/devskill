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
        'parent_id',
        'phone',
        'country',
        'password',
        'photo',
        'inn',
        'position',
        'parent_info',
        'student_info',
        'schedule',
    ];

    protected $casts = [
        'parent_info' => 'array',
        'student_info' => 'array',
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

    // Родитель этого ученика (role 4)
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // Дети этого родителя (role 1)
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /**
     * Задолженность ученика: сумма цен курсов по неоплаченным покупкам.
     */
    public function getDebtAttribute(): float
    {
        return (float) $this->purchases()
            ->where('purchases.status', '!=', 'completed')
            ->join('courses', 'purchases.course_id', '=', 'courses.id')
            ->sum('courses.price');
    }
}
