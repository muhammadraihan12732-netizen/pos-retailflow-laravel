<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseDetail extends Model
{
    public $timestamps = false;

    protected $fillable = ['purchase_id', 'product_id', 'quantity', 'purchase_price'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'purchase_price' => 'decimal:2',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
