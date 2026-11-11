<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Alur transaksi POS:
 * validasi stok -> hitung subtotal & diskon -> cek pembayaran ->
 * simpan transaksi -> simpan detail -> kurangi stok.
 * Semuanya dalam satu DB transaction (gagal di tengah = tidak ada yang tersimpan).
 */
class SaleService
{
    public function __construct(private StockService $stock)
    {
    }

    /**
     * @param  array{items: array<int, array{product_id:int, quantity:int}>, customer_id?: int|null, discount?: float|int|null, payment_method: string, paid?: float|int|null}  $data
     */
    public function create(array $data, int $userId): Sale
    {
        return DB::transaction(function () use ($data, $userId) {
            // Gabungkan baris produk yang sama.
            $quantities = collect($data['items'])
                ->groupBy('product_id')
                ->map(fn ($rows) => (int) $rows->sum('quantity'));

            // Kunci baris produk agar stok tidak bentrok dengan kasir lain.
            $products = Product::whereIn('id', $quantities->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;
            $lines = [];

            foreach ($quantities as $productId => $quantity) {
                /** @var Product|null $product */
                $product = $products->get($productId);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => 'Ada produk yang tidak tersedia atau sudah nonaktif.',
                    ]);
                }

                $price = (float) $product->selling_price;
                $lineTotal = round($price * $quantity, 2);
                $subtotal += $lineTotal;

                $lines[] = compact('product', 'quantity', 'price', 'lineTotal');
            }

            $discount = min(max((float) ($data['discount'] ?? 0), 0), $subtotal);
            $total = round($subtotal - $discount, 2);

            // Non-tunai dianggap dibayar pas.
            $paid = $data['payment_method'] === 'cash'
                ? (float) ($data['paid'] ?? 0)
                : $total;

            if ($paid < $total) {
                throw ValidationException::withMessages([
                    'paid' => 'Jumlah pembayaran kurang dari total belanja.',
                ]);
            }

            $sale = Sale::create([
                'user_id' => $userId,
                'customer_id' => $data['customer_id'] ?? null,
                'invoice_number' => $this->nextInvoiceNumber(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid' => $paid,
                'change' => round($paid - $total, 2),
                'payment_method' => $data['payment_method'],
            ]);

            foreach ($lines as $line) {
                $sale->details()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                    'subtotal' => $line['lineTotal'],
                ]);

                $this->stock->decrease(
                    $line['product'],
                    $line['quantity'],
                    $userId,
                    "Penjualan {$sale->invoice_number}"
                );
            }

            return $sale->load(['details.product', 'customer', 'user']);
        });
    }

    private function nextInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ymd').'-';

        $last = Sale::where('invoice_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
