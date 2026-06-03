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
        Schema::create('krs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('semester_id')->constrained('semester');
            $table->enum('status', ['Draft','Menunggu','Disetujui','Ditolak'])->default('Draft');
            $table->timestamp('diajukan_at')->nullable();
            $table->timestamp('diproses_at')->nullable();
            $table->text('catatan_pa')->nullable();
            $table->timestamps();
            $table->unique(['mahasiswa_id','kelas_id','semester_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('krs');
    }
};
