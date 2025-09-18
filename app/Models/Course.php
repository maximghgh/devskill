<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_title',
        'course_name',
        'price',
        'duration',
        'description',
        'hours',
        'simulators',
        'difficulty',
        'editor_data',
        'teachers',
        'language',
        'direction',
        'upgradequalification',
        'card_image',
        'description_image',
        'pdf_path',   
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'editor_data' => 'array',
        'teachers'    => 'array',
        'language' => 'array',
        'start_date'  => 'date',
        'end_date'    => 'date',
    ];

    // Отношение "многие ко многим" с преподавателями
    public function topics()
    {
        return $this->hasMany(\App\Models\Topic::class, 'course_id');
    }
    
    public function purchases()
    {
        return $this->hasMany(\App\Models\Purchase::class, 'course_id');
    }

    public function finalTest()
    {
        return $this->hasOne(FinalTest::class, 'chapter_id');
    }
}
