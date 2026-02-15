<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'teacher_id',
        'chapter_id',
        'score',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
