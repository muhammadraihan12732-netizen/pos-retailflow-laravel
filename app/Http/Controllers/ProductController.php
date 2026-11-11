<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(private StockService $stock)
    {
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100], true) ? (int) $request->per_page : 25;

        $products = Product::with('category')
            ->search($request->q)
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->category))
            ->when($request->stock === 'low', fn ($q) => $q->lowStock()->where('stock', '>', 0))
            ->when($request->stock === 'out', fn ($q) => $q->where('stock', '<=', 0))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'perPage' => $perPage,
        ]);
    }

    public function create()
    {
        return view('products.form', [
            'product' => new Product(['min_stock' => 5, 'is_active' => true, 'stock' => 0]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();
            $initialStock = (int) $data['stock'];
            $data['stock'] = 0;

            $product = Product::create($data);

            if ($initialStock > 0) {
                $this->stock->increase($product, $initialStock, $request->user()->id, 'Stok awal');
            }
        });

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load('category');

        return view('products.show', [
            'product' => $product,
            'movements' => $product->stockMovements()->with('user')->latest('id')->limit(20)->get(),
            'soldCount' => (int) $product->saleDetails()->sum('quantity'),
        ]);
    }

    public function edit(Product $product)
    {
        return view('products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product) {
            $data = $request->validated();
            $newStock = (int) $data['stock'];
            unset($data['stock']);

            $product->update($data);
            $this->stock->adjust($product, $newStock, $request->user()->id, 'Penyesuaian manual');
        });

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->saleDetails()->exists() || $product->purchaseDetails()->exists()) {
            return back()->with('error', 'Produk sudah punya riwayat transaksi dan tidak bisa dihapus. Nonaktifkan saja.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    /** Aksi massal untuk produk yang dicentang di tabel. */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
            'status_action' => ['nullable', 'in:none,activate,deactivate'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ], [
            'ids.required' => 'Centang minimal satu produk terlebih dahulu.',
            'ids.min' => 'Centang minimal satu produk terlebih dahulu.',
        ]);

        $changes = [];

        if (($data['status_action'] ?? 'none') === 'activate') {
            $changes['is_active'] = true;
        } elseif (($data['status_action'] ?? 'none') === 'deactivate') {
            $changes['is_active'] = false;
        }

        if (! empty($data['category_id'])) {
            $changes['category_id'] = $data['category_id'];
        }

        if (! $changes) {
            return back()->with('error', 'Pilih perubahan yang ingin diterapkan (status atau kategori).');
        }

        $count = Product::whereIn('id', $data['ids'])->update($changes);

        return back()->with('success', "{$count} produk berhasil diperbarui.");
    }
}
