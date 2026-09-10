<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportMessage extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array{name:string,email:string,subject:string,message:string} $data */
    public function __construct(public array $data)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Apoio ao cliente] '.$this->data['subject'],
            replyTo: [$this->data['email']],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.support', with: ['data' => $this->data]);
    }
}
