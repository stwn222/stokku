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

    // CRITICAL: Pastikan relasi ini ada dan nama method-nya PERSIS 'paymentMethod'
    // Nama method harus camelCase dan sama dengan yang dipanggil di controller
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnBarang::class);
    }
    
    // Optional: Accessor untuk mendapatkan payment method info dengan mudah
    public function getPaymentMethodNameAttribute()
    {
        return $this->paymentMethod?->nama_metode ?? '-';
    }
    
    public function getPaymentMethodAccountAttribute()
    {
        return $this->paymentMethod?->nomor_rekening ?? '-';
    }
    
    public function getPaymentMethodOwnerAttribute()
    {
        return $this->paymentMethod?->atas_nama ?? '-';
    }
}