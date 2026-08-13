<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('booking_id');
            $table->unsignedBigInteger('tourist_id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('guide_id')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'payment_pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->timestamp('booking_date')->useCurrent();
            $table->timestamps();
            $table->index(['tourist_id', 'status']);
            $table->index('service_id');
            $table->index('guide_id');
            $table->foreign('tourist_id')->references('tourist_id')->on('tourists')->restrictOnDelete();
            $table->foreign('service_id')->references('service_id')->on('tourism_services')->restrictOnDelete();
            $table->foreign('guide_id')->references('guide_id')->on('tour_guides')->restrictOnDelete();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_booking_target_check CHECK ((service_id IS NOT NULL AND guide_id IS NULL) OR (service_id IS NULL AND guide_id IS NOT NULL))');
        }

        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('payment_id');
            $table->unsignedBigInteger('booking_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('status');
            $table->string('payment_method');
            $table->string('gateway_reference')->nullable()->unique();
            $table->timestamps();
            $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('review_id');
            $table->unsignedBigInteger('booking_id')->unique();
            $table->unsignedBigInteger('tourist_id');
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->date('review_date');
            $table->timestamps();
            $table->index('tourist_id');
            $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
            $table->foreign('tourist_id')->references('tourist_id')->on('tourists')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
    }
};
