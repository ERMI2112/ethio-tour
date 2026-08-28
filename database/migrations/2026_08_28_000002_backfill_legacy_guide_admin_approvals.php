<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only legacy approvals without any Administrator decision evidence are
        // moved back into the final-approval queue. Explicit decisions remain.
        DB::table('tour_guides')
            ->where('verification_status', 'verified')
            ->where('admin_approval_status', 'approved')
            ->whereNull('admin_approved_by')
            ->whereNull('admin_approved_at')
            ->update([
                'admin_approval_status' => 'pending',
                'admin_approval_notes' => 'Requires explicit Administrator approval under the final guide approval workflow.',
            ]);

        Schema::table('tour_guides', function (Blueprint $table): void {
            $table->string('admin_approval_status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('tour_guides', function (Blueprint $table): void {
            $table->string('admin_approval_status')->default('approved')->change();
        });
    }
};
