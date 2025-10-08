<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisBarang extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_jenis',
        'nama_jenis',
        'keterangan',
    ];

    public function barangs()
    {
        return $this->hasMany(Barang::class);
    }

    // Method untuk generate ID Barang berikutnya
    public function getNextIdBarang()
    {
        $lastBarang = $this->barangs()->latest('id')->first();
        
        if (!$lastBarang) {
            return $this->kode_jenis . '001';
        }

        // Extract nomor dari id_barang terakhir
        $lastNumber = (int) substr($lastBarang->id_barang, strlen($this->kode_jenis));
        $nextNumber = $lastNumber + 1;

        return $this->kode_jenis . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}