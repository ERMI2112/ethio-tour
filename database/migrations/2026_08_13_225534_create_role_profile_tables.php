<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tourists', function (Blueprint $table) {
            $table->bigIncrements('tourist_id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('full_name');
            $table->string('nationality');
            $table->timestamps();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });

        Schema::create('tour_guides', function (Blueprint $table) {
            $table->bigIncrements('guide_id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('license_number')->unique();
            $table->text('expertise');
            $table->string('availability_status');
            $table->timestamps();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });

        Schema::create('service_providers', function (Blueprint $table) {
            $table->bigIncrements('provider_id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('business_name');
            $table->enum('provider_type', ['hotel', 'restaurant', 'transportation_car_rental', 'event_organizer']);
            $table->string('status');
            $table->timestamps();
            $table->index('provider_type');
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });

        Schema::create('tourism_bureau_officers', function (Blueprint $table) {
            $table->bigIncrements('officer_id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->timestamps();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });

        Schema::create('administrators', function (Blueprint $table) {
            $table->bigIncrements('admin_id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->timestamps();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrators');
        Schema::dropIfExists('tourism_bureau_officers');
        Schema::dropIfExists('service_providers');
        Schema::dropIfExists('tour_guides');
        Schema::dropIfExists('tourists');
    }
};
