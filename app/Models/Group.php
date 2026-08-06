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

    /**
     * Темы курса, открытые этой группе. Набор задаёт преподаватель
     * на странице курса — у разных групп он может отличаться.
     */
    public function topics()
    {
        return $this->belongsToMany(Topic::class, 'group_topics', 'group_id', 'topic_id')
            ->withTimestamps();
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'group_students', 'group_id', 'user_id')
            ->withTimestamps();
    }
}
