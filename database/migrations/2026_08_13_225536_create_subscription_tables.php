<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->bigIncrements('plan_id');
            $table->string('plan')->unique();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('commission_rate', 5, 2);
            $table->unsignedInteger('duration')->comment('Duration in days.');
            $table->timestamps();
        });

        Schema::create('provider_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('provider_subscription_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('plan_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status');
            $table->timestamps();
            $table->index(['provider_id', 'status']);
            $table->index('plan_id');
            $table->foreign('provider_id')->references('provider_id')->on('service_providers')->restrictOnDelete();
            $table->foreign('plan_id')->references('plan_id')->on('subscription_plans')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
