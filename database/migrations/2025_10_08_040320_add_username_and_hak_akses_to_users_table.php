<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan username jika belum ada
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->after('name');
            }
            
            // Tambahkan hak_akses jika belum ada
            if (!Schema::hasColumn('users', 'hak_akses')) {
                $table->enum('hak_akses', ['Administrator', 'Karyawan'])
                      ->default('Karyawan')
                      ->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }
            if (Schema::hasColumn('users', 'hak_akses')) {
                $table->dropColumn('hak_akses');
            }
        });
    }
};