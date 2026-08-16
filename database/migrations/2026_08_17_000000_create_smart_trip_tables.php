<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table): void {
            $table->bigIncrements('trip_id');
            $table->unsignedBigInteger('user_id');
            $table->string('title', 120);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('draft');
            $table->json('preferences')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });

        Schema::create('trip_destinations', function (Blueprint $table): void {
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('destination_id');
            $table->primary(['trip_id', 'destination_id']);
            $table->foreign('trip_id')->references('trip_id')->on('trips')->cascadeOnDelete();
            $table->foreign('destination_id')->references('destination_id')->on('destinations')->restrictOnDelete();
        });

        Schema::create('trip_items', function (Blueprint $table): void {
            $table->bigIncrements('trip_item_id');
            $table->unsignedBigInteger('trip_id');
            $table->string('item_type', 40);
            $table->unsignedBigInteger('item_id');
            $table->date('planned_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedInteger('sequence')->default(0);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('planned');
            $table->string('source', 20)->default('suggested');
            $table->timestamps();
            $table->unique(['trip_id', 'item_type', 'item_id']);
            $table->index(['trip_id', 'planned_date', 'sequence']);
            $table->index(['item_type', 'item_id']);
            $table->foreign('trip_id')->references('trip_id')->on('trips')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_items');
        Schema::dropIfExists('trip_destinations');
        Schema::dropIfExists('trips');
    }
};
