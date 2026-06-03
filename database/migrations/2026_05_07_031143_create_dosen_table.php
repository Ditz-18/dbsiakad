<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nip')->unique();
            $table->string('nama');
            $table->foreignId('program_studi_id')->constrained('program_studi');
            $table->string('fakultas');
            $table->enum('jabatan', ['Tenaga Pengajar','Asisten Ahli','Lektor','Lektor Kepala','Guru Besar'])->default('Tenaga Pengajar');
            $table->string('golongan')->nullable();
            $table->string('email_akademik')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('foto')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen');
    }
};
