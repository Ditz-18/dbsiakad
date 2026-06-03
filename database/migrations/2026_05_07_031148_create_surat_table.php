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
        Schema::create('surat', function (Blueprint $table) {
           $table->id();
            $table->string('no_pengajuan')->unique();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
            $table->string('jenis_surat');
            $table->text('keperluan');
            $table->enum('status', ['Menunggu','Diproses','Selesai','Ditolak'])->default('Menunggu');
            $table->foreignId('diproses_oleh')->nullable()->constrained('admin');
            $table->timestamp('diproses_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat');
    }
};
