<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $verifyUrl;
    public $name;

    /**
     * Создание нового экземпляра письма.
     */
    public function __construct($verifyUrl, $name)
    {
        $this->verifyUrl = $verifyUrl;
        $this->name = $name;
    }

    /**
     * Построение письма.
     */
    public function build()
    {
        return $this->subject('Подтверждение email')
            ->view('emails.verify-email')
            ->with([
                'verifyUrl' => $this->verifyUrl,
                'name' => $this->name,
            ]);
    }
}
