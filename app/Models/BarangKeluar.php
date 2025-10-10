<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_transaksi',
        'barang_id',
        'tanggal',
        'jumlah',
        'harga_jual',
        'ppn',
        'total',
        'is_ppn',
        'keterangan',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'harga_jual' => 'decimal:2',
        'ppn' => 'decimal:2',
        'total' => 'decimal:2',
        'is_ppn' => 'boolean',
    ];

    /**
     * Relasi ke tabel barangs
     */
   public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * Generate kode transaksi otomatis
     * Format: TK-00001, TK-00002, dst
     */
    public static function generateKodeTransaksi()
    {
        // Ambil transaksi terakhir
        $lastTransaction = self::orderBy('kode_transaksi', 'desc')->first();

        if ($lastTransaction) {
            // Ambil nomor dari kode terakhir (misal: TK-00001 -> 1)
            $lastNumber = intval(substr($lastTransaction->kode_transaksi, 3));
            $newNumber = $lastNumber + 1;
        } else {
            // Jika belum ada transaksi, mulai dari 1
            $newNumber = 1;
        }

        // Format: TK-00001 (5 digit dengan leading zero)
        return 'TK-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}