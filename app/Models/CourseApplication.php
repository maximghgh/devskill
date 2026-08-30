<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseApplication extends Model
{
    /** Заявка оставлена, админ связывается и подписывает договор. */
    public const STATUS_AWAITING_SIGNING = 'awaiting_signing';

    /** Договор подписан, человек оплачивает на сайте ИжГТУ. */
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';

    /** Оплата подтверждена, курс открыт. */
    public const STATUS_ISSUED = 'issued';

    /** Доступ временно закрыт (например, просрочена рассрочка). */
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_AWAITING_SIGNING,
        self::STATUS_AWAITING_PAYMENT,
        self::STATUS_ISSUED,
        self::STATUS_CLOSED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_AWAITING_SIGNING => 'Ожидает подписания',
        self::STATUS_AWAITING_PAYMENT => 'Договор подписан, ожидает оплаты',
        self::STATUS_ISSUED => 'Курс выдан',
        self::STATUS_CLOSED => 'Курс закрыт',
    ];

    protected $fillable = [
        'course_id',
        'user_id',
        'full_name',
        'phone',
        'email',
        'status',
        'admin_comment',
    ];

    protected $appends = ['status_label'];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
