<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = ['title', 'content', 'editor_data', 'image'];
    
    // Автоматическое преобразование JSON-данных в массив
    protected $casts = [
        'editor_data' => 'array',
    ];

    // Отношение: Новость имеет много комментариев
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function children()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

}

