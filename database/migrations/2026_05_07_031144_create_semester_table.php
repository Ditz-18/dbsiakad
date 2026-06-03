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
        Schema::create('semester', function (Blueprint $table) {
           $table->id();
            $table->string('nama');
            $table->string('tahun_akademik');
            $table->enum('tipe', ['Ganjil','Genap']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->date('krs_buka');
            $table->date('krs_tutup');
            $table->date('nilai_buka');
            $table->date('nilai_tutup');
            $table->enum('status', ['Aktif','Arsip'])->default('Arsip');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semester');
    }
};
