<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class MailgunMailingListService
{
    public function subscribe(User $user): void
    {
        $list = config('services.mailgun.newsletter_list');

        Http::withBasicAuth('api', config('services.mailgun.secret'))
            ->asForm()
            ->post(
                'https://api.mailgun.net/v3/lists/'
                . urlencode($list)
                . '/members',
                [
                    'address' => $user->email,
                    'name' => $user->name,
                    'subscribed' => 'true',
                    'upsert' => 'yes',
                ]
            )
            ->throw();
    }
}
