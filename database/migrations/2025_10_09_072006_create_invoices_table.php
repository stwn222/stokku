
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('tipe_invoice', ['MJU', 'BIP']);
            $table->string('nomor_invoice')->unique(); // Format: MJU/1.15.01.2025
            $table->string('nama_client');
            $table->string('nomor_client');
            $table->date('tanggal');
            $table->text('alamat_client');
            $table->decimal('diskon', 5, 2)->default(0); // Persentase diskon
            $table->boolean('ppn')->default(false); // PPN 11%
            $table->timestamps();

            // Index untuk query yang sering
            $table->index('tipe_invoice');
            $table->index('tanggal');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};