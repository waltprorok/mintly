<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\CategoryBudget;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserEmailReminder;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $model = new User();

        $this->assertEquals([
            'name',
            'email',
            'password',
        ], $model->getFillable());
    }

    public function test_hidden_attributes(): void
    {
        $model = new User();

        $this->assertEquals([
            'password',
            'remember_token',
        ], $model->getHidden());
    }

    public function test_email_verified_at_is_cast_to_datetime(): void
    {
        $model = new User();

        $model->email_verified_at = '2026-11-01 12:00:00';

        $this->assertInstanceOf(
            \Illuminate\Support\Carbon::class,
            $model->email_verified_at
        );

        $this->assertEquals(
            '2026-11-01 12:00:00',
            $model->email_verified_at->format('Y-m-d H:i:s')
        );
    }

    public function test_password_is_hashed(): void
    {
        $model = new User([
            'password' => 'password123',
        ]);

        $this->assertTrue(
            Hash::check('password123', $model->password)
        );
    }

    public function test_user_implements_email_verification(): void
    {
        $model = new User();

        $this->assertInstanceOf(
            MustVerifyEmail::class,
            $model
        );
    }

    public function test_user_can_access_filament_panel(): void
    {
        $model = new User();

        $panel = Panel::make();

        $this->assertTrue(
            $model->canAccessPanel($panel)
        );
    }

    public function test_categories_relationship(): void
    {
        $model = new User();

        $this->assertInstanceOf(
            HasMany::class,
            $model->categories()
        );

        $this->assertInstanceOf(
            Category::class,
            $model->categories()->getRelated()
        );

        $this->assertEquals(
            'user_id',
            $model->categories()->getForeignKeyName()
        );
    }

    public function test_category_budgets_relationship(): void
    {
        $model = new User();

        $this->assertInstanceOf(
            HasMany::class,
            $model->categoryBudgets()
        );

        $this->assertInstanceOf(
            CategoryBudget::class,
            $model->categoryBudgets()->getRelated()
        );

        $this->assertEquals(
            'user_id',
            $model->categoryBudgets()->getForeignKeyName()
        );
    }

    public function test_email_reminders_relationship(): void
    {
        $model = new User();

        $this->assertInstanceOf(
            HasMany::class,
            $model->emailReminders()
        );

        $this->assertInstanceOf(
            UserEmailReminder::class,
            $model->emailReminders()->getRelated()
        );

        $this->assertEquals(
            'user_id',
            $model->emailReminders()->getForeignKeyName()
        );
    }

    public function test_transactions_relationship(): void
    {
        $model = new User();

        $this->assertInstanceOf(
            HasMany::class,
            $model->transactions()
        );

        $this->assertInstanceOf(
            Transaction::class,
            $model->transactions()->getRelated()
        );

        $this->assertEquals(
            'user_id',
            $model->transactions()->getForeignKeyName()
        );
    }

    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function test_user_password_is_hidden_from_array(): void
    {
        $user = User::factory()->create();

        $this->assertArrayNotHasKey(
            'password',
            $user->toArray()
        );

        $this->assertArrayNotHasKey(
            'remember_token',
            $user->toArray()
        );
    }
}
