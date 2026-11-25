<?php

namespace App\Services;

use App\Mail\CourseIssuedMail;
use App\Models\CourseApplication;
use App\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CourseAccessService
{
    public function openCourseAccess(CourseApplication $application): void
    {
        Purchase::firstOrCreate(
            [
                'user_id' => $application->user_id,
                'course_id' => $application->course_id,
            ],
            [
                'payment_method' => 'contract',
                'status' => 'completed',
            ]
        );
    }

    public function closeCourseAccess(CourseApplication $application): void
    {
        if (!$application->user_id) {
            return;
        }

        Purchase::where('user_id', $application->user_id)
            ->where('course_id', $application->course_id)
            ->delete();
    }

    public function notifyCourseIssued(CourseApplication $application): void
    {
        if (!filter_var($application->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            Mail::to($application->email)->send(new CourseIssuedMail($application));
        } catch (\Throwable $e) {
            Log::warning('Не удалось отправить письмо о выдаче курса', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
