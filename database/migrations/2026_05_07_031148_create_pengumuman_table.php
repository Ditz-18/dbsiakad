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
        Schema::create('pengumuman', function (Blueprint $table) {
          $table->id();
            $table->string('judul');
            $table->text('isi');
            $table->enum('kategori', ['Akademik','Keuangan','Umum']);
            $table->enum('target', ['Semua','Mahasiswa','Dosen'])->default('Semua');
            $table->boolean('penting')->default(false);
            $table->boolean('baru')->default(true);
            $table->enum('status', ['Aktif','Arsip'])->default('Aktif');
            $table->foreignId('dibuat_oleh')->constrained('admin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
