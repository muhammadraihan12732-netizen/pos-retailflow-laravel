<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Validation\ValidationException;

/**
 * Satu-satunya tempat yang boleh mengubah kolom products.stock.
 * Setiap perubahan otomatis dicatat ke stock_movements.
 */
class StockService
{
    public function increase(Product $product, int $quantity, ?int $userId = null, ?string $notes = null): StockMovement
    {
        $product->increment('stock', $quantity);

        return $this->record($product, StockMovement::TYPE_IN, $quantity, $userId, $notes);
    }

    /**
     * @throws ValidationException bila stok tidak mencukupi
     */
    public function decrease(Product $product, int $quantity, ?int $userId = null, ?string $notes = null): StockMovement
    {
        if ($product->stock < $quantity) {
            throw ValidationException::withMessages([
                'items' => "Stok {$product->name} tidak cukup (tersedia {$product->stock}, diminta {$quantity}).",
            ]);
        }

        $product->decrement('stock', $quantity);

        return $this->record($product, StockMovement::TYPE_OUT, $quantity, $userId, $notes);
    }

    /** Koreksi stok manual ke angka tertentu. Mencatat selisih (bisa negatif). */
    public function adjust(Product $product, int $newStock, ?int $userId = null, ?string $notes = null): ?StockMovement
    {
        $difference = $newStock - $product->stock;

        if ($difference === 0) {
            return null;
        }

        $product->update(['stock' => $newStock]);

        return $this->record($product, StockMovement::TYPE_ADJUSTMENT, $difference, $userId, $notes);
    }

    private function record(Product $product, string $type, int $quantity, ?int $userId, ?string $notes): StockMovement
    {
        return StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'notes' => $notes,
        ]);
    }
}
