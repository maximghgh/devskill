<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Отзыв преподавателя об ученике в рамках курса (виден родителю в табеле).
 */
class StudentReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'teacher_id',
        'review',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
