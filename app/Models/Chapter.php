<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    /**
     * Атрибуты, разрешённые для массового заполнения.
     */
    protected $fillable = [
        'topic_id',
        'title',
        'type',
        'content',
        'video_url',
        'order',
        'correct_answer',
    ];

    
    protected $casts = [
        'content' => 'json', // или 'json'
    ];
    
    /**
     * Определяет связь «Принадлежит» с моделью Topic.
     * Каждая глава принадлежит одной теме.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function topic()
    {
        return $this->belongsTo(\App\Models\Topic::class, 'topic_id');
    }
    public function progress()
    {
        return $this->hasMany(UserChapterProgress::class);
    }
    
}
