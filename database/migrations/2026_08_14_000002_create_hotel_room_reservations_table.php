<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_room_reservations', function (Blueprint $table) {
            $table->bigIncrements('hotel_reservation_id');
            $table->unsignedBigInteger('booking_id')->unique();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedTinyInteger('guest_count');
            $table->timestamps();

            $table->index(['room_id', 'check_in_date', 'check_out_date'], 'hotel_room_reservation_dates_idx');
            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->cascadeOnDelete();
            $table->foreign('room_id')
                ->references('room_id')
                ->on('hotel_rooms')
                ->restrictOnDelete();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE hotel_room_reservations ADD CONSTRAINT hotel_room_reservations_date_check CHECK (check_out_date > check_in_date)');
            DB::statement('ALTER TABLE hotel_room_reservations ADD CONSTRAINT hotel_room_reservations_guest_count_check CHECK (guest_count > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_room_reservations');
    }
};
