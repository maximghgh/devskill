<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseQrCode extends Model
{
    use HasFactory;

    protected $table = 'course_qr_codes';

    protected $fillable = [
        'course_id',
        'qr_code_id',
    ];

    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class);
    }

    public function qrCode()
    {
        return $this->belongsTo(\App\Models\QrCode::class, 'qr_code_id');
    }
}
