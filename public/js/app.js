/* Interaksi umum: menu mobile, auto-dismiss alert, checkbox massal. */
(function () {
    // Toggle sidebar di layar kecil
    var btn = document.getElementById('menuBtn');
    var sidebar = document.getElementById('sidebar');
    if (btn && sidebar) {
        btn.addEventListener('click', function () { sidebar.classList.toggle('open'); });
        document.addEventListener('click', function (e) {
            if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !btn.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // Alert sukses hilang otomatis
    document.querySelectorAll('.alert-success').forEach(function (el) {
        setTimeout(function () { el.style.display = 'none'; }, 5000);
    });

    // Pilih massal pada tabel produk
    var all = document.getElementById('checkAll');
    var boxes = document.querySelectorAll('.row-check');
    var counter = document.getElementById('selectedCount');

    function updateCount() {
        if (!counter) return;
        var n = 0;
        boxes.forEach(function (b) { if (b.checked) n++; });
        counter.textContent = n;
        if (all) all.checked = boxes.length > 0 && n === boxes.length;
    }

    if (all) {
        all.addEventListener('change', function () {
            boxes.forEach(function (b) { b.checked = all.checked; });
            updateCount();
        });
    }
    boxes.forEach(function (b) { b.addEventListener('change', updateCount); });
    updateCount();
})();
