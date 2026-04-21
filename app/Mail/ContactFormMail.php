<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public $formData;
    public $subjectLine;
    public $link;

    public function __construct($formData, $subject, $link)
    {
        $this->formData    = $formData;
        $this->subjectLine = $subject;
        $this->link        = $link;
    }

    public function build()
    {
        return $this
            ->subject($this->subjectLine)
            ->replyTo($this->formData['email'], $this->formData['name'])
            ->view('layouts.contact-form-message');
    }
}
