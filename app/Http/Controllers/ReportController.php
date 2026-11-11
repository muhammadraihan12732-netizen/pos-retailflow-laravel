<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = Carbon::parse($request->input('from', now()->subDays(29)->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();

        if ($request->query('export') === 'csv') {
            return $this->exportCsv($from, $to);
        }

        $sales = Sale::whereBetween('created_at', [$from, $to]);

        $summary = [
            'revenue' => (clone $sales)->sum('total'),
            'transactions' => (clone $sales)->count(),
            'discount' => (clone $sales)->sum('discount'),
            'items_sold' => (int) SaleDetail::whereHas('sale', fn ($q) => $q->whereBetween('created_at', [$from, $to]))->sum('quantity'),
            'purchases' => Purchase::where('status', Purchase::STATUS_RECEIVED)->whereBetween('created_at', [$from, $to])->sum('total'),
        ];

        // Estimasi laba = omzet - (qty x harga beli produk saat ini).
        $cost = SaleDetail::join('sales', 'sales.id', '=', 'sale_details.sale_id')
            ->join('products', 'products.id', '=', 'sale_details.product_id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->selectRaw('SUM(sale_details.quantity * products.purchase_price) as cost')
            ->value('cost');
        $summary['profit'] = $summary['revenue'] - (float) $cost;

        $daily = Sale::selectRaw('DATE(created_at) as day, COUNT(*) as trx, SUM(total) as total')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $topProducts = SaleDetail::join('sales', 'sales.id', '=', 'sale_details.sale_id')
            ->join('products', 'products.id', '=', 'sale_details.product_id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->selectRaw('products.name, products.sku, SUM(sale_details.quantity) as qty, SUM(sale_details.subtotal) as revenue')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $byPayment = Sale::selectRaw('payment_method, COUNT(*) as trx, SUM(total) as total')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('payment_method')
            ->get();

        return view('reports.index', [
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
            'daily' => $daily,
            'topProducts' => $topProducts,
            'byPayment' => $byPayment,
        ]);
    }

    private function exportCsv(Carbon $from, Carbon $to): StreamedResponse
    {
        $filename = 'laporan-penjualan-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($from, $to) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['Invoice', 'Tanggal', 'Kasir', 'Pelanggan', 'Metode', 'Subtotal', 'Diskon', 'Total']);

            Sale::with(['user', 'customer'])
                ->whereBetween('created_at', [$from, $to])
                ->orderBy('created_at')
                ->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $s) {
                        fputcsv($out, [
                            $s->invoice_number,
                            $s->created_at->format('Y-m-d H:i'),
                            $s->user?->name,
                            $s->customer?->name,
                            $s->payment_label,
                            (float) $s->subtotal,
                            (float) $s->discount,
                            (float) $s->total,
                        ]);
                    }
                });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
