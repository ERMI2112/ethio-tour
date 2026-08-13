<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->bigIncrements('report_id');
            $table->unsignedBigInteger('generated_by_user_id');
            $table->string('report_type');
            $table->timestamp('generated_date')->useCurrent();
            $table->timestamps();
            $table->index('generated_by_user_id');
            $table->foreign('generated_by_user_id')->references('user_id')->on('users')->restrictOnDelete();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('notification_id');
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('message');
            $table->string('channel');
            $table->timestamp('sent_date')->useCurrent();
            $table->boolean('read_status')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'read_status']);
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('reports');
    }
};
