<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->string('permit_number')->nullable()->after('trade_license_number');
            $table->string('secondary_contact_name')->nullable()->after('manager_title');
            $table->string('secondary_contact_title')->nullable()->after('secondary_contact_name');
            $table->integer('capacity_count')->nullable()->after('total_rooms_count');
            $table->string('operating_hours')->nullable()->after('check_out_time');
            $table->json('specialties')->nullable()->after('amenities');
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn([
                'permit_number',
                'secondary_contact_name',
                'secondary_contact_title',
                'capacity_count',
                'operating_hours',
                'specialties',
            ]);
        });
    }
};
