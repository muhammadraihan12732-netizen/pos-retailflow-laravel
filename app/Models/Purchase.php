<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RECEIVED = 'received';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu',
        self::STATUS_RECEIVED => 'Diterima',
    ];

    protected $fillable = ['supplier_id', 'user_id', 'purchase_number', 'total', 'status'];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
