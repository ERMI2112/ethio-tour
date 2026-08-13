<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('category_id');
            $table->string('category_name')->unique();
            $table->timestamps();
        });

        Schema::create('destinations', function (Blueprint $table) {
            $table->bigIncrements('destination_id');
            $table->unsignedBigInteger('officer_id');
            $table->string('name');
            $table->string('location');
            $table->text('description');
            $table->timestamps();
            $table->index('officer_id');
            $table->foreign('officer_id')->references('officer_id')->on('tourism_bureau_officers')->restrictOnDelete();
        });

        Schema::create('tourism_services', function (Blueprint $table) {
            $table->bigIncrements('service_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('destination_id');
            $table->string('service_name');
            $table->decimal('price', 10, 2);
            $table->text('description');
            $table->timestamps();
            $table->index(['provider_id', 'category_id', 'destination_id']);
            $table->foreign('provider_id')->references('provider_id')->on('service_providers')->restrictOnDelete();
            $table->foreign('category_id')->references('category_id')->on('categories')->restrictOnDelete();
            $table->foreign('destination_id')->references('destination_id')->on('destinations')->restrictOnDelete();
        });

        Schema::create('heritage_sites', function (Blueprint $table) {
            $table->bigIncrements('heritage_id');
            $table->unsignedBigInteger('destination_id');
            $table->string('heritage_type');
            $table->string('opening_hours');
            $table->decimal('entrance_fee', 10, 2);
            $table->timestamps();
            $table->index('destination_id');
            $table->foreign('destination_id')->references('destination_id')->on('destinations')->cascadeOnDelete();
        });

        Schema::create('museum_information', function (Blueprint $table) {
            $table->bigIncrements('museum_id');
            $table->unsignedBigInteger('officer_id');
            $table->string('museum_name');
            $table->text('description');
            $table->string('location');
            $table->string('opening_hours');
            $table->decimal('entrance_fee', 10, 2)->nullable();
            $table->string('contact_information')->nullable();
            $table->string('media_path')->nullable();
            $table->timestamps();
            $table->index('officer_id');
            $table->foreign('officer_id')->references('officer_id')->on('tourism_bureau_officers')->restrictOnDelete();
        });

        Schema::create('cultural_events', function (Blueprint $table) {
            $table->bigIncrements('event_id');
            $table->unsignedBigInteger('destination_id');
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->string('event_name');
            $table->date('event_date');
            $table->string('venue');
            $table->timestamps();
            $table->index(['destination_id', 'event_date']);
            $table->index('provider_id');
            $table->foreign('destination_id')->references('destination_id')->on('destinations')->cascadeOnDelete();
            $table->foreign('provider_id')->references('provider_id')->on('service_providers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cultural_events');
        Schema::dropIfExists('museum_information');
        Schema::dropIfExists('heritage_sites');
        Schema::dropIfExists('tourism_services');
        Schema::dropIfExists('destinations');
        Schema::dropIfExists('categories');
    }
};
