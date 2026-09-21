<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoCategoriesCreatedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Budget Is Just One Step Away')
            ->greeting("Hi {$notifiable->name},")
            ->line('It looks like you haven\'t created any categories yet. Let\'s get your budget set up!')
            ->line('We\'ve made it easy to get started. Choose from our ready-made categories for things like groceries, utilities, housing, transportation, entertainment, and more — and create them all at once.')
            ->line('It only takes a minute, and you can customize your categories anytime.')
            ->action('Create My Categories', url('/dashboard/categories'))
            ->line('Once your categories are ready, you can start adding transactions and see where your money is going.');
    }
}
