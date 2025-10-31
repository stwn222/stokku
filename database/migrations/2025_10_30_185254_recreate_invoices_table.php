<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop jika sudah ada
        Schema::dropIfExists('invoice_details');
        Schema::dropIfExists('invoices');

        // Buat ulang dengan struktur yang benar
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('tipe_invoice', ['MJU', 'BIP']);
            $table->string('nomor_invoice')->unique();
            $table->string('nama_client');
            $table->date('tanggal');
            $table->text('alamat_client');
            $table->decimal('diskon', 5, 2)->default(0);
            $table->boolean('ppn')->default(false);
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->onDelete('set null');
            $table->timestamps();

            $table->index('tipe_invoice');
            $table->index('tanggal');
            $table->index('user_id');
        });

        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('restrict');
            $table->integer('qty');
            $table->decimal('harga', 12, 2);
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('barang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
        Schema::dropIfExists('invoices');
    }
};