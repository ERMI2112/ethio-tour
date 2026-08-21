<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attractions', function (Blueprint $table) {
            $table->bigIncrements('attraction_id');
            $table->unsignedBigInteger('destination_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('description');
            $table->string('category'); // heritage_site, church, museum, natural_site, market, palace, monument
            $table->json('images')->nullable(); // [{path, alt, attribution, is_primary}]
            $table->string('location_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('opening_hours')->nullable();
            $table->decimal('entry_fee', 10, 2)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['destination_id', 'category']);
            $table->foreign('destination_id')->references('destination_id')->on('destinations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attractions');
    }
};
