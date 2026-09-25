<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('master_departments')->onDelete('cascade');
            $table->string('location_code', 30)->unique();
            $table->string('location_name', 100);
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_locations');
    }
};
