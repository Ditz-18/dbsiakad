<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('soal_id')->constrained('soal_ujian')->onDelete('cascade');
            $table->string('jawaban')->nullable();   // key pilihan atau teks essay
            $table->boolean('ragu')->default(false); // tandai ragu-ragu
            $table->timestamps();

            $table->unique(['ujian_id', 'mahasiswa_id', 'soal_id']);
        });

        Schema::create('sesi_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->timestamp('mulai_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->integer('nilai')->nullable();
            $table->enum('status', ['Berlangsung', 'Selesai', 'Dibatalkan'])->default('Berlangsung');
            $table->integer('pelanggaran')->default(0);
            $table->timestamps();

            $table->unique(['ujian_id', 'mahasiswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_ujian');
        Schema::dropIfExists('jawaban_ujian');
    }
};
