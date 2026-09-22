<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendNewsletterCommand extends Command
{
    protected $signature = 'newsletter:send {--test : Send only to the test email address}';

    protected $description = 'Send the active Mintly Budget newsletter through Mailgun';

    public function handle(): int
    {
        $domain = config('services.mailgun.domain');
        $secret = config('services.mailgun.secret');
        $template = config('services.mailgun.newsletter_template');
        $mailingList = config('services.mailgun.newsletter_list');
        $testEmail = config('services.mailgun.newsletter_test_email');

        if (! $domain || ! $secret || ! $template || ! $mailingList) {
            $this->error('Mailgun newsletter configuration is incomplete.');

            return self::FAILURE;
        }

        $recipient = $this->option('test')
            ? $testEmail
            : $mailingList;

        if ($this->option('test') && ! $testEmail) {
            $this->error('MAILGUN_NEWSLETTER_TEST_EMAIL is not configured.');

            return self::FAILURE;
        }

        $this->info('Newsletter Template: '.$template);
        $this->info('Recipient: '.$recipient);

        if (! $this->option('test')) {
            if (! $this->confirm(
                "Send the newsletter to {$mailingList}?"
            )) {
                $this->warn('Newsletter send cancelled.');

                return self::SUCCESS;
            }
        }

        $response = Http::withBasicAuth('api', $secret)
            ->asMultipart()
            ->post(
                "https://api.mailgun.net/v3/{$domain}/messages",
                [
                    [
                        'name' => 'from',
                        'contents' => config('mail.from.name')
                            .' <'.config('mail.from.address').'>',
                    ],
                    [
                        'name' => 'to',
                        'contents' => $recipient,
                    ],
                    [
                        'name' => 'template',
                        'contents' => $template,
                    ],
                ]
            );

        if ($response->failed()) {
            $this->error('Mailgun failed to send the newsletter.');

            $this->error(
                $response->body()
            );

            return self::FAILURE;
        }

        $this->info(
            $this->option('test')
                ? 'Test newsletter sent successfully.'
                : 'Newsletter sent successfully.'
        );

        return self::SUCCESS;
    }
}
