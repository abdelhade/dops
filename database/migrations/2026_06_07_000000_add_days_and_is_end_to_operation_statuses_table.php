<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operation_statuses', function (Blueprint $table) {
            $table->unsignedInteger('days')->default(1)->after('sort_order');
            $table->boolean('is_end')->default(false)->after('days');
        });
    }

    public function down(): void
    {
        Schema::table('operation_statuses', function (Blueprint $table) {
            $table->dropColumn(['days', 'is_end']);
        });
    }
};
