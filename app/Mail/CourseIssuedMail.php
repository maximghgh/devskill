<?php

namespace App\Mail;

use App\Models\CourseApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CourseIssuedMail extends Mailable
{
    use Queueable, SerializesModels;

    public CourseApplication $application;

    public function __construct(CourseApplication $application)
    {
        $this->application = $application;
    }

    public function build()
    {
        $course = $this->application->course;

        return $this->subject('Курс открыт')
            ->view('emails.course-issued')
            ->with([
                'name' => $this->application->full_name,
                'courseTitle' => $course?->card_title ?: $course?->course_name,
            ]);
    }
}
