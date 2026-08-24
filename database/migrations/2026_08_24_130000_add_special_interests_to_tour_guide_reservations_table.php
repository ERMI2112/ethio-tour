<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_guide_reservations', function (Blueprint $table) {
            $table->text('special_interests')->nullable()->after('number_of_tourists');
            $table->string('language_preference')->nullable()->after('special_interests');
            $table->text('notes')->nullable()->after('language_preference');
        });
    }

    public function down(): void
    {
        Schema::table('tour_guide_reservations', function (Blueprint $table) {
            $table->dropColumn(['special_interests', 'language_preference', 'notes']);
        });
    }
};
