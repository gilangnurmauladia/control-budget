<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('master_item_categories')->onDelete('restrict');
            $table->string('item_code', 30)->unique();
            $table->string('item_name', 200);
            $table->string('unit', 30)->comment('Satuan: pcs, kg, liter, box, dll');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_items');
    }
};
