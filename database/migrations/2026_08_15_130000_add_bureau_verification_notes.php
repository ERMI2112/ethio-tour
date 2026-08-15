<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_guides', function (Blueprint $table): void {
            $table->text('verification_notes')->nullable()->after('verification_status');
        });

        Schema::table('service_providers', function (Blueprint $table): void {
            $table->text('verification_notes')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', fn (Blueprint $table) => $table->dropColumn('verification_notes'));
        Schema::table('tour_guides', fn (Blueprint $table) => $table->dropColumn('verification_notes'));
    }
};
