<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_ledger_entries', function (Blueprint $table) {
            $table->bigIncrements('ledger_entry_id');

            // Polymorphic owner: ServiceProvider or TourGuide. No FK here —
            // polymorphic keys cannot be enforced at the database level, and
            // ownership is enforced at the application/policy layer.
            $table->string('payable_type');
            $table->unsignedBigInteger('payable_id');

            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();

            $table->string('entry_type', 32);
            // Signed DECIMAL: earning = +gross, commission = -deduction.
            // Balance is always SUM(amount); nothing else is stored.
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('ETB');
            $table->string('description')->nullable();

            // Append-only: no updated_at, no modification endpoints.
            $table->timestamp('created_at');

            // Financial history must survive source-row deletion.
            $table->foreign('booking_id')->references('booking_id')->on('bookings')->nullOnDelete();
            $table->foreign('payment_id')->references('payment_id')->on('payments')->nullOnDelete();

            // Idempotency: one payment produces at most one entry per type,
            // enforced at the database level (race-safe).
            $table->unique(['payment_id', 'entry_type']);
            $table->index(['payable_type', 'payable_id']);
            $table->index('entry_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_ledger_entries');
    }
};
