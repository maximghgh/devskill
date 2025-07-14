<?php 

// app/Models/FinalTest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinalTest extends Model
{
    protected $table = 'final_tests';

    protected $casts = [
        'questions' => 'object',   // JSON → массив
    ];

    // FinalTest → Course  (ключ column = chapter_id)
    public function course()
    {
        return $this->belongsTo(Course::class, 'chapter_id');
    }
}


?>