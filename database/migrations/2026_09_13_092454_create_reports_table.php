<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('facility_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $table->enum('category', [
                'kerusakan_alat', 'kelistrikan', 'pendingin_ruangan',
                'furnitur', 'kebersihan', 'lainnya',
            ]);
            $table->text('description');
            $table->enum('status', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru');
            $table->text('resolution_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'idx_queue');
            $table->index(['facility_id', 'status'], 'idx_facility_freq');
            $table->index(['user_id', 'status'], 'idx_reporter');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};