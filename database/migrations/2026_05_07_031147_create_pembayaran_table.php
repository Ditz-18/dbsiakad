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
        Schema::create('pembayaran', function (Blueprint $table) {
          $table->id();
        $table->foreignId('mahasiswa_id')->constrained('mahasiswa');
        $table->foreignId('semester_id')->constrained('semester');
        $table->bigInteger('nominal');
        $table->enum('status', ['Lunas','Menunggak'])->default('Menunggak');
        $table->date('tanggal_bayar')->nullable();
        $table->string('no_referensi')->nullable();
        $table->foreignId('dikonfirmasi_oleh')->nullable()->constrained('admin');
        $table->timestamps();
        $table->unique(['mahasiswa_id','semester_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
