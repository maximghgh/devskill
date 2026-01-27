<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_group',
        'course_id',
        'students_count',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'group_students', 'group_id', 'user_id')
            ->withTimestamps();
    }
}
