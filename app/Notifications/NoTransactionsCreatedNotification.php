<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoTransactionsCreatedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Set Up Your Budget in Just One Month')
            ->greeting("Hi {$notifiable->name},")
            ->line('You\'ve created your categories — now it\'s time to bring your budget to life.')
            ->line('Start by adding just one month of income and expenses. For bills and expenses that repeat, simply choose how often they recur.')
            ->line('When you\'re ready for the next month, we\'ll carry your recurring transactions forward automatically — no need to enter them all again.')
            ->action('Add My First Transaction', url('/dashboard/transactions'))
            ->line('Set up one month and you\'ll have the foundation for your budget going forward.');
    }
}
