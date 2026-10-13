<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class TransactionCsvImportFailed extends Notification
{
    use Queueable;

    public function __construct(
        protected User $user,
        protected Throwable $exception,
        protected string $filePath,
        protected string $originalFileName,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Mintly CSV Import Failed')
            ->greeting('CSV Import Error')
            ->line('A transaction CSV import failed.')
            ->line("User ID: {$this->user->id}")
            ->line("Name: {$this->user->name}")
            ->line("Email: {$this->user->email}")
            ->line("File: {$this->originalFileName}")
            ->line('Error: ' . $this->exception->getMessage())
            ->line('Exception: ' . get_class($this->exception))
            ->line(
                "Location: {$this->exception->getFile()}:{$this->exception->getLine()}"
            )
            ->attach($this->filePath, [
                'as' => basename($this->originalFileName),
                'mime' => 'text/csv',
            ]);
    }
}
