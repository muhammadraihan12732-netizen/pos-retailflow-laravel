<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'sales_today' => Sale::whereDate('created_at', today())->sum('total'),
            'trx_today' => Sale::whereDate('created_at', today())->count(),
            'products' => Product::count(),
            'low_stock' => Product::where('is_active', true)->lowStock()->count(),
        ];

        // Penjualan 7 hari terakhir (hari tanpa transaksi tetap muncul sebagai 0).
        $from = now()->subDays(6)->startOfDay();
        $daily = Sale::selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->where('created_at', '>=', $from)
            ->groupBy('day')
            ->pluck('total', 'day');

        $chart = collect(range(0, 6))->map(function ($i) use ($from, $daily) {
            $date = $from->copy()->addDays($i);

            return [
                'label' => $date->format('d M'),
                'value' => (float) ($daily[$date->toDateString()] ?? 0),
            ];
        });

        $lowStockProducts = Product::with('category')
            ->where('is_active', true)
            ->lowStock()
            ->orderBy('stock')
            ->limit(8)
            ->get();

        $recentSales = Sale::with(['customer', 'user'])->latest()->limit(8)->get();

        return view('dashboard.index', compact('stats', 'chart', 'lowStockProducts', 'recentSales'));
    }
}
