<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/stok.php';
cek_instalasi();
wajib_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $pdo = db();

    if (post('aksi') === 'batal') {
        wajib_admin();
        $id = (int) post('id');
        $pr = satu('SELECT pr.*, p.nama FROM produksi pr JOIN produk p ON p.id = pr.produk_id WHERE pr.id = ?', [$id]);
        if ($pr) {
            $pdo->beginTransaction();
            $ket = sprintf('Pembatalan produksi #%d %s (%s)', $id, $pr['nama'], tgl($pr['tanggal']));
            foreach (semua("SELECT bahan_id, jumlah, harga FROM mutasi WHERE produksi_id = ? AND jenis = 'produksi'", [$id]) as $m) {
                catat_mutasi((int) $m['bahan_id'], 'produksi', -(float) $m['jumlah'], date('Y-m-d'), ['harga' => $m['harga'], 'keterangan' => $ket]);
            }
            q('UPDATE mutasi SET produksi_id = NULL WHERE produksi_id = ?', [$id]);
            q('DELETE FROM produksi WHERE id = ?', [$id]);
            $pdo->commit();
            flash('sukses', 'Produksi dibatalkan, stok bahan dikembalikan.');
        }
        redirect('produksi.php');
    }

    $produkId = (int) post('produk_id');
    $batch = ke_angka(post('batch'));
    $tanggal = post('tanggal');
    $produk = satu('SELECT * FROM produk WHERE id = ?', [$produkId]);
    $hasil = post('hasil') === '' ? $batch * (float) ($produk['hasil_per_batch'] ?? 0) : ke_angka(post('hasil'));
    $resep = semua('SELECT r.bahan_id, r.jumlah, b.harga FROM resep r JOIN bahan b ON b.id = r.bahan_id WHERE r.produk_id = ?', [$produkId]);

    if (!$produk || $batch <= 0 || !valid_tanggal($tanggal)) {
        flash('error', 'Pilih produk, isi jumlah batch dan tanggal dengan benar.');
    } elseif (!$resep) {
        flash('error', 'Produk ini belum punya resep. Isi resep dulu di menu Produk & Resep.');
    } else {
        try {
            $pdo->beginTransaction();
            q('INSERT INTO produksi (tanggal, produk_id, batch, hasil, keterangan, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$tanggal, $produkId, $batch, $hasil, post('keterangan'), pengguna()['id'], sekarang()]);
            $prId = (int) $pdo->lastInsertId();
            foreach ($resep as $r) {
                catat_mutasi((int) $r['bahan_id'], 'produksi', -round((float) $r['jumlah'] * $batch, 3), $tanggal, [
                    'harga' => $r['harga'], 'produksi_id' => $prId,
                    'keterangan' => sprintf('Produksi #%d %s × %s batch', $prId, $produk['nama'], angka($batch)),
                ]);
            }
            $pdo->commit();
            flash('sukses', sprintf('Produksi %s %s batch dicatat. Stok bahan sudah dikurangi.', $produk['nama'], angka($batch)));
        } catch (RuntimeException $ex) {
            $pdo->rollBack();
            flash('error', 'Produksi gagal: ' . $ex->getMessage());
        }
    }
    redirect('produksi.php');
}

$produk = semua('SELECT * FROM produk ORDER BY nama');
$dataResep = [];
foreach ($produk as $p) {
    $dataResep[$p['id']] = [
        'hasil'  => (float) $p['hasil_per_batch'],
        'satuan' => $p['satuan_hasil'],
        'bahan'  => array_map(fn ($r) => [
            'nama' => $r['nama'], 'satuan' => $r['satuan'], 'jumlah' => (float) $r['jumlah'],
            'stok' => (float) $r['stok'], 'harga' => (float) $r['harga'],
        ], semua('SELECT r.jumlah, b.nama, b.satuan, b.stok, b.harga FROM resep r JOIN bahan b ON b.id = r.bahan_id WHERE r.produk_id = ? ORDER BY b.nama', [$p['id']])),
    ];
}

$riwayat = semua(
    "SELECT pr.*, p.nama, p.satuan_hasil, u.nama AS petugas,
            (SELECT COALESCE(SUM(-m.jumlah * m.harga), 0) FROM mutasi m WHERE m.produksi_id = pr.id) AS biaya
     FROM produksi pr JOIN produk p ON p.id = pr.produk_id LEFT JOIN users u ON u.id = pr.user_id
     ORDER BY pr.tanggal DESC, pr.id DESC LIMIT 50"
);

$judul = 'Produksi';
include __DIR__ . '/inc/header.php';
?>
<div class="grid-2">
<section class="kartu kartu-form">
  <h2>Catat produksi</h2>
  <?php if (!$produk): ?>
    <p class="kosong">Belum ada produk. <a href="produk.php?aksi=tambah">Tambah produk &amp; resep</a>.</p>
  <?php else: ?>
  <form method="post" class="form" id="form-produksi">
    <?= csrf_field() ?>
    <label>Produk
      <select name="produk_id" id="produk" required>
        <?php foreach ($produk as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e($p['nama']) ?></option><?php endforeach; ?>
      </select>
    </label>
    <div class="grid-form grid-form-2">
      <label>Jumlah batch<input type="number" name="batch" id="batch" step="0.5" min="0.5" value="1" required></label>
      <label>Tanggal<input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>"></label>
      <label>Hasil aktual<div class="input-satuan"><input type="number" name="hasil" id="hasil" step="0.001" min="0" placeholder="otomatis"><span class="satuan" id="satuan-hasil"></span></div></label>
      <label>Keterangan<input name="keterangan" maxlength="255" placeholder="Shift, operator, dll."></label>
    </div>
    <button class="btn btn-primary btn-blok" id="tombol-produksi">Simpan &amp; kurangi stok</button>
  </form>
  <?php endif; ?>
</section>

<section class="kartu">
  <h2>Kebutuhan bahan</h2>
  <table class="tabel" id="kebutuhan">
    <thead><tr><th>Bahan</th><th class="kanan">Dibutuhkan</th><th class="kanan">Stok</th><th class="kanan">Sisa</th></tr></thead>
    <tbody></tbody>
    <tfoot><tr><th>Estimasi biaya bahan</th><th></th><th></th><th class="kanan" id="biaya">-</th></tr></tfoot>
  </table>
</section>
</div>

<section class="kartu">
  <div class="kartu-kepala"><h2>Riwayat produksi</h2></div>
  <div class="tabel-wrap">
  <table class="tabel">
    <thead><tr><th>#</th><th>Tanggal</th><th>Produk</th><th class="kanan">Batch</th><th class="kanan">Hasil</th><th class="kanan">Biaya bahan</th><th class="kanan">Biaya / satuan</th><th>Keterangan</th><th>Petugas</th><th></th></tr></thead>
    <tbody>
    <?php if (!$riwayat): ?><tr><td colspan="10" class="kosong">Belum ada produksi.</td></tr><?php endif; ?>
    <?php foreach ($riwayat as $r): ?>
      <tr>
        <td><?= (int) $r['id'] ?></td>
        <td><?= tgl($r['tanggal']) ?></td>
        <td><?= e($r['nama']) ?></td>
        <td class="kanan"><?= angka($r['batch']) ?></td>
        <td class="kanan"><?= angka($r['hasil']) ?> <?= e($r['satuan_hasil']) ?></td>
        <td class="kanan"><?= rupiah($r['biaya']) ?></td>
        <td class="kanan"><?= $r['hasil'] > 0 ? rupiah($r['biaya'] / $r['hasil']) : '-' ?></td>
        <td><?= e($r['keterangan']) ?></td>
        <td><?= e($r['petugas'] ?? '-') ?></td>
        <td class="aksi">
          <?php if (pengguna()['peran'] === 'admin'): ?>
          <form method="post" data-konfirmasi="Batalkan produksi #<?= (int) $r['id'] ?>? Stok bahan akan dikembalikan.">
            <?= csrf_field() ?><input type="hidden" name="aksi" value="batal"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <button class="btn btn-sm btn-hapus">Batalkan</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</section>
<script>window.DATA_RESEP = <?= json_encode($dataResep, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<?php include __DIR__ . '/inc/footer.php'; ?>
