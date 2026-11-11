@extends('layouts.app')

@section('title', 'Pembelian')

@section('content')
    <div class="page-header">
        <h2>Pembelian Stok</h2>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary"><x-icon name="plus" :size="18" /> Pembelian Baru</a>
    </div>

    <form method="GET" class="filters">
        <div class="search">
            <x-icon name="search" :size="18" />
            <input type="text" name="q" class="input" placeholder="Cari nomor pembelian..." value="{{ request('q') }}">
        </div>
        <select name="status" class="input" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            @foreach (\App\Models\Purchase::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                <tr><th>Nomor</th><th>Tanggal</th><th>Supplier</th><th>Dibuat oleh</th><th class="center">Status</th><th class="right">Total</th><th class="center">Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($purchases as $p)
                    <tr>
                        <td class="mono">{{ $p->purchase_number }}</td>
                        <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $p->supplier?->name }}</td>
                        <td>{{ $p->user?->name }}</td>
                        <td class="center"><span class="badge {{ $p->status === 'received' ? 'badge-green' : 'badge-amber' }}">{{ $p->status_label }}</span></td>
                        <td class="right strong">{{ rupiah($p->total) }}</td>
                        <td><div class="row-actions"><a href="{{ route('purchases.show', $p) }}" class="icon-btn view" title="Detail"><x-icon name="eye" :size="18" /></a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">Belum ada pembelian.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-pagination :paginator="$purchases" />
@endsection
