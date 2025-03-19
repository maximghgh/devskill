<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    /**
     * Атрибуты, разрешённые для массового заполнения.
     */
    protected $fillable = [
        'course_id',  // ID курса, к которому принадлежит тема
        'title',      // Название темы
        'description',// Описание темы (опционально)
        'order',      // Порядок сортировки тем (если используется)
    ];

    /**
     * Определяет связь «Принадлежит» с моделью Course.
     * Каждый объект Topic принадлежит одному курсу.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }

    /**
     * Определяет связь «Один ко многим» с моделью Chapter.
     * Тема может иметь много глав.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('order');
    }
}
