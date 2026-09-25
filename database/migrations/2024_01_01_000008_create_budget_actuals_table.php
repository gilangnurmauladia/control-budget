<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_actuals', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->foreignId('department_id')->constrained('master_departments')->onDelete('restrict');
            $table->foreignId('location_id')->nullable()->constrained('master_locations')->onDelete('set null');
            $table->foreignId('item_id')->constrained('master_items')->onDelete('restrict');
            $table->decimal('actual_qty', 15, 3);
            $table->decimal('unit_price_snapshot', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            
            $table->index(['transaction_date', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_actuals');
    }
};
