<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_packages', function (Blueprint $table) {
            $table->bigIncrements('package_id');
            $table->unsignedBigInteger('guide_id');
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->string('title');
            $table->string('slug')->index();
            $table->unsignedInteger('duration_days')->default(1);
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('max_group_size')->default(10);
            $table->string('difficulty_level')->default('moderate'); // easy, moderate, challenging
            $table->longText('description')->nullable();
            $table->json('itinerary')->nullable();
            $table->json('included')->nullable();
            $table->json('excluded')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('guide_id')->references('guide_id')->on('tour_guides')->cascadeOnDelete();
            $table->foreign('destination_id')->references('destination_id')->on('destinations')->nullOnDelete();
            $table->index(['guide_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_packages');
    }
};
