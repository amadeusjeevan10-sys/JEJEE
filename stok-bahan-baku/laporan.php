<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/stok.php';
cek_instalasi();
wajib_login();

$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
if (!valid_tanggal($dari)) $dari = date('Y-m-01');
if (!valid_tanggal($sampai)) $sampai = date('Y-m-d');
$bahanId = (int) ($_GET['bahan'] ?? 0);
$jenis = array_key_exists($_GET['jenis'] ?? '', LABEL_JENIS) ? $_GET['jenis'] : '';

$daftarBahan = semua('SELECT id, kode, nama FROM bahan ORDER BY nama');

// Rekap per bahan untuk periode: stok awal, masuk, keluar (+produksi), penyesuaian, stok akhir.
$rekap = semua(
    "SELECT b.id, b.kode, b.nama, b.satuan, b.harga,
        COALESCE(SUM(CASE WHEN m.tanggal < ? THEN m.jumlah END), 0) AS awal,
        COALESCE(SUM(CASE WHEN m.tanggal BETWEEN ? AND ? AND m.jenis = 'masuk' THEN m.jumlah END), 0) AS masuk,
        COALESCE(SUM(CASE WHEN m.tanggal BETWEEN ? AND ? AND m.jenis = 'produksi' THEN m.jumlah END), 0) AS produksi,
        COALESCE(SUM(CASE WHEN m.tanggal BETWEEN ? AND ? AND m.jenis = 'keluar' THEN m.jumlah END), 0) AS keluar,
        COALESCE(SUM(CASE WHEN m.tanggal BETWEEN ? AND ? AND m.jenis = 'penyesuaian' THEN m.jumlah END), 0) AS penyesuaian,
        COALESCE(SUM(CASE WHEN m.tanggal <= ? THEN m.jumlah END), 0) AS akhir
     FROM bahan b LEFT JOIN mutasi m ON m.bahan_id = b.id
     " . ($bahanId ? 'WHERE b.id = ?' : '') . "
     GROUP BY b.id, b.kode, b.nama, b.satuan, b.harga ORDER BY b.nama",
    array_merge([$dari, $dari, $sampai, $dari, $sampai, $dari, $sampai, $dari, $sampai, $sampai], $bahanId ? [$bahanId] : [])
);

$where = ['m.tanggal BETWEEN ? AND ?'];
$par = [$dari, $sampai];
if ($bahanId) { $where[] = 'm.bahan_id = ?'; $par[] = $bahanId; }
if ($jenis) { $where[] = 'm.jenis = ?'; $par[] = $jenis; }
$mutasi = semua(
    'SELECT m.*, b.kode, b.nama, b.satuan, s.nama AS supplier, u.nama AS petugas
     FROM mutasi m JOIN bahan b ON b.id = m.bahan_id
     LEFT JOIN supplier s ON s.id = m.supplier_id LEFT JOIN users u ON u.id = m.user_id
     WHERE ' . implode(' AND ', $where) . ' ORDER BY m.tanggal, m.id',
    $par
);

$totalBelanja = 0;
$totalPakai = 0;
foreach ($mutasi as $m) {
    if ($m['jenis'] === 'masuk') $totalBelanja += $m['jumlah'] * $m['harga'];
    if ($m['jenis'] === 'produksi') $totalPakai += -$m['jumlah'] * $m['harga'];
}

if (($_GET['ekspor'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="mutasi-' . $dari . '-sd-' . $sampai . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
    fputcsv($out, ['Tanggal', 'Kode', 'Bahan', 'Jenis', 'Jumlah', 'Satuan', 'Stok akhir', 'Harga', 'Supplier', 'Keterangan', 'Petugas'], ';');
    foreach ($mutasi as $m) {
        fputcsv($out, [$m['tanggal'], $m['kode'], $m['nama'], LABEL_JENIS[$m['jenis']] ?? $m['jenis'],
            str_replace('.', ',', (string) (float) $m['jumlah']), $m['satuan'], str_replace('.', ',', (string) (float) $m['stok_akhir']),
            (float) $m['harga'], $m['supplier'], $m['keterangan'], $m['petugas']], ';');
    }
    exit;
}

$judul = 'Kartu Stok & Mutasi';
include __DIR__ . '/inc/header.php';
?>
<section class="kartu no-print">
  <form class="filter" method="get">
    <label>Dari<input type="date" name="dari" value="<?= e($dari) ?>"></label>
    <label>Sampai<input type="date" name="sampai" value="<?= e($sampai) ?>"></label>
    <label>Bahan
      <select name="bahan"><option value="0">Semua bahan</option>
        <?php foreach ($daftarBahan as $b): ?><option value="<?= (int) $b['id'] ?>" <?= $bahanId === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['nama']) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Jenis
      <select name="jenis"><option value="">Semua jenis</option>
        <?php foreach (LABEL_JENIS as $k => $v): ?><option value="<?= e($k) ?>" <?= $jenis === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
      </select>
    </label>
    <button class="btn btn-primary btn-sm">Tampilkan</button>
    <a class="btn btn-sm" href="?<?= e(http_build_query(['dari' => $dari, 'sampai' => $sampai, 'bahan' => $bahanId, 'jenis' => $jenis, 'ekspor' => 'csv'])) ?>">Unduh CSV</a>
    <button type="button" class="btn btn-sm" onclick="window.print()">Cetak</button>
  </form>
</section>

<div class="judul-cetak">
  <h2><?= e(APP_NAME) ?> — Laporan Mutasi Bahan Baku</h2>
  <p>Periode <?= tgl($dari) ?> s/d <?= tgl($sampai) ?></p>
</div>

<div class="grid-stat">
  <div class="stat"><span class="stat-label">Transaksi</span><span class="stat-nilai"><?= count($mutasi) ?></span></div>
  <div class="stat"><span class="stat-label">Belanja bahan</span><span class="stat-nilai"><?= rupiah($totalBelanja) ?></span></div>
  <div class="stat"><span class="stat-label">Bahan terpakai produksi</span><span class="stat-nilai"><?= rupiah($totalPakai) ?></span></div>
</div>

<section class="kartu">
  <h2>Rekap stok per bahan</h2>
  <div class="tabel-wrap">
  <table class="tabel">
    <thead><tr><th>Bahan</th><th class="kanan">Stok awal</th><th class="kanan">Masuk</th><th class="kanan">Produksi</th><th class="kanan">Keluar</th><th class="kanan">Penyesuaian</th><th class="kanan">Stok akhir</th><th class="kanan">Nilai akhir</th></tr></thead>
    <tbody>
    <?php foreach ($rekap as $r): ?>
      <tr>
        <td><a href="?<?= e(http_build_query(['dari' => $dari, 'sampai' => $sampai, 'bahan' => $r['id']])) ?>"><?= e($r['nama']) ?></a> <small class="muted"><?= e($r['satuan']) ?></small></td>
        <td class="kanan"><?= angka($r['awal']) ?></td>
        <td class="kanan hijau"><?= $r['masuk'] ? '+' . angka($r['masuk']) : '-' ?></td>
        <td class="kanan merah"><?= $r['produksi'] ? angka($r['produksi']) : '-' ?></td>
        <td class="kanan merah"><?= $r['keluar'] ? angka($r['keluar']) : '-' ?></td>
        <td class="kanan"><?= $r['penyesuaian'] ? ($r['penyesuaian'] > 0 ? '+' : '') . angka($r['penyesuaian']) : '-' ?></td>
        <td class="kanan"><strong><?= angka($r['akhir']) ?></strong></td>
        <td class="kanan"><?= rupiah($r['akhir'] * $r['harga']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</section>

<section class="kartu">
  <h2><?= $bahanId ? 'Kartu stok' : 'Detail mutasi' ?></h2>
  <div class="tabel-wrap">
  <table class="tabel">
    <thead><tr><th>Tanggal</th><?php if (!$bahanId): ?><th>Bahan</th><?php endif; ?><th>Jenis</th><th class="kanan">Jumlah</th><th class="kanan">Stok akhir</th><th class="kanan">Harga</th><th>Supplier</th><th>Keterangan</th><th>Petugas</th></tr></thead>
    <tbody>
    <?php if (!$mutasi): ?><tr><td colspan="9" class="kosong">Tidak ada mutasi pada periode ini.</td></tr><?php endif; ?>
    <?php foreach ($mutasi as $m): ?>
      <tr>
        <td><?= tgl($m['tanggal']) ?></td>
        <?php if (!$bahanId): ?><td><?= e($m['nama']) ?></td><?php endif; ?>
        <td><span class="jenis jenis-<?= e($m['jenis']) ?>"><?= e(LABEL_JENIS[$m['jenis']] ?? $m['jenis']) ?></span></td>
        <td class="kanan <?= $m['jumlah'] < 0 ? 'merah' : 'hijau' ?>"><?= $m['jumlah'] > 0 ? '+' : '' ?><?= angka($m['jumlah']) ?> <?= e($m['satuan']) ?></td>
        <td class="kanan"><?= angka($m['stok_akhir']) ?></td>
        <td class="kanan"><?= $m['harga'] !== null ? rupiah($m['harga']) : '-' ?></td>
        <td><?= e($m['supplier'] ?? '-') ?></td>
        <td><?= e($m['keterangan']) ?></td>
        <td><?= e($m['petugas'] ?? '-') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</section>
<?php include __DIR__ . '/inc/footer.php'; ?>
