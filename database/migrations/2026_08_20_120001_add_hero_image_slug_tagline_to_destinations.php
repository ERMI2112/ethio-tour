<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('hero_image')->nullable()->after('description');
            $table->string('tagline')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn(['slug', 'hero_image', 'tagline']);
        });
    }
};
