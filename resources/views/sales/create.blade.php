@extends('layouts.app')

@section('title', 'POS Terminal')

@section('content')
    <form method="POST" action="{{ route('sales.store') }}" id="posForm" class="pos" autocomplete="off">
        @csrf

        {{-- Kiri: daftar produk --}}
        <section class="card pos-products">
            <div class="pos-tools">
                <div class="search">
                    <x-icon name="search" :size="18" />
                    <input type="text" id="posSearch" class="input" placeholder="Cari produk atau scan barcode lalu tekan Enter...">
                </div>
                <select id="posCategory" class="input">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pos-grid" id="posGrid"></div>
        </section>

        {{-- Kanan: keranjang & pembayaran --}}
        <aside class="card pos-cart">
            <h3>
                <span>Keranjang (<span id="cartCount">0</span>)</span>
                <button type="button" id="clearCart" class="btn btn-outline btn-sm">Kosongkan</button>
            </h3>

            <div class="pos-warning" id="posWarning"></div>
            <div class="cart-items" id="cartItems"></div>
            <div id="cartInputs"></div>

            <div class="cart-summary">
                <div class="row"><span>Subtotal</span><strong id="subtotalText">Rp 0</strong></div>

                <div>
                    <label for="customer_id">Pelanggan</label>
                    <select id="customer_id" name="customer_id" class="input">
                        <option value="">Pelanggan umum</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" @selected((string) old('customer_id') === (string) $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="discount">Diskon (Rp)</label>
                    <input type="number" id="discount" name="discount" class="input" min="0" step="1" value="{{ old('discount', 0) }}">
                </div>

                <div class="row total"><span>Total</span><span id="totalText">Rp 0</span></div>

                <div>
                    <label for="paymentMethod">Metode pembayaran</label>
                    <select id="paymentMethod" name="payment_method" class="input">
                        @foreach (\App\Models\Sale::PAYMENT_METHODS as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method', 'cash') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="paidWrap">
                    <label for="paid">Jumlah dibayar (Rp)</label>
                    <input type="number" id="paid" name="paid" class="input" min="0" step="1" value="{{ old('paid') }}">
                    <div class="quick-cash" id="quickCash"></div>
                </div>

                <div class="row"><span id="changeLabel">Kembalian</span><strong id="changeText">Rp 0</strong></div>

                <button type="submit" id="payBtn" class="btn btn-primary btn-block" disabled>Bayar &amp; Simpan Transaksi</button>
            </div>
        </aside>
    </form>
@endsection

@push('scripts')
    <script>
        window.POS_PRODUCTS = @json($products);
        window.POS_OLD_ITEMS = @json(old('items', []));
    </script>
    <script src="{{ asset('js/pos.js') }}"></script>
@endpush
