<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnBarang extends Model
{
    use HasFactory;

    protected $table = 'return_barangs';

    protected $fillable = [
        'invoice_id',
        'barang_id',
        'jumlah',
        'alasan',
        'tanggal_return',
        'user_id',
    ];

    protected $casts = [
        'tanggal_return' => 'date',
        'jumlah' => 'integer',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}