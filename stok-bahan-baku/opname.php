<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/stok.php';
cek_instalasi();
wajib_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $tanggal = post('tanggal');
    if (!valid_tanggal($tanggal)) {
        flash('error', 'Tanggal tidak valid.');
        redirect('opname.php');
    }
    $catatan = post('keterangan');
    $pdo = db();
    $pdo->beginTransaction();
    $berubah = 0;
    foreach ((array) ($_POST['fisik'] ?? []) as $bid => $fisik) {
        if (trim((string) $fisik) === '') {
            continue; // tidak dihitung
        }
        $b = satu('SELECT stok, harga FROM bahan WHERE id = ?', [(int) $bid]);
        if (!$b) {
            continue;
        }
        $selisih = round(max(ke_angka($fisik), 0) - (float) $b['stok'], 3);
        if (abs($selisih) >= 0.001) {
            catat_mutasi((int) $bid, 'penyesuaian', $selisih, $tanggal, [
                'harga' => $b['harga'], 'keterangan' => 'Stok opname' . ($catatan !== '' ? ' — ' . $catatan : ''),
            ]);
            $berubah++;
        }
    }
    $pdo->commit();
    flash('sukses', $berubah ? "Stok opname disimpan, $berubah bahan disesuaikan." : 'Tidak ada selisih stok.');
    redirect('opname.php');
}

$bahan = semua('SELECT * FROM bahan ORDER BY kategori, nama');
$riwayat = semua(
    "SELECT m.*, b.nama, b.satuan, u.nama AS petugas FROM mutasi m JOIN bahan b ON b.id = m.bahan_id
     LEFT JOIN users u ON u.id = m.user_id WHERE m.jenis = 'penyesuaian' ORDER BY m.tanggal DESC, m.id DESC LIMIT 30"
);

$judul = 'Stok Opname';
include __DIR__ . '/inc/header.php';
?>
<section class="kartu kartu-form">
  <h2>Hitung stok fisik</h2>
  <p class="muted">Isi jumlah hasil hitung di gudang. Kolom yang dikosongkan tidak diubah. Selisih otomatis dicatat sebagai penyesuaian.</p>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <div class="grid-form">
      <label>Tanggal opname<input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>"></label>
      <label class="span-2">Catatan<input name="keterangan" maxlength="150" placeholder="mis. Opname akhir bulan"></label>
    </div>
    <div class="tabel-wrap">
    <table class="tabel tabel-input">
      <thead><tr><th>Bahan</th><th>Kategori</th><th class="kanan">Stok sistem</th><th>Stok fisik</th><th class="kanan">Selisih</th></tr></thead>
      <tbody>
      <?php foreach ($bahan as $b): ?>
        <tr class="baris-opname" data-stok="<?= e((float) $b['stok']) ?>">
          <td><?= e($b['nama']) ?><br><small class="muted"><?= e($b['kode']) ?></small></td>
          <td><?= e($b['kategori']) ?></td>
          <td class="kanan"><?= angka($b['stok']) ?> <?= e($b['satuan']) ?></td>
          <td><div class="input-satuan"><input type="number" step="0.001" min="0" name="fisik[<?= (int) $b['id'] ?>]" class="isi-fisik"><span class="satuan"><?= e($b['satuan']) ?></span></div></td>
          <td class="kanan selisih muted">-</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <div class="aksi-form"><button class="btn btn-primary">Simpan hasil opname</button></div>
  </form>
</section>

<section class="kartu">
  <h2>Riwayat penyesuaian</h2>
  <div class="tabel-wrap">
  <table class="tabel">
    <thead><tr><th>Tanggal</th><th>Bahan</th><th class="kanan">Selisih</th><th class="kanan">Stok akhir</th><th>Keterangan</th><th>Petugas</th></tr></thead>
    <tbody>
    <?php if (!$riwayat): ?><tr><td colspan="6" class="kosong">Belum ada penyesuaian.</td></tr><?php endif; ?>
    <?php foreach ($riwayat as $m): ?>
      <tr>
        <td><?= tgl($m['tanggal']) ?></td>
        <td><?= e($m['nama']) ?></td>
        <td class="kanan <?= $m['jumlah'] < 0 ? 'merah' : 'hijau' ?>"><?= $m['jumlah'] > 0 ? '+' : '' ?><?= angka($m['jumlah']) ?> <?= e($m['satuan']) ?></td>
        <td class="kanan"><?= angka($m['stok_akhir']) ?></td>
        <td><?= e($m['keterangan']) ?></td>
        <td><?= e($m['petugas'] ?? '-') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</section>
<?php include __DIR__ . '/inc/footer.php'; ?>
