<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_reservations', function (Blueprint $table): void {
            $table->bigIncrements('restaurant_reservation_id');
            $table->unsignedBigInteger('booking_id')->unique();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('guest_count');
            $table->timestamps();

            $table->index(['table_id', 'reservation_date', 'start_time', 'end_time'], 'restaurant_reservation_window_idx');
            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->cascadeOnDelete();
            $table->foreign('table_id')
                ->references('table_id')
                ->on('restaurant_tables')
                ->restrictOnDelete();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE restaurant_reservations ADD CONSTRAINT restaurant_reservations_time_check CHECK (end_time > start_time)');
            DB::statement('ALTER TABLE restaurant_reservations ADD CONSTRAINT restaurant_reservations_guest_count_check CHECK (guest_count > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_reservations');
    }
};
