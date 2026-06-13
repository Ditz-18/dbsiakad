<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soal_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->onDelete('cascade');
            $table->integer('nomor');
            $table->text('pertanyaan');
            $table->string('tipe')->default('pilihan_ganda'); // pilihan_ganda | essay
            $table->json('pilihan')->nullable(); // [{"key":"A","teks":"..."}, ...]
            $table->string('jawaban_benar')->nullable(); // key jawaban benar (A/B/C/D/E)
            $table->integer('bobot')->default(1);
            $table->timestamps();

            $table->unique(['ujian_id', 'nomor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal_ujian');
    }
};
