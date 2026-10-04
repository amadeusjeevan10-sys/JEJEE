<?php
require_once __DIR__ . '/inc/auth.php';
cek_instalasi();
wajib_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $id = (int) post('id');

    if (post('aksi') === 'hapus') {
        q('DELETE FROM supplier WHERE id = ?', [$id]);
        flash('sukses', 'Supplier dihapus. Riwayat bahan masuk tetap tersimpan.');
        redirect('supplier.php');
    }

    $data = [post('nama'), post('telepon'), post('alamat'), post('keterangan')];
    if ($data[0] === '') {
        flash('error', 'Nama supplier wajib diisi.');
        redirect('supplier.php?' . ($id ? 'edit=' . $id : 'aksi=tambah'));
    }
    if ($id) {
        q('UPDATE supplier SET nama = ?, telepon = ?, alamat = ?, keterangan = ? WHERE id = ?', [...$data, $id]);
        flash('sukses', 'Supplier diperbarui.');
    } else {
        q('INSERT INTO supplier (nama, telepon, alamat, keterangan) VALUES (?, ?, ?, ?)', $data);
        flash('sukses', 'Supplier ditambahkan.');
    }
    redirect('supplier.php');
}

$form = null;
if (isset($_GET['edit'])) {
    $form = satu('SELECT * FROM supplier WHERE id = ?', [(int) $_GET['edit']]);
} elseif (($_GET['aksi'] ?? '') === 'tambah') {
    $form = ['id' => 0, 'nama' => '', 'telepon' => '', 'alamat' => '', 'keterangan' => ''];
}

$daftar = semua(
    "SELECT s.*, COUNT(m.id) AS transaksi, COALESCE(SUM(m.jumlah * m.harga), 0) AS total
     FROM supplier s LEFT JOIN mutasi m ON m.supplier_id = s.id AND m.jenis = 'masuk'
     GROUP BY s.id, s.nama, s.telepon, s.alamat, s.keterangan ORDER BY s.nama"
);

$judul = 'Supplier';
include __DIR__ . '/inc/header.php';
?>
<?php if ($form): ?>
<section class="kartu kartu-form">
  <h2><?= $form['id'] ? 'Ubah supplier' : 'Tambah supplier' ?></h2>
  <form method="post" class="form grid-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
    <label class="span-2">Nama<input name="nama" required maxlength="100" value="<?= e($form['nama']) ?>"></label>
    <label>Telepon / WA<input name="telepon" maxlength="30" value="<?= e($form['telepon']) ?>"></label>
    <label class="span-2">Alamat<input name="alamat" maxlength="255" value="<?= e($form['alamat']) ?>"></label>
    <label>Keterangan<input name="keterangan" maxlength="255" value="<?= e($form['keterangan']) ?>" placeholder="mis. tepung, telur"></label>
    <div class="aksi-form span-3">
      <button class="btn btn-primary">Simpan</button>
      <a href="supplier.php" class="btn btn-ghost">Batal</a>
    </div>
  </form>
</section>
<?php endif; ?>

<section class="kartu">
  <div class="kartu-kepala"><h2>Daftar supplier</h2><a href="supplier.php?aksi=tambah" class="btn btn-primary btn-sm">+ Tambah supplier</a></div>
  <div class="tabel-wrap">
  <table class="tabel">
    <thead><tr><th>Nama</th><th>Telepon</th><th>Alamat</th><th>Keterangan</th><th class="kanan">Transaksi</th><th class="kanan">Total belanja</th><th></th></tr></thead>
    <tbody>
    <?php if (!$daftar): ?><tr><td colspan="7" class="kosong">Belum ada supplier.</td></tr><?php endif; ?>
    <?php foreach ($daftar as $s): ?>
      <tr>
        <td><strong><?= e($s['nama']) ?></strong></td>
        <td><?= e($s['telepon']) ?></td>
        <td><?= e($s['alamat']) ?></td>
        <td><?= e($s['keterangan']) ?></td>
        <td class="kanan"><?= (int) $s['transaksi'] ?></td>
        <td class="kanan"><?= rupiah($s['total']) ?></td>
        <td class="aksi">
          <a href="supplier.php?edit=<?= (int) $s['id'] ?>" class="btn btn-sm btn-ghost">Ubah</a>
          <form method="post" data-konfirmasi="Hapus supplier <?= e($s['nama']) ?>?">
            <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
            <button class="btn btn-sm btn-hapus">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</section>
<?php include __DIR__ . '/inc/footer.php'; ?>
