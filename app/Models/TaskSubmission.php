<?php
// app/Models/TaskSubmission.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id','course_id','chapter_id',
        'comment','teacher_comment','file_path',
        'status','grade','teacher_id','attempt',
    ];

    protected $casts = [
        'grade' => 'integer',
    ];

    public function user()    { return $this->belongsTo(User::class); }
    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function course()  { return $this->belongsTo(\App\Models\Course::class); }
    public function chapter() { return $this->belongsTo(\App\Models\Chapter::class, 'chapter_id', 'id'); }
}


?>