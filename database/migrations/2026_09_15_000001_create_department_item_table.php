<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('master_departments')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('master_items')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['department_id', 'item_id']);
        });

        // Attach existing items to all existing departments so existing data stays intact
        $departments = DB::table('master_departments')->pluck('id');
        $items = DB::table('master_items')->pluck('id');

        $now = now();
        $records = [];
        foreach ($items as $itemId) {
            foreach ($departments as $deptId) {
                $records[] = [
                    'department_id' => $deptId,
                    'item_id'       => $itemId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
        }

        if (!empty($records)) {
            DB::table('department_item')->insert($records);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('department_item');
    }
};
