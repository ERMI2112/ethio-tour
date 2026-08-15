<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table): void {
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])
                ->default('pending')->index()->after('status');
        });

        DB::table('service_providers')->where('status', 'approved')->update(['verification_status' => 'verified']);
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table): void {
            $table->dropIndex(['verification_status']);
            $table->dropColumn('verification_status');
        });
    }
};
