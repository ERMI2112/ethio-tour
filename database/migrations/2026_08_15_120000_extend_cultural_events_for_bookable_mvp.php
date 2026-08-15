<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cultural_events', function (Blueprint $table): void {
            $table->unsignedBigInteger('service_id')->nullable()->after('provider_id');
            $table->text('description')->nullable()->after('event_name');
            $table->time('start_time')->nullable()->after('event_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->enum('status', ['draft', 'published', 'cancelled'])->default('draft')->after('end_time');
            $table->index(['provider_id', 'status']);
            $table->unique('service_id');
            $table->foreign('service_id')->references('service_id')->on('tourism_services')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cultural_events', function (Blueprint $table): void {
            $table->dropForeign(['service_id']);
            $table->dropUnique(['service_id']);
            $table->dropIndex(['provider_id', 'status']);
            $table->dropColumn(['service_id', 'description', 'start_time', 'end_time', 'status']);
        });
    }
};
