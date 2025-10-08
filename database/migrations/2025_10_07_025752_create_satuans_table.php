<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop table jika sudah ada (untuk safety)
        Schema::dropIfExists('satuans');
        
        Schema::create('satuans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_satuan', 50)->unique();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satuans');
    }
};