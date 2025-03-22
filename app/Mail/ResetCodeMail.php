<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code; // DTO: переменная для передачи кода в представление

    /**
     * Create a new message instance.
     *
     * @param mixed $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Ваш код для восстановления пароля')
                    ->view('emails.reset_code'); // создайте представление ниже
    }
}
