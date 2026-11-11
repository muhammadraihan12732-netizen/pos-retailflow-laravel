@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
    <div class="page-header">
        <h2>Riwayat Transaksi</h2>
        <a href="{{ route('sales.create') }}" class="btn btn-primary"><x-icon name="cart" :size="18" /> Transaksi Baru</a>
    </div>

    <form method="GET" class="filters">
        <div class="search">
            <x-icon name="search" :size="18" />
            <input type="text" name="q" class="input" placeholder="Cari nomor invoice..." value="{{ request('q') }}">
        </div>
        <select name="payment_method" class="input" onchange="this.form.submit()">
            <option value="">Semua Metode</option>
            @foreach (\App\Models\Sale::PAYMENT_METHODS as $value => $label)
                <option value="{{ $value }}" @selected(request('payment_method') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="from" class="input" style="width:auto;height:42px" value="{{ request('from') }}" onchange="this.form.submit()" title="Dari tanggal">
        <input type="date" name="to" class="input" style="width:auto;height:42px" value="{{ request('to') }}" onchange="this.form.submit()" title="Sampai tanggal">
    </form>

    <div class="list-meta">
        <span>
            @if ($sales->total())
                Menampilkan {{ $sales->firstItem() }}-{{ $sales->lastItem() }} dari {{ $sales->total() }} transaksi
            @else
                Tidak ada transaksi
            @endif
        </span>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Invoice</th><th>Waktu</th><th>Pelanggan</th><th>Kasir</th><th class="center">Metode</th><th class="right">Total</th><th class="center">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($sales as $sale)
                    <tr>
                        <td class="mono">{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->customer?->name ?? 'Umum' }}</td>
                        <td>{{ $sale->user?->name }}</td>
                        <td class="center"><span class="badge badge-green">{{ $sale->payment_label }}</span></td>
                        <td class="right strong">{{ rupiah($sale->total) }}</td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('sales.show', $sale) }}" class="icon-btn view" title="Detail"><x-icon name="eye" :size="18" /></a>
                                <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="icon-btn edit" title="Cetak struk"><x-icon name="printer" :size="18" /></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">Belum ada transaksi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-pagination :paginator="$sales" />
@endsection
