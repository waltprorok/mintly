<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SupportRequestMail extends Mailable
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Support Request: ' . $this->data['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.support-request',
        );
    }

    public function attachments(): array
    {
        if (! empty($this->data['screenshot'])) {
            $path = storage_path('app/' . $this->data['screenshot']);

            if (file_exists($path)) {
                return [
                    Attachment::fromPath($path)
                        ->as('screenshot.jpg')
                        ->withMime('image/jpeg'),
                ];
            }
        }

        return [];
    }
}
