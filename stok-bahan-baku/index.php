<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/stok.php';
cek_instalasi();
wajib_login();

$totalBahan = (int) nilai('SELECT COUNT(*) FROM bahan');
$nilaiStok = (float) nilai('SELECT COALESCE(SUM(stok * harga), 0) FROM bahan');
$menipis = semua('SELECT * FROM bahan WHERE stok <= stok_min ORDER BY (stok / CASE WHEN stok_min = 0 THEN 1 ELSE stok_min END), nama');
$awalBulan = date('Y-m-01');
$produksiBulan = semua(
    'SELECT p.nama, p.satuan_hasil, SUM(pr.hasil) AS total, SUM(pr.batch) AS batch
     FROM produksi pr JOIN produk p ON p.id = pr.produk_id
     WHERE pr.tanggal >= ? GROUP BY p.id, p.nama, p.satuan_hasil ORDER BY p.nama',
    [$awalBulan]
);
$belanjaBulan = (float) nilai("SELECT COALESCE(SUM(jumlah * harga), 0) FROM mutasi WHERE jenis = 'masuk' AND tanggal >= ?", [$awalBulan]);

// Kapasitas produksi: berapa batch lagi yang bisa dibuat dengan stok saat ini.
$kapasitas = [];
foreach (semua('SELECT * FROM produk ORDER BY nama') as $p) {
    $resep = semua('SELECT r.jumlah, b.nama, b.stok, b.satuan FROM resep r JOIN bahan b ON b.id = r.bahan_id WHERE r.produk_id = ?', [$p['id']]);
    $batch = null;
    $pembatas = null;
    foreach ($resep as $r) {
        if ((float) $r['jumlah'] <= 0) {
            continue;
        }
        $bisa = floor((float) $r['stok'] / (float) $r['jumlah']);
        if ($batch === null || $bisa < $batch) {
            $batch = $bisa;
            $pembatas = $r['nama'];
        }
    }
    $kapasitas[] = ['produk' => $p, 'batch' => (int) ($batch ?? 0), 'pembatas' => $pembatas, 'ada_resep' => (bool) $resep];
}

$terakhir = semua(
    'SELECT m.*, b.nama, b.satuan FROM mutasi m JOIN bahan b ON b.id = m.bahan_id ORDER BY m.id DESC LIMIT 8'
);

$judul = 'Dashboard';
include __DIR__ . '/inc/header.php';
?>
<div class="grid-stat">
  <div class="stat"><span class="stat-label">Jenis bahan baku</span><span class="stat-nilai"><?= $totalBahan ?></span></div>
  <div class="stat <?= $menipis ? 'stat-bahaya' : '' ?>"><span class="stat-label">Stok menipis</span><span class="stat-nilai"><?= count($menipis) ?></span></div>
  <div class="stat"><span class="stat-label">Nilai persediaan</span><span class="stat-nilai"><?= rupiah($nilaiStok) ?></span></div>
  <div class="stat"><span class="stat-label">Belanja bahan bulan ini</span><span class="stat-nilai"><?= rupiah($belanjaBulan) ?></span></div>
</div>

<div class="grid-2">
  <section class="kartu">
    <div class="kartu-kepala"><h2>Kapasitas produksi dari stok sekarang</h2><a href="produksi.php" class="btn btn-sm">Catat produksi</a></div>
    <?php if (!$kapasitas): ?>
      <p class="kosong">Belum ada produk. <a href="produk.php">Tambah produk &amp; resep</a>.</p>
    <?php endif; ?>
    <?php foreach ($kapasitas as $k): ?>
      <div class="kapasitas">
        <div>
          <strong><?= e($k['produk']['nama']) ?></strong>
          <small class="muted">
            <?php if (!$k['ada_resep']): ?>Resep belum diisi
            <?php else: ?>≈ <?= angka($k['batch'] * $k['produk']['hasil_per_batch']) ?> <?= e($k['produk']['satuan_hasil']) ?> · dibatasi oleh <?= e($k['pembatas']) ?><?php endif; ?>
          </small>
        </div>
        <span class="badge <?= $k['batch'] < 3 ? 'badge-bahaya' : 'badge-ok' ?>"><?= $k['batch'] ?> batch</span>
      </div>
    <?php endforeach; ?>

    <h3 class="sub">Produksi bulan ini</h3>
    <?php if (!$produksiBulan): ?><p class="kosong">Belum ada produksi bulan ini.</p><?php endif; ?>
    <?php foreach ($produksiBulan as $p): ?>
      <div class="kapasitas"><span><?= e($p['nama']) ?> <small class="muted"><?= angka($p['batch']) ?> batch</small></span><strong><?= angka($p['total']) ?> <?= e($p['satuan_hasil']) ?></strong></div>
    <?php endforeach; ?>
  </section>

  <section class="kartu">
    <div class="kartu-kepala"><h2>Perlu dibeli</h2><a href="masuk.php" class="btn btn-sm">Catat bahan masuk</a></div>
    <?php if (!$menipis): ?>
      <p class="kosong">Semua stok aman 👍</p>
    <?php else: ?>
      <table class="tabel">
        <thead><tr><th>Bahan</th><th class="kanan">Stok</th><th class="kanan">Minimum</th></tr></thead>
        <tbody>
        <?php foreach ($menipis as $b): ?>
          <tr>
            <td><?= e($b['nama']) ?><br><small class="muted"><?= e($b['kode']) ?></small></td>
            <td class="kanan merah"><?= angka($b['stok']) ?> <?= e($b['satuan']) ?></td>
            <td class="kanan"><?= angka($b['stok_min']) ?> <?= e($b['satuan']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>
</div>

<section class="kartu">
  <div class="kartu-kepala"><h2>Mutasi terakhir</h2><a href="laporan.php" class="btn btn-sm">Lihat semua</a></div>
  <div class="tabel-wrap">
  <table class="tabel">
    <thead><tr><th>Tanggal</th><th>Bahan</th><th>Jenis</th><th class="kanan">Jumlah</th><th class="kanan">Stok akhir</th><th>Keterangan</th></tr></thead>
    <tbody>
    <?php if (!$terakhir): ?><tr><td colspan="6" class="kosong">Belum ada transaksi.</td></tr><?php endif; ?>
    <?php foreach ($terakhir as $m): ?>
      <tr>
        <td><?= tgl($m['tanggal']) ?></td>
        <td><?= e($m['nama']) ?></td>
        <td><span class="jenis jenis-<?= e($m['jenis']) ?>"><?= e(LABEL_JENIS[$m['jenis']] ?? $m['jenis']) ?></span></td>
        <td class="kanan <?= $m['jumlah'] < 0 ? 'merah' : 'hijau' ?>"><?= $m['jumlah'] > 0 ? '+' : '' ?><?= angka($m['jumlah']) ?> <?= e($m['satuan']) ?></td>
        <td class="kanan"><?= angka($m['stok_akhir']) ?></td>
        <td><?= e($m['keterangan']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</section>
<?php include __DIR__ . '/inc/footer.php'; ?>
