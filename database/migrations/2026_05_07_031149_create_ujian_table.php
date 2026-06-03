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
        Schema::create('ujian', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('dosen_id')->constrained('dosen');
            $table->foreignId('semester_id')->constrained('semester');
            $table->enum('tipe', ['Kuis','UTS','UAS'])->default('UTS');
            $table->integer('durasi')->default(90);
            $table->datetime('mulai_at')->nullable();
            $table->datetime('selesai_at')->nullable();
            $table->enum('status', ['Draft','Berlangsung','Selesai','Dibatalkan'])->default('Draft');
            $table->integer('max_pelanggaran')->default(3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian');
    }
};
