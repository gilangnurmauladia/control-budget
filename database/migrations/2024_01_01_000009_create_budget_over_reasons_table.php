<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_over_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_plan_id')->constrained('budget_plans')->onDelete('cascade');
            $table->date('flag_date');
            $table->string('reason_category', 100);
            $table->text('reason_detail');
            $table->foreignId('submitted_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_over_reasons');
    }
};
