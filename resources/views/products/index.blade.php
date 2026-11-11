@extends('layouts.app')

@section('title', 'Produk')

@section('content')
    <div class="page-header">
        <h2>Manajemen Produk</h2>
        <div class="actions">
            <a href="{{ route('products.create') }}" class="btn btn-primary"><x-icon name="plus" :size="18" /> Tambah Produk</a>
        </div>
    </div>

    {{-- Filter (GET) --}}
    <form method="GET" action="{{ route('products.index') }}" id="filterForm" class="filters">
        <div class="search">
            <x-icon name="search" :size="18" />
            <input type="text" name="q" class="input" placeholder="Cari nama, SKU, barcode, merek..." value="{{ request('q') }}">
        </div>
        <select name="status" class="input" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="active" @selected(request('status') === 'active')>Aktif</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
        </select>
        <select name="category" class="input" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string) request('category') === (string) $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="stock" class="input" onchange="this.form.submit()">
            <option value="">Semua Stok</option>
            <option value="low" @selected(request('stock') === 'low')>Stok Menipis</option>
            <option value="out" @selected(request('stock') === 'out')>Stok Habis</option>
        </select>
    </form>

    <div class="list-meta">
        <span>
            @if ($products->total())
                Menampilkan {{ $products->firstItem() }}-{{ $products->lastItem() }} dari {{ $products->total() }} produk
            @else
                Tidak ada produk
            @endif
        </span>
        <label class="per-page" style="margin:0">
            Per halaman
            <select name="per_page" form="filterForm" class="input" onchange="this.form.submit()">
                @foreach ([10, 25, 50, 100] as $n)
                    <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                @endforeach
            </select>
        </label>
    </div>

    {{-- Tabel + aksi massal (POST) --}}
    <form method="POST" action="{{ route('products.bulk') }}">
        @csrf

        <div class="bulk-bar">
            <div class="field">
                <label for="status_action">Ubah Status</label>
                <select id="status_action" name="status_action" class="input">
                    <option value="none">Tidak ada perubahan</option>
                    <option value="activate">Aktifkan</option>
                    <option value="deactivate">Nonaktifkan</option>
                </select>
            </div>
            <div class="field" style="min-width:260px">
                <label for="bulk_category">Kategori</label>
                <select id="bulk_category" name="category_id" class="input">
                    <option value="">Tidak ada perubahan</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-indigo">Terapkan ke yang dipilih</button>
            <span class="selected">Dipilih: <strong id="selectedCount">0</strong></span>
        </div>

        <div class="card">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th style="width:40px"><input type="checkbox" id="checkAll" aria-label="Pilih semua"></th>
                        <th>Produk</th>
                        <th>Merek</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th class="center">Stok</th>
                        <th class="center">Status</th>
                        <th class="center">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($products as $product)
                        @php [$label, $class] = $product->statusBadge(); @endphp
                        <tr>
                            <td><input type="checkbox" class="row-check" name="ids[]" value="{{ $product->id }}"></td>
                            <td>
                                <div>{{ $product->name }}</div>
                                <div class="muted small mono">{{ $product->sku }}</div>
                            </td>
                            <td>{{ $product->brand ?: '-' }}</td>
                            <td>{{ $product->category?->name ?? '-' }}</td>
                            <td>{{ rupiah($product->selling_price) }}</td>
                            <td class="center">{{ $product->stock }}</td>
                            <td class="center"><span class="badge {{ $class }}">{{ $label }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('products.edit', $product) }}" class="icon-btn edit" title="Ubah"><x-icon name="edit" :size="18" /></a>
                                    <a href="{{ route('products.show', $product) }}" class="icon-btn view" title="Detail"><x-icon name="eye" :size="18" /></a>
                                    <button type="submit" form="delete-{{ $product->id }}" class="icon-btn danger" title="Hapus"
                                            onclick="return confirm('Hapus produk {{ addslashes($product->name) }}?')"><x-icon name="trash" :size="18" /></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty">Produk tidak ditemukan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    {{-- Form hapus ditaruh di luar form massal (form tidak boleh bersarang) --}}
    @foreach ($products as $product)
        <form id="delete-{{ $product->id }}" method="POST" action="{{ route('products.destroy', $product) }}" style="display:none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    <x-pagination :paginator="$products" />
@endsection
