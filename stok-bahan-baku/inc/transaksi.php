<?php
/**
 * Halaman bersama untuk transaksi masuk & keluar.
 * Variabel yang harus diset sebelum include: $jenis ('masuk'|'keluar'), $judul.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/stok.php';
cek_instalasi();
wajib_login();

$masuk = $jenis === 'masuk';
$file = $masuk ? 'masuk.php' : 'keluar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $tanggal = post('tanggal');
    $bahanIds = (array) ($_POST['bahan_id'] ?? []);
    $jumlahs = (array) ($_POST['jumlah'] ?? []);
    $hargas = (array) ($_POST['harga'] ?? []);
    $supplierId = (int) post('supplier_id') ?: null;
    $ket = post('keterangan');
    if (!$masuk) {
        $ket = post('alasan') . ($ket !== '' ? ' — ' . $ket : '');
    }

    $baris = [];
    foreach ($bahanIds as $i => $bid) {
        $jml = ke_angka($jumlahs[$i] ?? 0);
        if ((int) $bid && $jml > 0) {
            $baris[] = ['bahan_id' => (int) $bid, 'jumlah' => $jml, 'harga' => ke_angka($hargas[$i] ?? 0)];
        }
    }

    if (!valid_tanggal($tanggal)) {
        flash('error', 'Tanggal tidak valid.');
    } elseif (!$baris) {
        flash('error', 'Isi minimal satu bahan dengan jumlah lebih dari 0.');
    } else {
        $pdo = db();
        try {
            $pdo->beginTransaction();
            foreach ($baris as $b) {
                if ($masuk) {
                    catat_mutasi($b['bahan_id'], 'masuk', $b['jumlah'], $tanggal, [
                        'harga' => $b['harga'], 'supplier_id' => $supplierId, 'keterangan' => $ket,
                    ]);
                    if ($b['harga'] > 0) {
                        q('UPDATE bahan SET harga = ? WHERE id = ?', [$b['harga'], $b['bahan_id']]);
                    }
                } else {
                    $harga = (float) nilai('SELECT harga FROM bahan WHERE id = ?', [$b['bahan_id']]);
                    catat_mutasi($b['bahan_id'], 'keluar', -$b['jumlah'], $tanggal, ['harga' => $harga, 'keterangan' => $ket]);
                }
            }
            $pdo->commit();
            flash('sukses', count($baris) . ' bahan berhasil dicatat ' . ($masuk ? 'masuk' : 'keluar') . '.');
        } catch (RuntimeException $ex) {
            $pdo->rollBack();
            flash('error', $ex->getMessage());
        }
    }
    redirect($file);
}

$bahan = semua('SELECT id, kode, nama, satuan, stok, harga FROM bahan ORDER BY nama');
$supplier = semua('SELECT id, nama FROM supplier ORDER BY nama');
$riwayat = semua(
    'SELECT m.*, b.nama, b.satuan, s.nama AS supplier, u.nama AS petugas
     FROM mutasi m JOIN bahan b ON b.id = m.bahan_id
     LEFT JOIN supplier s ON s.id = m.supplier_id
     LEFT JOIN users u ON u.id = m.user_id
     WHERE m.jenis = ? ORDER BY m.tanggal DESC, m.id DESC LIMIT 50',
    [$jenis]
);

include __DIR__ . '/header.php';
?>
<section class="kartu kartu-form">
  <h2><?= $masuk ? 'Catat pembelian / bahan masuk' : 'Catat bahan keluar' ?></h2>
  <?php if (!$masuk): ?><p class="muted">Untuk pemakaian produksi, gunakan menu <a href="produksi.php">Produksi</a> agar bahan berkurang otomatis sesuai resep.</p><?php endif; ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <div class="grid-form">
      <label>Tanggal<input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>"></label>
      <?php if ($masuk): ?>
        <label>Supplier
          <select name="supplier_id"><option value="">— Tanpa supplier —</option>
            <?php foreach ($supplier as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['nama']) ?></option><?php endforeach; ?>
          </select>
        </label>
        <label>Keterangan<input name="keterangan" maxlength="200" placeholder="No. nota / catatan"></label>
      <?php else: ?>
        <label>Alasan
          <select name="alasan"><option>Rusak / kedaluwarsa</option><option>Tumpah / terbuang</option><option>Retur ke supplier</option><option>Dipakai di luar produksi</option><option>Lainnya</option></select>
        </label>
        <label>Keterangan<input name="keterangan" maxlength="150" placeholder="Catatan tambahan"></label>
      <?php endif; ?>
    </div>

    <table class="tabel tabel-input" id="baris-bahan">
      <thead><tr><th>Bahan</th><th class="kanan">Stok</th><th>Jumlah</th><?php if ($masuk): ?><th>Harga / satuan</th><th class="kanan">Subtotal</th><?php endif; ?><th></th></tr></thead>
      <tbody>
        <tr class="baris">
          <td>
            <select name="bahan_id[]" class="pilih-bahan" required>
              <option value="">— Pilih bahan —</option>
              <?php foreach ($bahan as $b): ?>
                <option value="<?= (int) $b['id'] ?>" data-stok="<?= e(angka($b['stok'])) ?>" data-satuan="<?= e($b['satuan']) ?>" data-harga="<?= e((float) $b['harga']) ?>"><?= e($b['kode'] . ' · ' . $b['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td class="kanan info-stok muted">-</td>
          <td><div class="input-satuan"><input type="number" name="jumlah[]" step="0.001" min="0.001" required class="isi-jumlah"><span class="satuan"></span></div></td>
          <?php if ($masuk): ?>
            <td><input type="number" name="harga[]" step="1" min="0" class="isi-harga"></td>
            <td class="kanan subtotal">-</td>
          <?php endif; ?>
          <td><button type="button" class="btn btn-sm btn-ghost hapus-baris" title="Hapus baris">✕</button></td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="<?= $masuk ? 4 : 3 ?>"><button type="button" class="btn btn-sm" id="tambah-baris">+ Tambah baris</button></td>
          <?php if ($masuk): ?><td class="kanan"><strong id="total">Rp 0</strong></td><?php endif; ?>
          <td></td>
        </tr>
      </tfoot>
    </table>
    <div class="aksi-form"><button class="btn btn-primary">Simpan transaksi</button></div>
  </form>
</section>

<section class="kartu">
  <div class="kartu-kepala"><h2>Riwayat <?= $masuk ? 'bahan masuk' : 'bahan keluar' ?> (50 terakhir)</h2><a href="laporan.php?jenis=<?= e($jenis) ?>" class="btn btn-sm">Laporan lengkap</a></div>
  <div class="tabel-wrap">
  <table class="tabel">
    <thead><tr><th>Tanggal</th><th>Bahan</th><th class="kanan">Jumlah</th><?php if ($masuk): ?><th class="kanan">Harga</th><th class="kanan">Subtotal</th><th>Supplier</th><?php endif; ?><th>Keterangan</th><th>Petugas</th></tr></thead>
    <tbody>
    <?php if (!$riwayat): ?><tr><td colspan="8" class="kosong">Belum ada transaksi.</td></tr><?php endif; ?>
    <?php foreach ($riwayat as $m): ?>
      <tr>
        <td><?= tgl($m['tanggal']) ?></td>
        <td><?= e($m['nama']) ?></td>
        <td class="kanan"><?= angka(abs($m['jumlah'])) ?> <?= e($m['satuan']) ?></td>
        <?php if ($masuk): ?>
          <td class="kanan"><?= rupiah($m['harga']) ?></td>
          <td class="kanan"><?= rupiah($m['jumlah'] * $m['harga']) ?></td>
          <td><?= e($m['supplier'] ?? '-') ?></td>
        <?php endif; ?>
        <td><?= e($m['keterangan']) ?></td>
        <td><?= e($m['petugas'] ?? '-') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
