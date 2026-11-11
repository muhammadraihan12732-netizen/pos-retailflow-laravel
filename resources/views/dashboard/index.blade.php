@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="stats">
        <div class="card stat">
            <div class="ico green"><x-icon name="dollar" :size="22" /></div>
            <div><div class="label">Penjualan hari ini</div><div class="value">{{ rupiah($stats['sales_today']) }}</div></div>
        </div>
        <div class="card stat">
            <div class="ico"><x-icon name="receipt" :size="22" /></div>
            <div><div class="label">Transaksi hari ini</div><div class="value">{{ number_format($stats['trx_today'], 0, ',', '.') }}</div></div>
        </div>
        <div class="card stat">
            <div class="ico blue"><x-icon name="box" :size="22" /></div>
            <div><div class="label">Total produk</div><div class="value">{{ number_format($stats['products'], 0, ',', '.') }}</div></div>
        </div>
        <div class="card stat">
            <div class="ico amber"><x-icon name="alert" :size="22" /></div>
            <div><div class="label">Stok menipis</div><div class="value">{{ $stats['low_stock'] }}</div></div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card card-pad">
            <div class="card-title">Penjualan 7 hari terakhir</div>
            <div class="chart-box"><canvas id="salesChart"></canvas></div>
        </div>

        <div class="card card-pad">
            <div class="card-title">Peringatan stok</div>
            @forelse ($lowStockProducts as $p)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f1f4">
                    <div>
                        <div class="strong small">{{ $p->name }}</div>
                        <div class="muted small">{{ $p->category?->name ?? 'Tanpa kategori' }}</div>
                    </div>
                    <span class="badge {{ $p->stock <= 0 ? 'badge-red' : 'badge-amber' }}">{{ $p->stock <= 0 ? 'Habis' : $p->stock.' tersisa' }}</span>
                </div>
            @empty
                <p class="muted small">Semua stok aman.</p>
            @endforelse

            @if (auth()->user()->hasRole('admin', 'manager') && $lowStockProducts->isNotEmpty())
                <a href="{{ route('products.index', ['stock' => 'low']) }}" class="btn btn-outline btn-sm" style="margin-top:12px">Lihat semua</a>
            @endif
        </div>
    </div>

    <div class="card">
        <div style="padding:20px 24px 0"><div class="card-title" style="margin:0">Transaksi terbaru</div></div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr>
                    <th>Invoice</th><th>Waktu</th><th>Pelanggan</th><th>Kasir</th><th>Metode</th><th class="right">Total</th><th class="center">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($recentSales as $sale)
                    <tr>
                        <td class="mono">{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->customer?->name ?? 'Umum' }}</td>
                        <td>{{ $sale->user?->name }}</td>
                        <td><span class="badge badge-green">{{ $sale->payment_label }}</span></td>
                        <td class="right strong">{{ rupiah($sale->total) }}</td>
                        <td class="center">
                            <a href="{{ route('sales.show', $sale) }}" class="icon-btn view" title="Detail"><x-icon name="eye" :size="18" /></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">Belum ada transaksi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            if (typeof Chart === 'undefined') return;
            var data = @json($chart);
            new Chart(document.getElementById('salesChart'), {
                type: 'bar',
                data: {
                    labels: data.map(function (d) { return d.label; }),
                    datasets: [{
                        label: 'Penjualan (Rp)',
                        data: data.map(function (d) { return d.value; }),
                        backgroundColor: '#9333ea',
                        borderRadius: 6,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: function (v) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(v); } } }
                    }
                }
            });
        })();
    </script>
@endpush
