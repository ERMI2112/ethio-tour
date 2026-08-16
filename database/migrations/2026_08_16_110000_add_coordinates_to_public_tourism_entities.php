<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['destinations', 'heritage_sites', 'museum_information', 'tourism_services', 'cultural_events'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->index(['latitude', 'longitude']);
            });
        }
    }

    public function down(): void
    {
        foreach (['destinations', 'heritage_sites', 'museum_information', 'tourism_services', 'cultural_events'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropIndex(['latitude', 'longitude']);
                $table->dropColumn(['latitude', 'longitude']);
            });
        }
    }
};
