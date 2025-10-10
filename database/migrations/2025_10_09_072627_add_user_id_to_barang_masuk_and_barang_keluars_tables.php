<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang_masuks', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('keterangan')->constrained('users')->onDelete('cascade');
        });

        Schema::table('barang_keluars', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('keterangan')->constrained('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('barang_masuks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('barang_keluars', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};