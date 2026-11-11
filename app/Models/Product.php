<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'sku', 'barcode', 'name', 'brand',
        'purchase_price', 'selling_price', 'stock', 'min_stock', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function purchaseDetails(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('barcode', 'like', "%{$term}%")
                ->orWhere('brand', 'like', "%{$term}%");
        });
    }

    /** Stok sudah di bawah atau sama dengan batas minimum. */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    /** [label, css-class] untuk badge status di tabel. */
    public function statusBadge(): array
    {
        if (! $this->is_active) {
            return ['Nonaktif', 'badge-gray'];
        }
        if ($this->stock <= 0) {
            return ['Habis', 'badge-red'];
        }
        if ($this->isLowStock()) {
            return ['Stok Menipis', 'badge-amber'];
        }

        return ['Aktif', 'badge-green'];
    }
}
