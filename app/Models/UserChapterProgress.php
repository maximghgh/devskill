<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChapterProgress extends Model
{
    protected $table = 'user_chapter_progress';
    protected $fillable = ['user_id', 'chapter_id', 'completed_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
