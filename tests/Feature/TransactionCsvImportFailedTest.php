<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\TransactionCsvImportFailed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class TransactionCsvImportFailedTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_the_csv_import_failure_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Walter Test',
            'email' => 'walter@example.com',
        ]);

        $filePath = storage_path('app/test-import.csv');

        file_put_contents(
            $filePath,
            "Date,Description,Amount\n10/01/2026,Test Transaction,-25.00\n"
        );

        $exception = new RuntimeException(
            'Unable to identify a transaction amount column.'
        );

        $notification = new TransactionCsvImportFailed(
            user: $user,
            exception: $exception,
            filePath: $filePath,
            originalFileName: 'test-import.csv',
        );

        $mail = $notification->toMail(
            new \Illuminate\Notifications\AnonymousNotifiable()
        );

        $this->assertSame(
            'Mintly CSV Import Failed',
            $mail->subject
        );

        $this->assertStringContainsString(
            'A transaction CSV import failed.',
            implode(' ', $mail->introLines)
        );

        $this->assertStringContainsString(
            "User ID: {$user->id}",
            implode(' ', $mail->introLines)
        );

        $this->assertStringContainsString(
            'Name: Walter Test',
            implode(' ', $mail->introLines)
        );

        $this->assertStringContainsString(
            'Email: walter@example.com',
            implode(' ', $mail->introLines)
        );

        $this->assertStringContainsString(
            'File: test-import.csv',
            implode(' ', $mail->introLines)
        );

        $this->assertStringContainsString(
            'Error: Unable to identify a transaction amount column.',
            implode(' ', $mail->introLines)
        );

        $this->assertNotEmpty($mail->attachments);

        unlink($filePath);
    }
}
