@extends('layouts.app')

@php $editing = $product->exists; @endphp

@section('title', $editing ? 'Ubah Produk' : 'Tambah Produk')

@section('content')
    <div class="page-header">
        <h2>{{ $editing ? 'Ubah Produk' : 'Tambah Produk' }}</h2>
        <a href="{{ route('products.index') }}" class="btn btn-outline"><x-icon name="arrow-left" :size="16" /> Kembali</a>
    </div>

    <div class="card card-pad" style="max-width:900px">
        <form method="POST" action="{{ $editing ? route('products.update', $product) : route('products.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="form-grid">
                <div class="full">
                    <label for="name">Nama produk *</label>
                    <input id="name" type="text" name="name" class="input" value="{{ old('name', $product->name) }}" required>
                </div>

                <div>
                    <label for="sku">SKU *</label>
                    <input id="sku" type="text" name="sku" class="input" value="{{ old('sku', $product->sku) }}" required>
                </div>
                <div>
                    <label for="barcode">Barcode</label>
                    <input id="barcode" type="text" name="barcode" class="input" value="{{ old('barcode', $product->barcode) }}">
                </div>

                <div>
                    <label for="category_id">Kategori</label>
                    <select id="category_id" name="category_id" class="input">
                        <option value="">- Tanpa kategori -</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) old('category_id', $product->category_id) === (string) $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="brand">Merek</label>
                    <input id="brand" type="text" name="brand" class="input" value="{{ old('brand', $product->brand) }}">
                </div>

                <div>
                    <label for="purchase_price">Harga beli (Rp) *</label>
                    <input id="purchase_price" type="number" step="1" min="0" name="purchase_price" class="input" value="{{ old('purchase_price', (int) $product->purchase_price) }}" required>
                </div>
                <div>
                    <label for="selling_price">Harga jual (Rp) *</label>
                    <input id="selling_price" type="number" step="1" min="0" name="selling_price" class="input" value="{{ old('selling_price', (int) $product->selling_price) }}" required>
                </div>

                <div>
                    <label for="stock">Stok *</label>
                    <input id="stock" type="number" step="1" min="0" name="stock" class="input" value="{{ old('stock', $product->stock) }}" required>
                    @if ($editing)
                        <div class="hint">Mengubah angka ini dicatat sebagai penyesuaian stok.</div>
                    @endif
                </div>
                <div>
                    <label for="min_stock">Stok minimum *</label>
                    <input id="min_stock" type="number" step="1" min="0" name="min_stock" class="input" value="{{ old('min_stock', $product->min_stock) }}" required>
                    <div class="hint">Peringatan muncul bila stok &le; angka ini.</div>
                </div>

                <div class="full">
                    <label class="check">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
                        Produk aktif (tampil di POS)
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('products.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
