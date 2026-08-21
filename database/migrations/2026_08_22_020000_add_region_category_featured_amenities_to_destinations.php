<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->string('region')->nullable()->index()->after('location');
            $table->string('category')->nullable()->index()->after('region');
            $table->boolean('is_featured')->default(false)->index()->after('category');
            $table->json('amenities')->nullable()->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropColumn(['region', 'category', 'is_featured', 'amenities']);
        });
    }
};
