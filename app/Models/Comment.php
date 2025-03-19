<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['news_id', 'parent_id', 'user_id', 'body', 'user_name'];

    // Отношение: Комментарий принадлежит новости
    public function news()
    {
        return $this->belongsTo(News::class);
    }

    // Связь: один комментарий принадлежит одному пользователю
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Родительский комментарий (если есть)
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Подкомментарии (ответы на комментарий)
    public function children()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}

