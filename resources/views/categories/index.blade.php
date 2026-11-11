@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
    <div class="page-header">
        <h2>Kategori Produk</h2>
        <a href="{{ route('categories.create') }}" class="btn btn-primary"><x-icon name="plus" :size="18" /> Tambah Kategori</a>
    </div>

    <form method="GET" class="filters">
        <div class="search">
            <x-icon name="search" :size="18" />
            <input type="text" name="q" class="input" placeholder="Cari kategori..." value="{{ request('q') }}">
        </div>
    </form>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr><th>Nama</th><th>Deskripsi</th><th class="center">Jumlah produk</th><th class="center">Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td class="strong">{{ $category->name }}</td>
                        <td class="muted">{{ $category->description ?: '-' }}</td>
                        <td class="center"><a href="{{ route('products.index', ['category' => $category->id]) }}" class="badge badge-purple">{{ $category->products_count }}</a></td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('categories.edit', $category) }}" class="icon-btn edit" title="Ubah"><x-icon name="edit" :size="18" /></a>
                                <x-delete-form :action="route('categories.destroy', $category)" message="Hapus kategori ini? Produk di dalamnya menjadi tanpa kategori." />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">Belum ada kategori.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-pagination :paginator="$categories" />
@endsection
