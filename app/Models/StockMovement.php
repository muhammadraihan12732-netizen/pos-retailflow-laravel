<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPES = [
        self::TYPE_IN => 'Masuk',
        self::TYPE_OUT => 'Keluar',
        self::TYPE_ADJUSTMENT => 'Penyesuaian',
    ];

    // Tabel hanya punya created_at.
    public const UPDATED_AT = null;

    protected $fillable = ['product_id', 'user_id', 'type', 'quantity', 'notes'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
