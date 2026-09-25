<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_plans', function (Blueprint $table) {
            $table->id();
            $table->string('period', 7)->comment('Format: YYYY-MM');
            $table->foreignId('department_id')->constrained('master_departments')->onDelete('restrict');
            $table->foreignId('location_id')->nullable()->constrained('master_locations')->onDelete('set null');
            $table->foreignId('item_id')->constrained('master_items')->onDelete('restrict');
            $table->decimal('plan_qty', 15, 3)->default(0);
            $table->decimal('unit_price_snapshot', 15, 2)->default(0);
            $table->decimal('current_version', 5, 1)->default(1.0);
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            
            $table->unique(['period', 'department_id', 'item_id'], 'unique_plan_per_period');
            $table->index(['period', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_plans');
    }
};
