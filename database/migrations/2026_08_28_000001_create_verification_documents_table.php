<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_documents', function (Blueprint $table): void {
            $table->bigIncrements('document_id');
            $table->string('documentable_type');
            $table->unsignedBigInteger('documentable_id');
            $table->unsignedBigInteger('uploaded_by');
            $table->string('document_type', 50);
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type', 100);
            $table->unsignedInteger('size_bytes');
            $table->string('sha256', 64);
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            $table->index(['documentable_type', 'documentable_id']);
            $table->index(['status', 'document_type']);
            $table->foreign('uploaded_by')->references('user_id')->on('users')->restrictOnDelete();
            $table->foreign('reviewed_by')->references('user_id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_documents');
    }
};
