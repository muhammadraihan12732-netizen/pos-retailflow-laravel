@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content')
    @php [$label, $class] = $product->statusBadge(); @endphp

    <div class="page-header">
        <h2>{{ $product->name }}</h2>
        <div class="actions">
            <a href="{{ route('products.index') }}" class="btn btn-outline"><x-icon name="arrow-left" :size="16" /> Kembali</a>
            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary"><x-icon name="edit" :size="16" /> Ubah</a>
        </div>
    </div>

    <div class="card card-pad" style="margin-bottom:20px">
        <dl class="detail-grid" style="margin:0">
            <div><dt>SKU</dt><dd class="mono">{{ $product->sku }}</dd></div>
            <div><dt>Barcode</dt><dd class="mono">{{ $product->barcode ?: '-' }}</dd></div>
            <div><dt>Status</dt><dd><span class="badge {{ $class }}">{{ $label }}</span></dd></div>
            <div><dt>Kategori</dt><dd>{{ $product->category?->name ?? '-' }}</dd></div>
            <div><dt>Merek</dt><dd>{{ $product->brand ?: '-' }}</dd></div>
            <div><dt>Stok / minimum</dt><dd>{{ $product->stock }} / {{ $product->min_stock }}</dd></div>
            <div><dt>Harga beli</dt><dd>{{ rupiah($product->purchase_price) }}</dd></div>
            <div><dt>Harga jual</dt><dd>{{ rupiah($product->selling_price) }}</dd></div>
            <div><dt>Total terjual</dt><dd>{{ number_format($soldCount, 0, ',', '.') }} unit</dd></div>
        </dl>
    </div>

    <div class="card">
        <div style="padding:20px 24px 0"><div class="card-title" style="margin:0">Riwayat pergerakan stok (20 terakhir)</div></div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr><th>Waktu</th><th>Jenis</th><th class="right">Jumlah</th><th>Catatan</th><th>Oleh</th></tr>
                </thead>
                <tbody>
                @forelse ($movements as $m)
                    @php
                        $qty = $m->type === 'out' ? -$m->quantity : $m->quantity;
                        $badge = ['in' => 'badge-green', 'out' => 'badge-red', 'adjustment' => 'badge-amber'][$m->type] ?? 'badge-gray';
                    @endphp
                    <tr>
                        <td>{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                        <td><span class="badge {{ $badge }}">{{ \App\Models\StockMovement::TYPES[$m->type] ?? $m->type }}</span></td>
                        <td class="right strong">{{ $qty > 0 ? '+' : '' }}{{ $qty }}</td>
                        <td>{{ $m->notes ?: '-' }}</td>
                        <td>{{ $m->user?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Belum ada pergerakan stok.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
