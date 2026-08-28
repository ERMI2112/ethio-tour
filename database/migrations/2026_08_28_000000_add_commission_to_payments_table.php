<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Snapshot of the provider's plan commission rate at confirmation
            // time. NULL = not commissionable (guide bookings, providers in
            // trial without an active plan, or pre-commission payments).
            $table->decimal('commission_rate', 5, 2)->nullable()->after('amount');
            $table->decimal('commission_amount', 10, 2)->nullable()->after('commission_rate');
            $table->decimal('provider_net_amount', 10, 2)->nullable()->after('commission_amount');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['commission_rate', 'commission_amount', 'provider_net_amount']);
        });
    }
};
