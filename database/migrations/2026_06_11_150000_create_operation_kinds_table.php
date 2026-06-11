<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_kinds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $now = now();
        foreach ([
            ['name' => 'سلك سكرين', 'sort_order' => 1],
            ['name' => 'تقطيع', 'sort_order' => 2],
            ['name' => 'تغليف', 'sort_order' => 3],
        ] as $kind) {
            DB::table('operation_kinds')->insert(array_merge($kind, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        Schema::table('operations', function (Blueprint $table) {
            $table->foreignId('operation_kind_id')->nullable()->after('operation_type_id')->constrained('operation_kinds')->nullOnDelete();
        });

        $generalTypeId = DB::table('operation_types')->where('slug', 'general')->value('id');
        $silkTypeId = DB::table('operation_types')->where('slug', 'silk_screen')->value('id');

        if ($generalTypeId && $silkTypeId) {
            DB::table('operations')
                ->where('operation_type_id', $silkTypeId)
                ->update(['operation_type_id' => $generalTypeId]);
        }

        if ($silkTypeId) {
            DB::table('operation_types')->where('id', $silkTypeId)->delete();
        }

        DB::table('operation_types')
            ->where('slug', 'general')
            ->update([
                'name' => 'عام',
                'form_mode' => 'general',
                'serial_prefix' => 'SS',
                'sort_order' => 2,
                'is_system' => true,
                'updated_at' => $now,
            ]);

        DB::table('operation_types')
            ->where('form_mode', 'silk_screen')
            ->update(['form_mode' => 'general', 'updated_at' => $now]);

        if (Schema::hasColumn('operations', 'operation_kind')) {
            Schema::table('operations', function (Blueprint $table) {
                $table->dropColumn('operation_kind');
            });
        }
    }

    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->string('operation_kind', 255)->nullable()->after('operation_type_id');
            $table->dropConstrainedForeignId('operation_kind_id');
        });

        Schema::dropIfExists('operation_kinds');

        $now = now();
        DB::table('operation_types')->insert([
            'name' => 'SILK-SCREEN',
            'slug' => 'silk_screen',
            'form_mode' => 'silk_screen',
            'serial_prefix' => 'SS',
            'sort_order' => 2,
            'is_system' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
