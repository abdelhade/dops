<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('operation_movements', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
            $table->foreignId('operation_status_id')->nullable()->constrained('operation_statuses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operation_movements', function (Blueprint $table) {
            $table->dropForeign(['operation_status_id']);
            $table->dropColumn('operation_status_id');
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
        });
    }
};
