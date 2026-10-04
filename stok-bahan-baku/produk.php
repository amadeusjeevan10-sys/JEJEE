<?php
require_once __DIR__ . '/inc/auth.php';
cek_instalasi();
wajib_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $id = (int) post('id');

    if (post('aksi') === 'hapus') {
        if (nilai('SELECT COUNT(*) FROM produksi WHERE produk_id = ?', [$id])) {
            flash('error', 'Produk sudah punya riwayat produksi, tidak bisa dihapus.');
        } else {
            q('DELETE FROM resep WHERE produk_id = ?', [$id]);
            q('DELETE FROM produk WHERE id = ?', [$id]);
            flash('sukses', 'Produk dihapus.');
        }
        redirect('produk.php');
    }

    $nama = post('nama');
    $hasil = ke_angka(post('hasil_per_batch'));
    $satuan = post('satuan_hasil');
    $ket = post('keterangan');

    $resep = [];
    foreach ((array) ($_POST['bahan_id'] ?? []) as $i => $bid) {
        $jml = ke_angka($_POST['jumlah'][$i] ?? 0);
        if ((int) $bid && $jml > 0) {
            $resep[(int) $bid] = ($resep[(int) $bid] ?? 0) + $jml; // gabungkan bahan yang dobel
        }
    }

    if ($nama === '' || $hasil <= 0 || $satuan === '') {
        flash('error', 'Nama, hasil per batch, dan satuan hasil wajib diisi.');
        redirect('produk.php?' . ($id ? 'edit=' . $id : 'aksi=tambah'));
    }

    $pdo = db();
    $pdo->beginTransaction();
    if ($id) {
        q('UPDATE produk SET nama = ?, hasil_per_batch = ?, satuan_hasil = ?, keterangan = ? WHERE id = ?', [$nama, $hasil, $satuan, $ket, $id]);
        q('DELETE FROM resep WHERE produk_id = ?', [$id]);
    } else {
        q('INSERT INTO produk (nama, hasil_per_batch, satuan_hasil, keterangan) VALUES (?, ?, ?, ?)', [$nama, $hasil, $satuan, $ket]);
        $id = (int) $pdo->lastInsertId();
    }
    foreach ($resep as $bid => $jml) {
        q('INSERT INTO resep (produk_id, bahan_id, jumlah) VALUES (?, ?, ?)', [$id, $bid, $jml]);
    }
    $pdo->commit();
    flash('sukses', 'Produk & resep disimpan.');
    redirect('produk.php');
}

$bahan = semua('SELECT id, kode, nama, satuan, harga FROM bahan ORDER BY nama');

$form = null;
if (isset($_GET['edit'])) {
    $form = satu('SELECT * FROM produk WHERE id = ?', [(int) $_GET['edit']]);
    if ($form) {
        $form['resep'] = semua('SELECT bahan_id, jumlah FROM resep WHERE produk_id = ?', [$form['id']]);
    }
} elseif (($_GET['aksi'] ?? '') === 'tambah') {
    $form = ['id' => 0, 'nama' => '', 'hasil_per_batch' => 1, 'satuan_hasil' => 'kg', 'keterangan' => '', 'resep' => []];
}
if ($form && !$form['resep']) {
    $form['resep'][] = ['bahan_id' => 0, 'jumlah' => ''];
}

$produk = semua('SELECT * FROM produk ORDER BY nama');
foreach ($produk as &$p) {
    $p['resep'] = semua(
        'SELECT r.jumlah, b.nama, b.satuan, b.harga, b.stok FROM resep r JOIN bahan b ON b.id = r.bahan_id WHERE r.produk_id = ? ORDER BY b.nama',
        [$p['id']]
    );
    $p['hpp'] = array_sum(array_map(fn ($r) => $r['jumlah'] * $r['harga'], $p['resep']));
}
unset($p);

$judul = 'Produk & Resep';
include __DIR__ . '/inc/header.php';
?>
<?php if ($form): ?>
<section class="kartu kartu-form">
  <h2><?= $form['id'] ? 'Ubah produk & resep' : 'Tambah produk' ?></h2>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
    <div class="grid-form">
      <label class="span-2">Nama produk<input name="nama" required maxlength="100" value="<?= e($form['nama']) ?>" placeholder="mis. Mie Telur Basah"></label>
      <label>Hasil per batch<div class="input-satuan"><input type="number" step="0.001" min="0.001" name="hasil_per_batch" required value="<?= e((float) $form['hasil_per_batch']) ?>"></div></label>
      <label>Satuan hasil<input name="satuan_hasil" list="daftar-satuan" required maxlength="20" value="<?= e($form['satuan_hasil']) ?>"></label>
      <datalist id="daftar-satuan"><?php foreach (SATUAN as $s): ?><option value="<?= e($s) ?>"><?php endforeach; ?><option value="lembar"></datalist>
      <label class="span-2">Keterangan<input name="keterangan" maxlength="255" value="<?= e($form['keterangan']) ?>"></label>
    </div>

    <h3 class="sub">Resep untuk 1 batch</h3>
    <table class="tabel tabel-input" id="baris-bahan">
      <thead><tr><th>Bahan</th><th>Jumlah per batch</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($form['resep'] as $r): ?>
        <tr class="baris">
          <td>
            <select name="bahan_id[]" class="pilih-bahan">
              <option value="">— Pilih bahan —</option>
              <?php foreach ($bahan as $b): ?>
                <option value="<?= (int) $b['id'] ?>" data-satuan="<?= e($b['satuan']) ?>" <?= (int) $r['bahan_id'] === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['kode'] . ' · ' . $b['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><div class="input-satuan"><input type="number" name="jumlah[]" step="0.001" min="0" value="<?= $r['jumlah'] === '' ? '' : e((float) $r['jumlah']) ?>"><span class="satuan"></span></div></td>
          <td><button type="button" class="btn btn-sm btn-ghost hapus-baris">✕</button></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot><tr><td colspan="3"><button type="button" class="btn btn-sm" id="tambah-baris">+ Tambah bahan</button></td></tr></tfoot>
    </table>
    <div class="aksi-form">
      <button class="btn btn-primary">Simpan</button>
      <a href="produk.php" class="btn btn-ghost">Batal</a>
    </div>
  </form>
</section>
<?php endif; ?>

<div class="kartu-kepala lepas">
  <p class="muted">Resep dipakai untuk mengurangi stok bahan secara otomatis saat produksi dicatat.</p>
  <a href="produk.php?aksi=tambah" class="btn btn-primary btn-sm">+ Tambah produk</a>
</div>
<div class="grid-2">
  <?php if (!$produk): ?><section class="kartu"><p class="kosong">Belum ada produk.</p></section><?php endif; ?>
  <?php foreach ($produk as $p): ?>
  <section class="kartu">
    <div class="kartu-kepala">
      <div>
        <h2><?= e($p['nama']) ?></h2>
        <small class="muted">1 batch = <?= angka($p['hasil_per_batch']) ?> <?= e($p['satuan_hasil']) ?><?= $p['keterangan'] ? ' · ' . e($p['keterangan']) : '' ?></small>
      </div>
      <div class="aksi">
        <a href="produk.php?edit=<?= (int) $p['id'] ?>" class="btn btn-sm btn-ghost">Ubah</a>
        <form method="post" data-konfirmasi="Hapus produk <?= e($p['nama']) ?>?">
          <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
          <button class="btn btn-sm btn-hapus">Hapus</button>
        </form>
      </div>
    </div>
    <table class="tabel">
      <thead><tr><th>Bahan</th><th class="kanan">Per batch</th><th class="kanan">Biaya</th></tr></thead>
      <tbody>
      <?php if (!$p['resep']): ?><tr><td colspan="3" class="kosong">Resep belum diisi.</td></tr><?php endif; ?>
      <?php foreach ($p['resep'] as $r): ?>
        <tr><td><?= e($r['nama']) ?></td><td class="kanan"><?= angka($r['jumlah']) ?> <?= e($r['satuan']) ?></td><td class="kanan"><?= rupiah($r['jumlah'] * $r['harga']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><th>Biaya bahan per batch</th><th></th><th class="kanan"><?= rupiah($p['hpp']) ?></th></tr>
        <tr><th>Biaya bahan per <?= e($p['satuan_hasil']) ?></th><th></th><th class="kanan"><?= rupiah($p['hpp'] / max((float) $p['hasil_per_batch'], 0.001)) ?></th></tr>
      </tfoot>
    </table>
  </section>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
