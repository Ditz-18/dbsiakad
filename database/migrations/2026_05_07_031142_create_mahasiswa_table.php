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
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nim')->unique();
            $table->string('nama');
            $table->foreignId('program_studi_id')->constrained('program_studi');
            $table->year('angkatan');
            $table->integer('semester')->default(1);
            $table->enum('status', ['Aktif','Cuti','Lulus','Dropout'])->default('Aktif');
            $table->unsignedBigInteger('dosen_pa_id')->nullable();
            $table->string('foto')->nullable();
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
