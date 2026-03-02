<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Multi-user SaaS
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Income or Expense
            $table->enum('type', ['income', 'expense'])->index();

            // Category relationship (recommended over string)
            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Store as decimal dollars (ex: 1250.50)
            $table->decimal('amount', 12, 2);

            // Due date
            $table->dateTime('due_at')->nullable()->index();

            // Description / Notes
            $table->string('description')->nullable();
            $table->text('notes')->nullable();

            // Merchant (optional)
            $table->string('merchant')->nullable();

            // Payment method (credit_card, bank, etc)
            $table->string('payment_method')->nullable();

            // Recurring
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_rule')->nullable();
            // Example: monthly, weekly, yearly, cron expression

            // Paid status
            $table->boolean('status')->default(false)->index();
            // false = unpaid, true = paid

            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
