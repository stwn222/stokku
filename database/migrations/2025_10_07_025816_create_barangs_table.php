<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop table jika sudah ada (untuk safety)
        Schema::dropIfExists('barangs');
        
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('id_barang')->unique();
            $table->unsignedBigInteger('jenis_barang_id');
            $table->string('nama_barang');
            $table->integer('stok')->default(0);
            $table->unsignedBigInteger('satuan_id');
            $table->decimal('harga_jual', 15, 2);
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('jenis_barang_id')
                  ->references('id')
                  ->on('jenis_barangs')
                  ->onDelete('cascade');
                  
            $table->foreign('satuan_id')
                  ->references('id')
                  ->on('satuans')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};