@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
    <div class="page-header">
        <h2>Laporan Penjualan</h2>
        <a href="{{ route('reports.index', ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'export' => 'csv']) }}" class="btn btn-outline">
            <x-icon name="download" :size="16" /> Unduh CSV
        </a>
    </div>

    <form method="GET" class="filters">
        <div>
            <label for="from">Dari</label>
            <input id="from" type="date" name="from" class="input" style="height:42px" value="{{ $from->toDateString() }}">
        </div>
        <div>
            <label for="to">Sampai</label>
            <input id="to" type="date" name="to" class="input" style="height:42px" value="{{ $to->toDateString() }}">
        </div>
        <div style="align-self:flex-end">
            <button type="submit" class="btn btn-indigo" style="height:42px">Tampilkan</button>
        </div>
    </form>

    <div class="stats">
        <div class="card stat">
            <div class="ico green"><x-icon name="dollar" :size="22" /></div>
            <div><div class="label">Omzet</div><div class="value">{{ rupiah($summary['revenue']) }}</div></div>
        </div>
        <div class="card stat">
            <div class="ico"><x-icon name="receipt" :size="22" /></div>
            <div><div class="label">Transaksi</div><div class="value">{{ number_format($summary['transactions'], 0, ',', '.') }}</div></div>
        </div>
        <div class="card stat">
            <div class="ico blue"><x-icon name="box" :size="22" /></div>
            <div><div class="label">Item terjual</div><div class="value">{{ number_format($summary['items_sold'], 0, ',', '.') }}</div></div>
        </div>
        <div class="card stat">
            <div class="ico amber"><x-icon name="chart" :size="22" /></div>
            <div><div class="label">Estimasi laba</div><div class="value">{{ rupiah($summary['profit']) }}</div></div>
        </div>
    </div>
    <p class="hint" style="margin:-8px 0 20px">
        Estimasi laba = omzet &minus; (jumlah terjual &times; harga beli produk saat ini).
        Pembelian stok diterima pada periode ini: <strong>{{ rupiah($summary['purchases']) }}</strong> &middot;
        Total diskon: <strong>{{ rupiah($summary['discount']) }}</strong>.
    </p>

    <div class="grid-2">
        <div class="card card-pad">
            <div class="card-title">Penjualan per hari</div>
            <div class="chart-box"><canvas id="dailyChart"></canvas></div>
        </div>
        <div class="card card-pad">
            <div class="card-title">Metode pembayaran</div>
            @forelse ($byPayment as $row)
                <div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f1f1f4">
                    <div>
                        <div class="strong small">{{ \App\Models\Sale::PAYMENT_METHODS[$row->payment_method] ?? $row->payment_method }}</div>
                        <div class="muted small">{{ $row->trx }} transaksi</div>
                    </div>
                    <div class="strong">{{ rupiah($row->total) }}</div>
                </div>
            @empty
                <p class="muted small">Belum ada data.</p>
            @endforelse
        </div>
    </div>

    <div class="grid-eq">
        <div class="card">
            <div style="padding:20px 24px 0"><div class="card-title" style="margin:0">Produk terlaris (omzet)</div></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Produk</th><th class="center">Terjual</th><th class="right">Omzet</th></tr></thead>
                    <tbody>
                    @forelse ($topProducts as $row)
                        <tr>
                            <td><div>{{ $row->name }}</div><div class="muted small mono">{{ $row->sku }}</div></td>
                            <td class="center">{{ $row->qty }}</td>
                            <td class="right strong">{{ rupiah($row->revenue) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div style="padding:20px 24px 0"><div class="card-title" style="margin:0">Rincian per hari</div></div>
            <div class="table-wrap" style="max-height:420px;overflow-y:auto">
                <table class="table">
                    <thead><tr><th>Tanggal</th><th class="center">Transaksi</th><th class="right">Total</th></tr></thead>
                    <tbody>
                    @forelse ($daily->reverse() as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->day)->format('d/m/Y') }}</td>
                            <td class="center">{{ $row->trx }}</td>
                            <td class="right strong">{{ rupiah($row->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            if (typeof Chart === 'undefined') return;
            var rows = @json($daily->map(fn ($r) => ['day' => $r->day, 'total' => (float) $r->total])->values());
            new Chart(document.getElementById('dailyChart'), {
                type: 'line',
                data: {
                    labels: rows.map(function (r) { return r.day.slice(8, 10) + '/' + r.day.slice(5, 7); }),
                    datasets: [{
                        label: 'Penjualan (Rp)',
                        data: rows.map(function (r) { return r.total; }),
                        borderColor: '#9333ea',
                        backgroundColor: 'rgba(147,51,234,.12)',
                        fill: true,
                        tension: .3,
                        pointRadius: 3,
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
