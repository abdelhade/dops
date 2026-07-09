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
            $table->date('printing_in_date')->nullable()->after('printing_supplier_id');
            $table->date('printing_out_date')->nullable()->after('printing_in_date');

            $table->date('service_1_in_date')->nullable()->after('service_1_id');
            $table->date('service_1_out_date')->nullable()->after('service_1_in_date');

            $table->date('service_2_in_date')->nullable()->after('service_2_id');
            $table->date('service_2_out_date')->nullable()->after('service_2_in_date');

            $table->date('service_3_in_date')->nullable()->after('service_3_id');
            $table->date('service_3_out_date')->nullable()->after('service_3_in_date');

            $table->foreignId('service_4_id')->nullable()->after('service_3_out_date')->constrained('services')->nullOnDelete();
            $table->date('service_4_in_date')->nullable()->after('service_4_id');
            $table->date('service_4_out_date')->nullable()->after('service_4_in_date');

            $table->date('entry_date')->nullable()->after('operation_date');
            $table->date('exit_date')->nullable()->after('entry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropColumn([
                'printing_in_date',
                'printing_out_date',
                'service_1_in_date',
                'service_1_out_date',
                'service_2_in_date',
                'service_2_out_date',
                'service_3_in_date',
                'service_3_out_date',
                'service_4_in_date',
                'service_4_out_date',
                'entry_date',
                'exit_date',
            ]);
            $table->dropConstrainedForeignId('service_4_id');
        });
    }
};
