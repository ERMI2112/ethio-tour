<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->decimal('total_amount', 10, 2)->nullable()->after('guide_id');
            $table->string('currency', 3)->default('ETB')->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn(['total_amount', 'currency']);
        });
    }
};
