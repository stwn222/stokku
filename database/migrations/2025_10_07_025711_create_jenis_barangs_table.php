<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop table jika sudah ada (untuk safety)
        Schema::dropIfExists('jenis_barangs');
        
        Schema::create('jenis_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_jenis', 10)->unique();
            $table->string('nama_jenis');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_barangs');
    }
};