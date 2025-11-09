<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finances', function (Blueprint $table) {
            // Cek dulu apakah kolom sudah ada (mencegah duplicate column error)
            if (!Schema::hasColumn('finances', 'tanggal')) {
                $table->date('tanggal')->nullable(); // hapus ->after('nominal') jika pakai PostgreSQL
            }
        });
    }

    public function down(): void
    {
        Schema::table('finances', function (Blueprint $table) {
            if (Schema::hasColumn('finances', 'tanggal')) {
                $table->dropColumn('tanggal');
            }
        });
    }
};
