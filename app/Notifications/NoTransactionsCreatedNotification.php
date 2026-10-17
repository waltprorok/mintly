<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NoTransactionsCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Set Up Your Budget in Minutes with Mintly')
            ->greeting("Hi {$notifiable->name},")
            ->line('You\'ve created your categories — now it\'s time to bring your budget to life.')
            ->line('Getting started is easy! Import a CSV file from your bank or credit card to quickly add your income and expenses, or enter transactions manually.')
            ->line('We recommend starting with just one month of transactions. Mintly automatically checks for duplicates during CSV imports, making it easy to add missing transactions without starting over.')
            ->line('Start with just one month to build your budget, or import a year or more of transactions to explore your financial history and discover spending trends.')
            ->line('Mintly automatically checks for duplicate transactions during CSV imports, making it easy to add more transactions later.')
            ->line('For bills and expenses that repeat, simply choose how often they recur. Mintly will automatically carry those transactions forward each month.')
            ->action('Manage My Transactions', url('/dashboard/transactions'))
            ->line('Import one month, set up your recurring transactions, and you\'ll have the foundation for your budget going forward.');
    }
}
