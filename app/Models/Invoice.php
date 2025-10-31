<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tipe_invoice',
        'nomor_invoice',
        'nama_client',
        'tanggal',
        'alamat_client',
        'diskon',
        'ppn',
        'payment_method_id',
    ];

    protected $casts = [
        'ppn' => 'boolean',
        'tanggal' => 'date',
        'diskon' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}