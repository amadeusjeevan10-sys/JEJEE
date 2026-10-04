(function () {
  'use strict';

  var fmt = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 3 });
  var rp = function (n) { return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(n); };
  var num = function (v) { var n = parseFloat(v); return isNaN(n) ? 0 : n; };

  // Konfirmasi sebelum hapus / batal.
  document.querySelectorAll('form[data-konfirmasi]').forEach(function (f) {
    f.addEventListener('submit', function (ev) {
      if (!confirm(f.getAttribute('data-konfirmasi'))) ev.preventDefault();
    });
  });

  // Tabel baris bahan (transaksi masuk/keluar & resep).
  var tabel = document.getElementById('baris-bahan');
  if (tabel) {
    var tbody = tabel.querySelector('tbody');
    var cetakan = tbody.querySelector('.baris').cloneNode(true);

    var perbaruiBaris = function (tr, isiHarga) {
      var sel = tr.querySelector('.pilih-bahan');
      var opt = sel.options[sel.selectedIndex];
      var satuan = opt && opt.dataset.satuan ? opt.dataset.satuan : '';
      var s = tr.querySelector('.satuan'); if (s) s.textContent = satuan;
      var st = tr.querySelector('.info-stok'); if (st) st.textContent = opt && opt.dataset.stok ? opt.dataset.stok + ' ' + satuan : '-';
      var h = tr.querySelector('.isi-harga');
      if (h && isiHarga && opt && opt.dataset.harga) h.value = num(opt.dataset.harga);
      hitungTotal();
    };

    var hitungTotal = function () {
      var totalEl = document.getElementById('total');
      var total = 0;
      tbody.querySelectorAll('.baris').forEach(function (tr) {
        var h = tr.querySelector('.isi-harga');
        var sub = tr.querySelector('.subtotal');
        if (!h || !sub) return;
        var n = num(tr.querySelector('.isi-jumlah').value) * num(h.value);
        sub.textContent = n ? rp(n) : '-';
        total += n;
      });
      if (totalEl) totalEl.textContent = rp(total);
    };

    tbody.addEventListener('change', function (ev) {
      if (ev.target.classList.contains('pilih-bahan')) perbaruiBaris(ev.target.closest('tr'), true);
    });
    tbody.addEventListener('input', hitungTotal);
    tbody.addEventListener('click', function (ev) {
      if (!ev.target.classList.contains('hapus-baris')) return;
      var baris = tbody.querySelectorAll('.baris');
      var tr = ev.target.closest('tr');
      if (baris.length > 1) tr.remove();
      else { tr.querySelectorAll('input').forEach(function (i) { i.value = ''; }); tr.querySelector('select').selectedIndex = 0; perbaruiBaris(tr, false); }
      hitungTotal();
    });
    document.getElementById('tambah-baris').addEventListener('click', function () {
      var baru = cetakan.cloneNode(true);
      baru.querySelectorAll('input').forEach(function (i) { i.value = ''; });
      baru.querySelectorAll('option').forEach(function (o) { o.selected = false; o.removeAttribute('selected'); });
      tbody.appendChild(baru);
      perbaruiBaris(baru, false);
      baru.querySelector('select').focus();
    });
    tbody.querySelectorAll('.baris').forEach(function (tr) { perbaruiBaris(tr, false); });
  }

  // Selisih stok opname.
  document.querySelectorAll('.baris-opname').forEach(function (tr) {
    var input = tr.querySelector('.isi-fisik');
    var out = tr.querySelector('.selisih');
    input.addEventListener('input', function () {
      if (input.value === '') { out.textContent = '-'; out.className = 'kanan selisih muted'; return; }
      var d = Math.round((num(input.value) - num(tr.dataset.stok)) * 1000) / 1000;
      out.textContent = (d > 0 ? '+' : '') + fmt.format(d);
      out.className = 'kanan selisih ' + (d < 0 ? 'merah' : d > 0 ? 'hijau' : 'muted');
    });
  });

  // Pratinjau kebutuhan bahan produksi.
  var formProduksi = document.getElementById('form-produksi');
  if (formProduksi && window.DATA_RESEP) {
    var produk = document.getElementById('produk');
    var batch = document.getElementById('batch');
    var hasil = document.getElementById('hasil');
    var tombol = document.getElementById('tombol-produksi');
    var body = document.querySelector('#kebutuhan tbody');

    var render = function () {
      var r = window.DATA_RESEP[produk.value];
      var b = num(batch.value);
      body.innerHTML = '';
      if (!r) return;
      document.getElementById('satuan-hasil').textContent = r.satuan;
      hasil.placeholder = fmt.format(r.hasil * b) + ' (otomatis)';
      var biaya = 0, cukup = true;
      if (!r.bahan.length) {
        body.innerHTML = '<tr><td colspan="4" class="kosong">Resep belum diisi.</td></tr>';
        cukup = false;
      }
      r.bahan.forEach(function (x) {
        var butuh = Math.round(x.jumlah * b * 1000) / 1000;
        var sisa = Math.round((x.stok - butuh) * 1000) / 1000;
        biaya += butuh * x.harga;
        if (sisa < 0) cukup = false;
        var tr = document.createElement('tr');
        [x.nama, fmt.format(butuh) + ' ' + x.satuan, fmt.format(x.stok) + ' ' + x.satuan, fmt.format(sisa)].forEach(function (t, i) {
          var td = document.createElement('td');
          td.textContent = t;
          if (i > 0) td.className = 'kanan';
          if (i === 3) td.className += sisa < 0 ? ' merah' : ' hijau';
          tr.appendChild(td);
        });
        body.appendChild(tr);
      });
      document.getElementById('biaya').textContent = rp(biaya);
      tombol.disabled = !cukup;
      tombol.textContent = cukup ? 'Simpan & kurangi stok' : 'Stok bahan tidak cukup';
    };
    produk.addEventListener('change', render);
    batch.addEventListener('input', render);
    render();
  }
})();
