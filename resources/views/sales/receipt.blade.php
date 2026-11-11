<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $sale->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f4f6; font-family: "Courier New", ui-monospace, monospace; font-size: 12px; color: #000; }
        .toolbar { text-align: center; padding: 12px; }
        .toolbar button, .toolbar a { font: inherit; font-family: system-ui, sans-serif; padding: 8px 16px; border-radius: 8px; border: 1px solid #d1d5db; background: #fff; cursor: pointer; text-decoration: none; color: #111; margin: 0 4px; }
        .toolbar .primary { background: #9333ea; color: #fff; border-color: #9333ea; }
        .receipt { width: 80mm; max-width: 100%; margin: 0 auto 24px; background: #fff; padding: 12px 10px; }
        .center { text-align: center; }
        .title { font-size: 15px; font-weight: 700; }
        hr { border: 0; border-top: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 1px 0; }
        .r { text-align: right; }
        .b { font-weight: 700; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .receipt { width: 80mm; margin: 0; padding: 0; }
            @page { margin: 4mm; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <button class="primary" onclick="window.print()">Cetak</button>
    <a href="{{ route('sales.show', $sale) }}">Kembali</a>
    <a href="{{ route('sales.create') }}">Transaksi baru</a>
</div>

<div class="receipt">
    <div class="center">
        <div class="title">{{ config('retail.store_name') }}</div>
        @if (config('retail.store_address')) <div>{{ config('retail.store_address') }}</div> @endif
        @if (config('retail.store_phone')) <div>Telp: {{ config('retail.store_phone') }}</div> @endif
    </div>
    <hr>
    <table>
        <tr><td>No</td><td class="r">{{ $sale->invoice_number }}</td></tr>
        <tr><td>Tanggal</td><td class="r">{{ $sale->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Kasir</td><td class="r">{{ $sale->user?->name }}</td></tr>
        <tr><td>Pelanggan</td><td class="r">{{ $sale->customer?->name ?? 'Umum' }}</td></tr>
    </table>
    <hr>
    <table>
        @foreach ($sale->details as $d)
            <tr><td colspan="2">{{ $d->product?->name ?? 'Produk dihapus' }}</td></tr>
            <tr>
                <td>{{ $d->quantity }} x {{ number_format($d->price, 0, ',', '.') }}</td>
                <td class="r">{{ number_format($d->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>
    <hr>
    <table>
        <tr><td>Subtotal</td><td class="r">{{ number_format($sale->subtotal, 0, ',', '.') }}</td></tr>
        @if ($sale->discount > 0)
            <tr><td>Diskon</td><td class="r">-{{ number_format($sale->discount, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="b"><td>TOTAL</td><td class="r">{{ number_format($sale->total, 0, ',', '.') }}</td></tr>
        <tr><td>Bayar ({{ $sale->payment_label }})</td><td class="r">{{ number_format($sale->paid, 0, ',', '.') }}</td></tr>
        <tr><td>Kembali</td><td class="r">{{ number_format($sale->change, 0, ',', '.') }}</td></tr>
    </table>
    <hr>
    <div class="center">{{ config('retail.receipt_footer') }}</div>
</div>

@if (request()->boolean('print'))
    <script>window.addEventListener('load', function () { window.print(); });</script>
@endif
</body>
</html>
