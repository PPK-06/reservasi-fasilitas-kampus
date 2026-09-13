<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('facility_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('purpose', 255);
            $table->enum('status', [
                'pending', 'approved', 'rejected',
                'cancelled_by_user', 'cancelled_by_officer',
            ])->default('pending');
            $table->text('status_reason')->nullable();
            $table->timestamps();

            $table->index(['facility_id', 'status', 'start_time', 'end_time'], 'idx_conflict');
            $table->index(['user_id', 'status', 'start_time'], 'idx_user_history');
            $table->index(['status', 'start_time'], 'idx_queue');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};