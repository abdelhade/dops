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
        Schema::create('operation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 50)->unique();
            $table->string('form_mode', 30);
            $table->string('serial_prefix', 20);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $now = now();
        $types = [
            ['name' => 'OFFSET', 'slug' => 'offset', 'form_mode' => OperationTypeMode::Offset->value, 'serial_prefix' => 'OFF', 'sort_order' => 1, 'is_system' => true],
            ['name' => 'عام', 'slug' => 'general', 'form_mode' => OperationTypeMode::General->value, 'serial_prefix' => 'SS', 'sort_order' => 2, 'is_system' => true],
        ];

        foreach ($types as $type) {
            DB::table('operation_types')->insert(array_merge($type, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_types');
    }
};
