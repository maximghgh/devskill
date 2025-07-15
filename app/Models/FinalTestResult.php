<?php

// app/Models/FinalTestResult.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinalTestResult extends Model
{
    protected $table = 'final_test_results';
    protected $fillable = [
        'user_id',
        'course_id',          // или final_test_id
        'answers',
        'score',
        'correct_count',
        'total_questions',
    ];

    protected $casts = [
        'answers' => 'array', // JSON → array автоматически
    ];

    /* связи (по желанию) */
    public function user()   { return $this->belongsTo(User::class); }
    public function course() { return $this->belongsTo(Course::class); }
}

