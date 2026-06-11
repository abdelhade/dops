<?php

use App\Enums\OperationTypeMode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->foreignId('operation_type_id')->nullable()->after('operation_number')->constrained('operation_types')->nullOnDelete();
            $table->string('operation_kind', 255)->nullable()->after('operation_type_id');
        });

        $typeMap = DB::table('operation_types')->pluck('id', 'slug');

        foreach (DB::table('operations')->orderBy('id')->get() as $operation) {
            $slug = $operation->operation_type ?? 'offset';
            $typeId = $typeMap[$slug] ?? $typeMap['offset'] ?? null;

            DB::table('operations')->where('id', $operation->id)->update([
                'operation_type_id' => $typeId,
            ]);
        }

        Schema::table('operations', function (Blueprint $table) {
            $table->dropColumn('operation_type');
        });
    }

    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->string('operation_type', 30)->default('offset')->after('operation_number');
        });

        $slugMap = DB::table('operation_types')->pluck('slug', 'id');

        foreach (DB::table('operations')->orderBy('id')->get() as $operation) {
            DB::table('operations')->where('id', $operation->id)->update([
                'operation_type' => $slugMap[$operation->operation_type_id] ?? 'offset',
            ]);
        }

        Schema::table('operations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('operation_type_id');
            $table->dropColumn('operation_kind');
        });
    }
};
