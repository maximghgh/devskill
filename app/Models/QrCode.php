<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'qr_image',
        'link',
    ];

    public function courses()
    {
        return $this->belongsToMany(\App\Models\Course::class, 'course_qr_codes')
            ->withTimestamps();
    }
}
