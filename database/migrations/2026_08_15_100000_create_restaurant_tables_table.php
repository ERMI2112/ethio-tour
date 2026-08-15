<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_tables', function (Blueprint $table): void {
            $table->bigIncrements('table_id');
            $table->unsignedBigInteger('provider_id');
            $table->string('table_number');
            $table->unsignedSmallInteger('capacity');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['provider_id', 'table_number'], 'restaurant_tables_provider_number_unique');
            $table->index(['provider_id', 'status'], 'restaurant_tables_provider_status_idx');
            $table->foreign('provider_id')
                ->references('provider_id')
                ->on('service_providers')
                ->restrictOnDelete();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE restaurant_tables ADD CONSTRAINT restaurant_tables_capacity_check CHECK (capacity > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
