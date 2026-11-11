<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::withCount('purchases')
            ->when($request->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.form', ['supplier' => new Supplier]);
    }

    public function store(Request $request): RedirectResponse
    {
        Supplier::create($this->validated($request));

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->validated($request));

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'Supplier sudah punya riwayat pembelian dan tidak bisa dihapus.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
