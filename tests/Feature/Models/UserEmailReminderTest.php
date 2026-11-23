<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserEmailReminder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserEmailReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $model = new UserEmailReminder();

        $this->assertEquals([
            'user_id',
            'type',
            'sent_at',
        ], $model->getFillable());
    }

    public function test_sent_at_is_cast_to_datetime(): void
    {
        $model = new UserEmailReminder([
            'sent_at' => '2026-11-01 12:00:00',
        ]);

        $this->assertInstanceOf(
            Carbon::class,
            $model->sent_at
        );

        $this->assertEquals(
            '2026-11-01 12:00:00',
            $model->sent_at->format('Y-m-d H:i:s')
        );
    }

    public function test_sent_at_can_be_null(): void
    {
        $model = new UserEmailReminder();

        $this->assertNull($model->sent_at);
    }

    public function test_user_relationship(): void
    {
        $model = new UserEmailReminder();

        $this->assertInstanceOf(
            BelongsTo::class,
            $model->user()
        );

        $this->assertInstanceOf(
            User::class,
            $model->user()->getRelated()
        );

        $this->assertEquals(
            'user_id',
            $model->user()->getForeignKeyName()
        );
    }

    public function test_it_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $reminder = UserEmailReminder::query()->create([
            'user_id' => $user->id,
            'type' => 'monthly',
            'sent_at' => now(),
        ]);

        $this->assertTrue($reminder->user->is($user));
    }

    public function test_it_can_be_created(): void
    {
        $user = User::factory()->create();

        $reminder = UserEmailReminder::query()->create([
            'user_id' => $user->id,
            'type' => 'monthly',
            'sent_at' => now(),
        ]);

        $this->assertDatabaseHas('user_email_reminders', [
            'id' => $reminder->id,
            'user_id' => $user->id,
            'type' => 'monthly',
        ]);
    }
}
