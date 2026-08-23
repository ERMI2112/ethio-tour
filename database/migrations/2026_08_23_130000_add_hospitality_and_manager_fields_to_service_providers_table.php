<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->string('manager_name')->nullable()->after('business_name');
            $table->string('manager_title')->nullable()->after('manager_name');
            $table->string('manager_phone')->nullable()->after('manager_title');
            $table->string('contact_email')->nullable()->after('manager_phone');
            $table->string('tin_number')->nullable()->after('contact_email');
            $table->string('trade_license_number')->nullable()->after('tin_number');
            $table->string('star_rating')->nullable()->after('trade_license_number');
            $table->unsignedBigInteger('destination_id')->nullable()->after('star_rating');
            $table->string('physical_address')->nullable()->after('destination_id');
            $table->integer('total_rooms_count')->nullable()->after('physical_address');
            $table->string('check_in_time')->nullable()->after('total_rooms_count');
            $table->string('check_out_time')->nullable()->after('check_in_time');
            $table->json('amenities')->nullable()->after('check_out_time');
            $table->string('payout_bank_name')->nullable()->after('amenities');
            $table->string('payout_account_number')->nullable()->after('payout_bank_name');
            $table->string('payout_account_name')->nullable()->after('payout_account_number');
            $table->text('description')->nullable()->after('payout_account_name');
            $table->string('cover_image')->nullable()->after('description');
            $table->integer('application_step')->default(1)->after('cover_image');

            $table->foreign('destination_id')->references('destination_id')->on('destinations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropForeign(['destination_id']);
            $table->dropColumn([
                'manager_name',
                'manager_title',
                'manager_phone',
                'contact_email',
                'tin_number',
                'trade_license_number',
                'star_rating',
                'destination_id',
                'physical_address',
                'total_rooms_count',
                'check_in_time',
                'check_out_time',
                'amenities',
                'payout_bank_name',
                'payout_account_number',
                'payout_account_name',
                'description',
                'cover_image',
                'application_step',
            ]);
        });
    }
};
