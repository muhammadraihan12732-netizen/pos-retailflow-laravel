@extends('layouts.app')

@section('title', 'Supplier')

@section('content')
    <div class="page-header">
        <h2>Data Supplier</h2>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary"><x-icon name="plus" :size="18" /> Tambah Supplier</a>
    </div>

    <form method="GET" class="filters">
        <div class="search">
            <x-icon name="search" :size="18" />
            <input type="text" name="q" class="input" placeholder="Cari nama atau telepon..." value="{{ request('q') }}">
        </div>
    </form>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr><th>Nama</th><th>Telepon</th><th>Alamat</th><th class="center">Pembelian</th><th class="center">Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td class="strong">{{ $supplier->name }}</td>
                        <td>{{ $supplier->phone ?: '-' }}</td>
                        <td class="muted">{{ $supplier->address ?: '-' }}</td>
                        <td class="center">{{ $supplier->purchases_count }}</td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="icon-btn edit" title="Ubah"><x-icon name="edit" :size="18" /></a>
                                <x-delete-form :action="route('suppliers.destroy', $supplier)" message="Hapus supplier ini?" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Belum ada supplier.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-pagination :paginator="$suppliers" />
@endsection
