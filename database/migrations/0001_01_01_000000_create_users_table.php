<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->enum('role', ['pengguna', 'petugas', 'admin'])->default('pengguna');
            $table->enum('status', ['pending', 'verified', 'rejected', 'suspended'])->default('pending');
            $table->string('identity_number', 30)->nullable()->unique();
            $table->enum('user_type', ['mahasiswa', 'dosen', 'staf'])->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};