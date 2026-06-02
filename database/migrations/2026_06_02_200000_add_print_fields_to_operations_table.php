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
        Schema::table('operations', function (Blueprint $table) {
            $table->time('operation_time')->nullable()->after('operation_date');
            $table->foreignId('item_id')->nullable()->after('operation_time')->constrained('items')->nullOnDelete();
            $table->unsignedInteger('quantity')->nullable()->after('item_id');
            $table->text('statement')->nullable()->after('quantity');
            $table->foreignId('printing_supplier_id')->nullable()->after('statement')->constrained('suppliers')->nullOnDelete();
            $table->foreignId('ctp_supplier_id')->nullable()->after('printing_supplier_id')->constrained('suppliers')->nullOnDelete();
            $table->unsignedTinyInteger('color_count')->nullable()->after('ctp_supplier_id');
            $table->foreignId('material_id')->nullable()->after('color_count')->constrained('materials')->nullOnDelete();
            $table->decimal('job_size', 12, 2)->nullable()->after('material_id');
            $table->unsignedInteger('pull_count')->nullable()->after('job_size');
            $table->unsignedInteger('quantity_per_sheet')->nullable()->after('pull_count');
            $table->foreignId('service_1_id')->nullable()->after('quantity_per_sheet')->constrained('services')->nullOnDelete();
            $table->foreignId('service_2_id')->nullable()->after('service_1_id')->constrained('services')->nullOnDelete();
            $table->foreignId('service_3_id')->nullable()->after('service_2_id')->constrained('services')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_3_id');
            $table->dropConstrainedForeignId('service_2_id');
            $table->dropConstrainedForeignId('service_1_id');
            $table->dropColumn([
                'quantity_per_sheet',
                'pull_count',
                'job_size',
            ]);
            $table->dropConstrainedForeignId('material_id');
            $table->dropColumn('color_count');
            $table->dropConstrainedForeignId('ctp_supplier_id');
            $table->dropConstrainedForeignId('printing_supplier_id');
            $table->dropColumn('statement');
            $table->dropColumn('quantity');
            $table->dropConstrainedForeignId('item_id');
            $table->dropColumn('operation_time');
        });
    }
};
