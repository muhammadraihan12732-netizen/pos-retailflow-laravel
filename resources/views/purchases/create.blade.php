@extends('layouts.app')

@section('title', 'Pembelian Baru')

@section('content')
    <div class="page-header">
        <h2>Pembelian Baru</h2>
        <a href="{{ route('purchases.index') }}" class="btn btn-outline"><x-icon name="arrow-left" :size="16" /> Kembali</a>
    </div>

    <form method="POST" action="{{ route('purchases.store') }}" class="card card-pad" id="purchaseForm">
        @csrf

        <div class="form-grid" style="margin-bottom:20px">
            <div>
                <label for="supplier_id">Supplier *</label>
                <select id="supplier_id" name="supplier_id" class="input" required>
                    <option value="">- Pilih supplier -</option>
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->id }}" @selected((string) old('supplier_id') === (string) $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status">Status *</label>
                <select id="status" name="status" class="input" required>
                    <option value="received" @selected(old('status', 'received') === 'received')>Diterima (stok langsung bertambah)</option>
                    <option value="pending" @selected(old('status') === 'pending')>Menunggu (stok belum bertambah)</option>
                </select>
            </div>
        </div>

        <div class="item-row item-head">
            <span>Produk</span><span>Jumlah</span><span>Harga beli (Rp)</span><span>Subtotal</span><span></span>
        </div>
        <div id="rows"></div>

        <button type="button" class="btn btn-outline btn-sm" id="addRow"><x-icon name="plus" :size="14" /> Tambah baris</button>

        <div class="totals" style="padding-right:0">
            <div class="row grand"><span>Total</span><span id="grandTotal">Rp 0</span></div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
            <a href="{{ route('purchases.index') }}" class="btn btn-outline">Batal</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    var products = @json($products);
    var old = @json(old('items', []));
    var rows = document.getElementById('rows');
    var idx = 0;
    var fmt = function (n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n || 0)); };

    function addRow(data) {
        data = data || {};
        var i = idx++;
        var div = document.createElement('div');
        div.className = 'item-row';
        div.innerHTML =
            '<select name="items[' + i + '][product_id]" class="input p-sel" required>' +
                '<option value="">- Pilih produk -</option>' +
                products.map(function (p) {
                    return '<option value="' + p.id + '" data-price="' + p.price + '"' + (String(data.product_id) === String(p.id) ? ' selected' : '') + '>' +
                        p.label.replace(/</g, '&lt;') + '</option>';
                }).join('') +
            '</select>' +
            '<input type="number" min="1" step="1" name="items[' + i + '][quantity]" class="input q" value="' + (data.quantity || 1) + '" required>' +
            '<input type="number" min="0" step="1" name="items[' + i + '][purchase_price]" class="input pr" value="' + (data.purchase_price || '') + '" required>' +
            '<strong class="sub">Rp 0</strong>' +
            '<button type="button" class="icon-btn danger rm" title="Hapus baris">✕</button>';
        rows.appendChild(div);
        recalc();
    }

    function recalc() {
        var total = 0;
        rows.querySelectorAll('.item-row').forEach(function (r) {
            var s = (parseFloat(r.querySelector('.q').value) || 0) * (parseFloat(r.querySelector('.pr').value) || 0);
            r.querySelector('.sub').textContent = fmt(s);
            total += s;
        });
        document.getElementById('grandTotal').textContent = fmt(total);
    }

    rows.addEventListener('input', recalc);
    rows.addEventListener('change', function (e) {
        if (e.target.classList.contains('p-sel')) {
            var opt = e.target.options[e.target.selectedIndex];
            var price = opt ? opt.getAttribute('data-price') : '';
            var input = e.target.closest('.item-row').querySelector('.pr');
            if (price && !input.value) input.value = Math.round(price);
            recalc();
        }
    });
    rows.addEventListener('click', function (e) {
        if (e.target.classList.contains('rm')) {
            if (rows.children.length > 1) { e.target.closest('.item-row').remove(); recalc(); }
        }
    });
    document.getElementById('addRow').addEventListener('click', function () { addRow(); });

    if (old.length) { old.forEach(addRow); } else { addRow(); }
})();
</script>
@endpush
