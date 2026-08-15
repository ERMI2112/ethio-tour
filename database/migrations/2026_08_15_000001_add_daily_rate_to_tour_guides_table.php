<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_guides', function (Blueprint $table): void {
            $table->decimal('daily_rate', 10, 2)->nullable()->after('availability_status');
        });
    }

    public function down(): void
    {
        Schema::table('tour_guides', function (Blueprint $table): void {
            $table->dropColumn('daily_rate');
        });
    }
};
