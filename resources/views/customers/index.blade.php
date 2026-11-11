@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content')
    <div class="page-header">
        <h2>Data Pelanggan</h2>
        <a href="{{ route('customers.create') }}" class="btn btn-primary"><x-icon name="plus" :size="18" /> Tambah Pelanggan</a>
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
                <tr><th>Nama</th><th>Telepon</th><th>Alamat</th><th class="center">Transaksi</th><th class="right">Total belanja</th><th class="center">Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($customers as $customer)
                    <tr>
                        <td class="strong">{{ $customer->name }}</td>
                        <td>{{ $customer->phone ?: '-' }}</td>
                        <td class="muted">{{ $customer->address ?: '-' }}</td>
                        <td class="center">{{ $customer->sales_count }}</td>
                        <td class="right">{{ rupiah($customer->sales_sum_total) }}</td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('customers.edit', $customer) }}" class="icon-btn edit" title="Ubah"><x-icon name="edit" :size="18" /></a>
                                @if (auth()->user()->hasRole('admin', 'manager'))
                                    <x-delete-form :action="route('customers.destroy', $customer)" message="Hapus pelanggan ini? Riwayat transaksinya tetap tersimpan." />
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">Belum ada pelanggan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-pagination :paginator="$customers" />
@endsection
