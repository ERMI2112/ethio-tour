<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_guide_reservations', function (Blueprint $table) {
            $table->bigIncrements('guide_reservation_id');
            $table->unsignedBigInteger('booking_id')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('number_of_tourists');
            $table->timestamps();

            $table->index(['start_date', 'end_date'], 'tour_guide_reservation_dates_idx');
            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->cascadeOnDelete();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE tour_guide_reservations ADD CONSTRAINT tour_guide_reservations_date_check CHECK (end_date > start_date)');
            DB::statement('ALTER TABLE tour_guide_reservations ADD CONSTRAINT tour_guide_reservations_party_size_check CHECK (number_of_tourists > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_guide_reservations');
    }
};
