<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('type', 50);
            $table->string('location', 50);
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'under_maintenance', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['type', 'location'], 'idx_search');
            $table->index('status', 'idx_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};