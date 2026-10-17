<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoCategoriesCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Mintly Budget Is Almost Ready')
            ->greeting("Hi {$notifiable->name},")
            ->line('You\'re just one step away from getting your budget started with Mintly!')
            ->line('Start by creating your budget categories. Choose from our ready-made categories for groceries, utilities, housing, transportation, entertainment, and more — all in just a few clicks.')
            ->line('You can create multiple categories at once and customize them anytime to fit your financial needs.')
            ->line('Once your categories are ready, you can import transactions directly from your bank or credit card using a CSV file, or add them manually.')
            ->line('From there, Mintly helps you track spending, manage recurring expenses, and understand where your money goes.')
            ->action('Create My Categories', url('/dashboard/categories'))
            ->line('It only takes a minute to get started. Your financial picture starts here!');
    }
}
