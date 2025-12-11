<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BarangMasuk extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_transaksi',
        'barang_id',
        'tanggal',
        'jumlah',
        'harga_beli',
        'is_ppn',
        'ppn',
        'total',
        'vendor',
        'keterangan',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'harga_beli' => 'decimal:2',
        'ppn' => 'decimal:2',
        'total' => 'decimal:2',
        'jumlah' => 'integer',
        'is_ppn' => 'boolean',
    ];

    protected $with = ['barang'];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public static function generateKodeTransaksi()
    {
        // Gunakan lock untuk mencegah race condition
        return DB::transaction(function () {
            $lastTransaction = self::orderBy('kode_transaksi', 'desc')
                ->lockForUpdate()
                ->first();
            
            if (!$lastTransaction) {
                return 'TM0001';
            }

            // PERBAIKAN: Gunakan posisi 2, bukan 5
            // Format: TM0001 -> substr dari posisi 2 = "0001"
            $lastNumber = (int) substr($lastTransaction->kode_transaksi, 2);
            $nextNumber = $lastNumber + 1;

            return 'TM' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    public function calculatePPN()
    {
        if ($this->is_ppn) {
            return $this->harga_beli * $this->jumlah * 0.11;
        }
        return 0;
    }

    public function calculateTotal()
    {
        $subtotal = $this->harga_beli * $this->jumlah;
        return $subtotal + $this->ppn;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}