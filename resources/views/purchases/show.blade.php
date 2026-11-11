@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
    <div class="page-header">
        <h2>{{ $purchase->purchase_number }}</h2>
        <div class="actions">
            <a href="{{ route('purchases.index') }}" class="btn btn-outline"><x-icon name="arrow-left" :size="16" /> Kembali</a>
            @if ($purchase->status === 'pending')
                <form method="POST" action="{{ route('purchases.receive', $purchase) }}" onsubmit="return confirm('Tandai barang sudah diterima? Stok akan bertambah.')">
                    @csrf
                    <button type="submit" class="btn btn-success"><x-icon name="check" :size="16" /> Terima Barang</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card card-pad" style="margin-bottom:20px">
        <dl class="detail-grid" style="margin:0">
            <div><dt>Tanggal</dt><dd>{{ $purchase->created_at->format('d/m/Y H:i') }}</dd></div>
            <div><dt>Supplier</dt><dd>{{ $purchase->supplier?->name }}</dd></div>
            <div><dt>Dibuat oleh</dt><dd>{{ $purchase->user?->name }}</dd></div>
            <div><dt>Status</dt><dd><span class="badge {{ $purchase->status === 'received' ? 'badge-green' : 'badge-amber' }}">{{ $purchase->status_label }}</span></dd></div>
        </dl>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr><th>Produk</th><th class="right">Harga beli</th><th class="center">Jumlah</th><th class="right">Subtotal</th></tr>
                </thead>
                <tbody>
                @foreach ($purchase->details as $d)
                    <tr>
                        <td>
                            <div>{{ $d->product?->name }}</div>
                            <div class="muted small mono">{{ $d->product?->sku }}</div>
                        </td>
                        <td class="right">{{ rupiah($d->purchase_price) }}</td>
                        <td class="center">{{ $d->quantity }}</td>
                        <td class="right strong">{{ rupiah($d->purchase_price * $d->quantity) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="totals" style="padding-bottom:16px">
            <div class="row grand"><span>Total</span><span>{{ rupiah($purchase->total) }}</span></div>
        </div>
    </div>
@endsection
