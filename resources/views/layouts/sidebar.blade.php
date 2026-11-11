@php
    $user = auth()->user();
    $isManager = $user->hasRole('admin', 'manager');
    $isAdmin = $user->hasRole('admin');
@endphp
<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-logo">RetailFlow</div>
    </div>

    <nav class="nav">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <x-icon name="home" /> Dashboard
        </a>
        <a href="{{ route('sales.create') }}" class="{{ request()->routeIs('sales.create') ? 'active' : '' }}">
            <x-icon name="cart" /> POS Terminal
        </a>

        @if ($isManager)
            <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                <x-icon name="box" /> Produk
            </a>
            <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <x-icon name="tag" /> Kategori
            </a>
            <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <x-icon name="truck" /> Supplier
            </a>
        @endif

        <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <x-icon name="users" /> Pelanggan
        </a>

        @if ($isManager)
            <a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                <x-icon name="download" /> Pembelian
            </a>
        @endif

        <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.index', 'sales.show', 'sales.receipt') ? 'active' : '' }}">
            <x-icon name="receipt" /> Transaksi
        </a>

        @if ($isManager)
            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <x-icon name="chart" /> Laporan
            </a>
        @endif

        @if ($isAdmin)
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <x-icon name="user" /> Pengguna
            </a>
        @endif
    </nav>

    <div class="sidebar-foot">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn"><x-icon name="logout" /> Keluar</button>
        </form>
        <div class="copy">© {{ date('Y') }} {{ config('app.name') }}<br>Hak cipta dilindungi</div>
    </div>
</aside>
