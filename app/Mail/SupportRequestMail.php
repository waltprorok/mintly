<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Storage;

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
        $attachments = [];

        if (! empty($this->data['screenshots'])) {
            foreach ($this->data['screenshots'] as $file) {
                // Handle both string + array formats
                $path = is_array($file) ? ($file['path'] ?? null) : $file;
                if ($path && Storage::disk('public')->exists($path)) {
                    $attachments[] = Attachment::fromPath(
                        Storage::disk('public')->path($path)
                    );
                }
            }
        }

        return $attachments;
    }
}
