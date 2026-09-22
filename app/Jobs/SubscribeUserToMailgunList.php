<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\MailgunMailingListService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SubscribeUserToMailgunList implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public User $user
    ) {
    }

    public function handle(MailgunMailingListService $mailgun): void
    {
        if (! app()->environment('production')) {
            return;
        }

        $mailgun->subscribe($this->user);

        Log::info('User subscribed to Mailgun mailing list', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Failed to subscribe user to Mailgun mailing list', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'message' => $exception?->getMessage(),
            'exception' => $exception,
        ]);
    }
}
