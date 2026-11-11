<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    public const PAYMENT_METHODS = [
        'cash' => 'Tunai',
        'debit' => 'Kartu Debit',
        'qris' => 'QRIS',
        'transfer' => 'Transfer Bank',
    ];

    protected $fillable = [
        'user_id', 'customer_id', 'invoice_number', 'subtotal', 'discount',
        'total', 'paid', 'change', 'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid' => 'decimal:2',
            'change' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function getPaymentLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }
}
