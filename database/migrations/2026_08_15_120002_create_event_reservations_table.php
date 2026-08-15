<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_reservations', function (Blueprint $table): void {
            $table->bigIncrements('event_reservation_id');
            $table->unsignedBigInteger('booking_id')->unique();
            $table->unsignedBigInteger('ticket_type_id');
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->foreign('booking_id')->references('booking_id')->on('bookings')->cascadeOnDelete();
            $table->foreign('ticket_type_id')->references('ticket_type_id')->on('event_ticket_types')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_reservations');
    }
};
