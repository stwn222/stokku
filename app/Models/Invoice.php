<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'user_id',
        'tipe_invoice',
        'nomor_invoice',
        'nama_client',
        'nomor_client',
        'tanggal',
        'alamat_client',
        'diskon',
        'ppn',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'ppn' => 'boolean',
        'diskon' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->details->sum(function ($detail) {
            return $detail->qty * $detail->harga;
        });
    }

    public function getDiscountAmountAttribute(): float
    {
        return ($this->subtotal * $this->diskon) / 100;
    }

    public function getAfterDiscountAttribute(): float
    {
        return $this->subtotal - $this->discount_amount;
    }

    public function getPpnAmountAttribute(): float
    {
        if ($this->ppn) {
            return ($this->after_discount * 11) / 100;
        }
        return 0;
    }

    public function getTotalAttribute(): float
    {
        return $this->after_discount + $this->ppn_amount;
    }
}