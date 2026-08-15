<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transportation_reservations', function (Blueprint $table): void {
            $table->bigIncrements('transportation_reservation_id');
            $table->unsignedBigInteger('booking_id')->unique();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->dateTime('pickup_at');
            $table->dateTime('dropoff_at');
            $table->unsignedInteger('passenger_count');
            $table->timestamps();
            $table->index(['pickup_at', 'dropoff_at']);
            $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
            $table->foreign('vehicle_id')->references('vehicle_id')->on('transportation_vehicles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transportation_reservations');
    }
};
