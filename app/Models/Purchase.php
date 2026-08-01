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
        'receipt_path',
        'status'
    ];

    protected $appends = ['receipt_url'];

    /**
     * Публичная ссылка на загруженный чек (или null, если не прикреплён).
     */
    public function getReceiptUrlAttribute(): ?string
    {
        if (empty($this->receipt_path)) {
            return null;
        }

        return str_starts_with($this->receipt_path, 'http')
            ? $this->receipt_path
            : '/' . ltrim($this->receipt_path, '/');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function course() {
        // return $this->belongsTo(Course::class);
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }
}
