@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
    <div class="page-header">
        <h2>{{ $sale->invoice_number }}</h2>
        <div class="actions">
            <a href="{{ route('sales.index') }}" class="btn btn-outline"><x-icon name="arrow-left" :size="16" /> Riwayat</a>
            <a href="{{ route('sales.create') }}" class="btn btn-outline"><x-icon name="cart" :size="16" /> Transaksi Baru</a>
            <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="btn btn-primary"><x-icon name="printer" :size="16" /> Cetak Struk</a>
        </div>
    </div>

    <div class="card card-pad" style="margin-bottom:20px">
        <dl class="detail-grid" style="margin:0">
            <div><dt>Waktu</dt><dd>{{ $sale->created_at->format('d/m/Y H:i') }}</dd></div>
            <div><dt>Kasir</dt><dd>{{ $sale->user?->name ?? '-' }}</dd></div>
            <div><dt>Pelanggan</dt><dd>{{ $sale->customer?->name ?? 'Umum' }}</dd></div>
            <div><dt>Metode pembayaran</dt><dd>{{ $sale->payment_label }}</dd></div>
            <div><dt>Dibayar</dt><dd>{{ rupiah($sale->paid) }}</dd></div>
            <div><dt>Kembalian</dt><dd>{{ rupiah($sale->change) }}</dd></div>
        </dl>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr><th>Produk</th><th class="right">Harga</th><th class="center">Jumlah</th><th class="right">Subtotal</th></tr>
                </thead>
                <tbody>
                @foreach ($sale->details as $d)
                    <tr>
                        <td>
                            <div>{{ $d->product?->name ?? 'Produk dihapus' }}</div>
                            <div class="muted small mono">{{ $d->product?->sku }}</div>
                        </td>
                        <td class="right">{{ rupiah($d->price) }}</td>
                        <td class="center">{{ $d->quantity }}</td>
                        <td class="right strong">{{ rupiah($d->subtotal) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="totals" style="padding-bottom:16px">
            <div class="row"><span>Subtotal</span><span>{{ rupiah($sale->subtotal) }}</span></div>
            <div class="row"><span>Diskon</span><span>- {{ rupiah($sale->discount) }}</span></div>
            <div class="row grand"><span>Total</span><span>{{ rupiah($sale->total) }}</span></div>
        </div>
    </div>
@endsection
