/*
 * POS Terminal (vanilla JS)
 * Alur: cari/pilih produk -> masukkan ke keranjang -> validasi stok ->
 *       hitung subtotal & diskon -> pilih pelanggan & metode bayar -> input bayar.
 * Validasi final tetap dilakukan server (SaleService).
 */
(function () {
    var products = window.POS_PRODUCTS || [];
    var byId = {};
    products.forEach(function (p) { byId[p.id] = p; });

    var cart = {}; // product_id -> qty

    var $ = function (id) { return document.getElementById(id); };
    var grid = $('posGrid');
    var search = $('posSearch');
    var catSel = $('posCategory');
    var cartItems = $('cartItems');
    var cartInputs = $('cartInputs');
    var discountEl = $('discount');
    var methodEl = $('paymentMethod');
    var paidEl = $('paid');
    var paidWrap = $('paidWrap');
    var payBtn = $('payBtn');
    var warning = $('posWarning');
    var cartCount = $('cartCount');

    var fmt = function (n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
    };
    var esc = function (s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };

    var warnTimer;
    function warn(msg) {
        warning.textContent = msg;
        warning.style.display = 'block';
        clearTimeout(warnTimer);
        warnTimer = setTimeout(function () { warning.style.display = 'none'; }, 3500);
    }

    /* ---------- Daftar produk ---------- */
    function renderProducts() {
        var q = (search.value || '').trim().toLowerCase();
        var cat = catSel.value;

        var list = products.filter(function (p) {
            if (cat && String(p.category_id) !== cat) return false;
            if (!q) return true;
            return (p.name + ' ' + (p.sku || '') + ' ' + (p.barcode || '') + ' ' + (p.brand || '')).toLowerCase().indexOf(q) !== -1;
        });

        if (!list.length) {
            grid.innerHTML = '<div class="cart-empty" style="grid-column:1/-1">Produk tidak ditemukan.</div>';
            return;
        }

        grid.innerHTML = list.map(function (p) {
            var inCart = cart[p.id] || 0;
            var left = p.stock - inCart;
            var low = left <= 5;
            return '<button type="button" class="pos-item" data-id="' + p.id + '"' + (left <= 0 ? ' disabled' : '') + '>' +
                '<span class="name">' + esc(p.name) + '</span>' +
                '<span class="sku">' + esc(p.sku) + (p.brand ? ' · ' + esc(p.brand) : '') + '</span>' +
                '<span class="price">' + fmt(p.price) + '</span>' +
                '<span class="stock' + (low ? ' low' : '') + '">' + (left <= 0 ? 'Stok habis' : 'Stok: ' + left) + '</span>' +
                '</button>';
        }).join('');
    }

    /* ---------- Keranjang ---------- */
    function addToCart(id) {
        var p = byId[id];
        if (!p) return;
        var cur = cart[id] || 0;
        if (cur + 1 > p.stock) {
            warn('Stok "' + p.name + '" tidak mencukupi (tersedia ' + p.stock + ').');
            return;
        }
        cart[id] = cur + 1;
        refresh();
    }

    function setQty(id, qty) {
        var p = byId[id];
        if (!p) return;
        if (qty <= 0) {
            delete cart[id];
        } else if (qty > p.stock) {
            warn('Stok "' + p.name + '" tidak mencukupi (tersedia ' + p.stock + ').');
            cart[id] = p.stock;
        } else {
            cart[id] = qty;
        }
        refresh();
    }

    function subtotal() {
        var s = 0;
        Object.keys(cart).forEach(function (id) { s += byId[id].price * cart[id]; });
        return s;
    }

    function renderCart() {
        var ids = Object.keys(cart);
        cartCount.textContent = ids.reduce(function (a, id) { return a + cart[id]; }, 0);

        if (!ids.length) {
            cartItems.innerHTML = '<div class="cart-empty">Keranjang kosong.<br>Klik produk untuk menambahkan.</div>';
        } else {
            cartItems.innerHTML = ids.map(function (id) {
                var p = byId[id], q = cart[id];
                return '<div class="cart-line" data-id="' + id + '">' +
                    '<div><div class="n">' + esc(p.name) + '</div><div class="p">' + fmt(p.price) + ' / unit</div></div>' +
                    '<div class="t">' + fmt(p.price * q) + '</div>' +
                    '<div class="qty"><button type="button" data-act="dec">−</button><span>' + q + '</span><button type="button" data-act="inc">+</button></div>' +
                    '<div style="text-align:right"><button type="button" class="remove" data-act="del">Hapus</button></div>' +
                    '</div>';
            }).join('');
        }

        // Hidden input yang dikirim ke server.
        cartInputs.innerHTML = ids.map(function (id, i) {
            return '<input type="hidden" name="items[' + i + '][product_id]" value="' + id + '">' +
                '<input type="hidden" name="items[' + i + '][quantity]" value="' + cart[id] + '">';
        }).join('');
    }

    function totals() {
        var sub = subtotal();
        var disc = Math.min(Math.max(parseFloat(discountEl.value) || 0, 0), sub);
        var total = sub - disc;
        var isCash = methodEl.value === 'cash';
        var paid = isCash ? (parseFloat(paidEl.value) || 0) : total;
        var change = paid - total;

        $('subtotalText').textContent = fmt(sub);
        $('totalText').textContent = fmt(total);

        paidWrap.style.display = isCash ? 'block' : 'none';
        if (!isCash) paidEl.value = Math.round(total);

        var changeEl = $('changeText');
        if (isCash) {
            changeEl.textContent = fmt(Math.max(change, 0));
            changeEl.className = change >= 0 ? 'change-ok' : 'change-bad';
            $('changeLabel').textContent = change >= 0 ? 'Kembalian' : 'Kurang bayar';
            if (change < 0) changeEl.textContent = fmt(-change);
        } else {
            changeEl.textContent = fmt(0);
            changeEl.className = '';
            $('changeLabel').textContent = 'Kembalian';
        }

        payBtn.disabled = !(Object.keys(cart).length && total >= 0 && (!isCash || paid >= total));

        // Tombol uang cepat
        var quick = $('quickCash');
        if (isCash && total > 0) {
            var opts = [total];
            [10000, 20000, 50000, 100000].forEach(function (n) {
                var v = Math.ceil(total / n) * n;
                if (opts.indexOf(v) === -1) opts.push(v);
            });
            quick.innerHTML = opts.slice(0, 5).map(function (v) {
                return '<button type="button" data-v="' + Math.round(v) + '">' + (v === total ? 'Uang pas' : fmt(v)) + '</button>';
            }).join('');
        } else {
            quick.innerHTML = '';
        }
    }

    function refresh() {
        renderCart();
        renderProducts();
        totals();
    }

    /* ---------- Event ---------- */
    grid.addEventListener('click', function (e) {
        var btn = e.target.closest('.pos-item');
        if (btn) addToCart(parseInt(btn.dataset.id, 10));
    });

    cartItems.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-act]');
        if (!btn) return;
        var id = parseInt(btn.closest('.cart-line').dataset.id, 10);
        if (btn.dataset.act === 'inc') setQty(id, (cart[id] || 0) + 1);
        if (btn.dataset.act === 'dec') setQty(id, (cart[id] || 0) - 1);
        if (btn.dataset.act === 'del') setQty(id, 0);
    });

    search.addEventListener('input', renderProducts);
    catSel.addEventListener('change', renderProducts);

    // Enter pada kolom cari = scan barcode / SKU persis
    search.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        var q = search.value.trim().toLowerCase();
        if (!q) return;
        var hit = products.filter(function (p) {
            return (p.barcode || '').toLowerCase() === q || (p.sku || '').toLowerCase() === q;
        })[0];
        if (hit) {
            addToCart(hit.id);
            search.value = '';
            renderProducts();
        } else {
            warn('Barcode / SKU "' + search.value + '" tidak ditemukan.');
        }
    });

    [discountEl, paidEl].forEach(function (el) { el.addEventListener('input', totals); });
    methodEl.addEventListener('change', totals);

    $('quickCash').addEventListener('click', function (e) {
        var b = e.target.closest('button[data-v]');
        if (b) { paidEl.value = b.dataset.v; totals(); }
    });

    $('clearCart').addEventListener('click', function () {
        cart = {};
        refresh();
    });

    // Cegah submit dengan Enter di input angka (agar tidak tak sengaja bayar)
    $('posForm').addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.tagName === 'INPUT') e.preventDefault();
    });

    // Pulihkan keranjang bila server menolak (mis. stok berubah)
    (window.POS_OLD_ITEMS || []).forEach(function (i) {
        var p = byId[i.product_id];
        var q = parseInt(i.quantity, 10) || 0;
        if (p && p.stock > 0 && q > 0) cart[p.id] = Math.min(q, p.stock);
    });

    refresh();
    search.focus();
})();
