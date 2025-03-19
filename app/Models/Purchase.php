<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'payment_method',
        'payment_details',
        'status'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function course() {
        // return $this->belongsTo(Course::class);
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }
}
