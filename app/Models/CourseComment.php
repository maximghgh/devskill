<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CourseComment extends Model
{
    protected $fillable = [
        'course_id',
        'user_id',
        'body',
        'parent_id',
        'user_name',
        'likes',
        'dislikes'
    ];

    // Комментарий принадлежит курсу
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Комментарий принадлежит пользователю (если есть)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Дочерние комментарии (ответы)
    public function children()
    {
        return $this->hasMany(CourseComment::class, 'parent_id');
    }

    // Родительский комментарий
    public function parent()
    {
        return $this->belongsTo(CourseComment::class, 'parent_id');
    }

}
