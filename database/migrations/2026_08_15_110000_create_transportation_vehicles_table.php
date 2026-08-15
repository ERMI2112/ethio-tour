<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transportation_vehicles', function (Blueprint $table): void {
            $table->bigIncrements('vehicle_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('service_id');
            $table->string('vehicle_identifier', 100);
            $table->string('vehicle_type', 100);
            $table->string('make', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedSmallInteger('capacity');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->unique(['provider_id', 'vehicle_identifier']);
            $table->index(['service_id', 'status']);
            $table->foreign('provider_id')->references('provider_id')->on('service_providers')->restrictOnDelete();
            $table->foreign('service_id')->references('service_id')->on('tourism_services')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transportation_vehicles');
    }
};
