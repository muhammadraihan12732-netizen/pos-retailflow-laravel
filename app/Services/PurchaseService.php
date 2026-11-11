<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pengadaan stok dari supplier.
 * Status "received" langsung menambah stok; "pending" menunggu barang diterima.
 */
class PurchaseService
{
    public function __construct(private StockService $stock)
    {
    }

    /**
     * @param  array{supplier_id:int, status:string, items: array<int, array{product_id:int, quantity:int, purchase_price:float|int}>}  $data
     */
    public function create(array $data, int $userId): Purchase
    {
        return DB::transaction(function () use ($data, $userId) {
            $total = collect($data['items'])
                ->sum(fn ($item) => $item['quantity'] * $item['purchase_price']);

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $userId,
                'purchase_number' => $this->nextNumber(),
                'total' => $total,
                'status' => Purchase::STATUS_PENDING,
            ]);

            foreach ($data['items'] as $item) {
                $purchase->details()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                ]);
            }

            if ($data['status'] === Purchase::STATUS_RECEIVED) {
                $this->receive($purchase, $userId);
            }

            return $purchase->fresh(['details.product', 'supplier']);
        });
    }

    /** Tandai pembelian diterima: tambah stok dan perbarui harga beli produk. */
    public function receive(Purchase $purchase, int $userId): Purchase
    {
        return DB::transaction(function () use ($purchase, $userId) {
            $purchase = Purchase::lockForUpdate()->findOrFail($purchase->id);

            if ($purchase->status === Purchase::STATUS_RECEIVED) {
                throw ValidationException::withMessages([
                    'status' => 'Pembelian ini sudah diterima sebelumnya.',
                ]);
            }

            foreach ($purchase->details()->get() as $detail) {
                $product = Product::lockForUpdate()->find($detail->product_id);

                $this->stock->increase(
                    $product,
                    $detail->quantity,
                    $userId,
                    "Pembelian {$purchase->purchase_number}"
                );

                $product->update(['purchase_price' => $detail->purchase_price]);
            }

            $purchase->update(['status' => Purchase::STATUS_RECEIVED]);

            return $purchase;
        });
    }

    private function nextNumber(): string
    {
        $prefix = 'PO-'.now()->format('Ymd').'-';

        $last = Purchase::where('purchase_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('purchase_number')
            ->value('purchase_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
