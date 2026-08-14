<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_room_types', function (Blueprint $table) {
            $table->bigIncrements('room_type_id');
            $table->unsignedBigInteger('service_id')->unique();
            $table->unsignedTinyInteger('capacity');
            $table->json('amenities');
            $table->timestamps();

            $table->foreign('service_id')
                ->references('service_id')
                ->on('tourism_services')
                ->restrictOnDelete();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE hotel_room_types ADD CONSTRAINT hotel_room_types_capacity_check CHECK (capacity > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_room_types');
    }
};
