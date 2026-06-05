<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('material_id');
            $table->foreignId('paper_type_id')->nullable()->after('color_count')->constrained('paper_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paper_type_id');
            $table->foreignId('material_id')->nullable()->after('color_count')->constrained('materials')->nullOnDelete();
        });
    }
};
