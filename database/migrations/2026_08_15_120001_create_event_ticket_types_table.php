<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_ticket_types', function (Blueprint $table): void {
            $table->bigIncrements('ticket_type_id');
            $table->unsignedBigInteger('event_id');
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->unique(['event_id', 'name']);
            $table->index(['event_id', 'status']);
            $table->foreign('event_id')->references('event_id')->on('cultural_events')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_ticket_types');
    }
};
