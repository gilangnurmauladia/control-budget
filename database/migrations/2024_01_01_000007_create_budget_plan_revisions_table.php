<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_plan_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_plan_id')->constrained('budget_plans')->onDelete('cascade');
            $table->dateTime('revision_date');
            $table->decimal('old_plan_qty', 15, 3);
            $table->decimal('new_plan_qty', 15, 3);
            $table->decimal('version_from', 5, 1);
            $table->decimal('version_to', 5, 1);
            $table->string('reason_category', 100)->nullable();
            $table->text('reason_detail')->nullable();
            $table->foreignId('revised_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_plan_revisions');
    }
};
