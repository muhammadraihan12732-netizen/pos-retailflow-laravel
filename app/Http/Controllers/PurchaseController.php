<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseService $purchases)
    {
    }

    public function index(Request $request)
    {
        $purchases = Purchase::with(['supplier', 'user'])
            ->when($request->q, fn ($q, $s) => $q->where('purchase_number', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'products' => Product::where('is_active', true)->orderBy('name')
                ->get(['id', 'sku', 'name', 'purchase_price'])
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'label' => "{$p->sku} - {$p->name}",
                    'price' => (float) $p->purchase_price,
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'status' => ['required', Rule::in([Purchase::STATUS_PENDING, Purchase::STATUS_RECEIVED])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Tambahkan minimal satu produk.',
            'items.min' => 'Tambahkan minimal satu produk.',
        ]);

        $purchase = $this->purchases->create($data, $request->user()->id);

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', "Pembelian {$purchase->purchase_number} berhasil disimpan.");
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['details.product', 'supplier', 'user']);

        return view('purchases.show', compact('purchase'));
    }

    public function receive(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->purchases->receive($purchase, $request->user()->id);

        return back()->with('success', 'Barang diterima dan stok sudah diperbarui.');
    }
}
