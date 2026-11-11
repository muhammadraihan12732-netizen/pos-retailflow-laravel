<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller tipis: hanya menerima request dan mengembalikan response.
 * Seluruh logika transaksi ada di SaleService.
 */
class SaleController extends Controller
{
    /** Riwayat transaksi. */
    public function index(Request $request)
    {
        $sales = Sale::with(['customer', 'user'])
            ->when($request->q, fn ($q, $s) => $q->where('invoice_number', 'like', "%{$s}%"))
            ->when($request->payment_method, fn ($q, $m) => $q->where('payment_method', $m))
            ->when($request->from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    /** Halaman POS Terminal. */
    public function create()
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'category_id', 'sku', 'barcode', 'name', 'brand', 'selling_price', 'stock'])
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'category_id' => $p->category_id,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'name' => $p->name,
                'brand' => $p->brand,
                'price' => (float) $p->selling_price,
                'stock' => $p->stock,
            ]);

        return view('sales.create', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreSaleRequest $request, SaleService $sales): RedirectResponse
    {
        $sale = $sales->create($request->validated(), $request->user()->id);

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', "Transaksi {$sale->invoice_number} berhasil disimpan.");
    }

    public function show(Sale $sale)
    {
        $sale->load(['details.product', 'customer', 'user']);

        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['details.product', 'customer', 'user']);

        return view('sales.receipt', compact('sale'));
    }
}
