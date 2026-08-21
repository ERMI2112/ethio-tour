<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_guides', function (Blueprint $table): void {
            $table->string('profile_image')->nullable()->after('license_number');
            $table->text('bio')->nullable()->after('expertise');
            $table->string('phone_number')->nullable()->after('bio');
            $table->json('languages')->nullable()->after('phone_number');
            $table->unsignedSmallInteger('years_of_experience')->nullable()->after('languages');
            $table->unsignedBigInteger('primary_destination_id')->nullable()->after('years_of_experience');
            $table->json('specialties')->nullable()->after('primary_destination_id');

            $table->foreign('primary_destination_id')
                ->references('destination_id')
                ->on('destinations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tour_guides', function (Blueprint $table): void {
            $table->dropForeign(['primary_destination_id']);
            $table->dropColumn([
                'profile_image',
                'bio',
                'phone_number',
                'languages',
                'years_of_experience',
                'primary_destination_id',
                'specialties',
            ]);
        });
    }
};
