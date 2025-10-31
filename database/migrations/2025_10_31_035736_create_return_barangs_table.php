<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->integer('jumlah');
            $table->text('alasan')->nullable();
            $table->date('tanggal_return');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_barangs');
    }
};
