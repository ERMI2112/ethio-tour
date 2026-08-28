<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_guides', function (Blueprint $table): void {
            $table->string('admin_approval_status')->default('approved')->after('verification_status');
            $table->text('admin_approval_notes')->nullable()->after('verification_notes');
            $table->timestamp('admin_approved_at')->nullable()->after('admin_approval_notes');
            $table->unsignedBigInteger('admin_approved_by')->nullable()->after('admin_approved_at');
            $table->foreign('admin_approved_by')->references('user_id')->on('users')->nullOnDelete();
            $table->index(['verification_status', 'admin_approval_status']);
        });
    }

    public function down(): void
    {
        Schema::table('tour_guides', function (Blueprint $table): void {
            $table->dropForeign(['admin_approved_by']);
            $table->dropIndex(['verification_status', 'admin_approval_status']);
            $table->dropColumn(['admin_approval_status', 'admin_approval_notes', 'admin_approved_at', 'admin_approved_by']);
        });
    }
};
