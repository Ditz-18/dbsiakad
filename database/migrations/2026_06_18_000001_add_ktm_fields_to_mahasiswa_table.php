<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->boolean('ktm_aktif')->default(false)->after('no_hp_wali');
            $table->date('ktm_berlaku_hingga')->nullable()->after('ktm_aktif');
            $table->timestamp('ktm_generated_at')->nullable()->after('ktm_berlaku_hingga');
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn(['ktm_aktif', 'ktm_berlaku_hingga', 'ktm_generated_at']);
        });
    }
};
