<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable()->after('alamat');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('nama_ayah')->nullable()->after('tanggal_lahir');
            $table->string('nama_ibu')->nullable()->after('nama_ayah');
            $table->string('no_hp_wali')->nullable()->after('nama_ibu');
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn(['tempat_lahir','tanggal_lahir','nama_ayah','nama_ibu','no_hp_wali']);
        });
    }
};
