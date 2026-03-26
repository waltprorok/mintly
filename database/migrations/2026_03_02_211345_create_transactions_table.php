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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['income', 'expense'])->index();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->dateTime('due_at')->nullable()->index();
            $table->string('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('merchant')->nullable();
            $table->string('payment_method')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_rule')->nullable();
            $table->boolean('is_paid')->default(false)->index();
            $table->timestamps();
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
