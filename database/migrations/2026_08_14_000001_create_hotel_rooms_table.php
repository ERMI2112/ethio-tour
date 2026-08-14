<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->bigIncrements('room_id');
            $table->unsignedBigInteger('room_type_id');
            $table->string('room_number');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['room_type_id', 'room_number']);
            $table->index(['room_type_id', 'status']);
            $table->foreign('room_type_id')
                ->references('room_type_id')
                ->on('hotel_room_types')
                ->restrictOnDelete();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE hotel_rooms ADD CONSTRAINT hotel_rooms_room_number_check CHECK (CHAR_LENGTH(TRIM(room_number)) > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_rooms');
    }
};
