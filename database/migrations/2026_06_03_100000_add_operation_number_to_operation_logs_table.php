<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operation_logs', function (Blueprint $table) {
            $table->string('operation_number', 100)->nullable()->after('operation_id');
        });

        Schema::table('operation_logs', function (Blueprint $table) {
            $table->dropForeign(['operation_id']);
        });

        Schema::table('operation_logs', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable()->change();
            $table->foreign('operation_id')->references('id')->on('operations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operation_logs', function (Blueprint $table) {
            $table->dropForeign(['operation_id']);
        });

        Schema::table('operation_logs', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable(false)->change();
            $table->foreign('operation_id')->references('id')->on('operations')->cascadeOnDelete();
            $table->dropColumn('operation_number');
        });
    }
};
