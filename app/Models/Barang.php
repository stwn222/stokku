<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_barang',
        'jenis_barang_id',
        'nama_barang',
        'stok',
        'stok_minimum',
        'stok_maksimum',
        'satuan_id',
        'harga_jual',
    ];

    protected $casts = [
        'stok' => 'integer',
        'stok_minimum' => 'integer',
        'stok_maksimum' => 'integer',
        'harga_jual' => 'decimal:2',
    ];

    public function jenisBarang()
    {
        return $this->belongsTo(JenisBarang::class);
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }
}