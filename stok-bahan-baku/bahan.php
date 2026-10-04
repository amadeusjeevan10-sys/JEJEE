<?php
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/stok.php';
cek_instalasi();
wajib_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $aksi = post('aksi');

    if ($aksi === 'hapus') {
        $id = (int) post('id');
        $dipakai = (int) nilai('SELECT COUNT(*) FROM resep WHERE bahan_id = ?', [$id]);
        $mutasi = (int) nilai("SELECT COUNT(*) FROM mutasi WHERE bahan_id = ? AND keterangan <> 'Stok awal'", [$id]);
        if ($dipakai || $mutasi) {
            flash('error', 'Bahan tidak bisa dihapus karena sudah dipakai di resep atau memiliki riwayat transaksi.');
        } else {
            q('DELETE FROM mutasi WHERE bahan_id = ?', [$id]);
            q('DELETE FROM bahan WHERE id = ?', [$id]);
            flash('sukses', 'Bahan dihapus.');
        }
        redirect('bahan.php');
    }

    $id = (int) post('id');
    $data = [
        'kode'     => strtoupper(post('kode')),
        'nama'     => post('nama'),
        'kategori' => post('kategori'),
        'satuan'   => post('satuan'),
        'stok_min' => ke_angka(post('stok_min')),
        'harga'    => ke_angka(post('harga')),
    ];
    $stokAwal = ke_angka(post('stok_awal'));

    $galat = [];
    if ($data['kode'] === '' || $data['nama'] === '') $galat[] = 'Kode dan nama wajib diisi.';
    if (!in_array($data['kategori'], KATEGORI_BAHAN, true)) $galat[] = 'Kategori tidak valid.';
    if ($data['satuan'] === '') $galat[] = 'Satuan wajib diisi.';
    if ($data['stok_min'] < 0 || $data['harga'] < 0 || $stokAwal < 0) $galat[] = 'Angka tidak boleh negatif.';
    if (nilai('SELECT COUNT(*) FROM bahan WHERE kode = ? AND id <> ?', [$data['kode'], $id])) $galat[] = 'Kode sudah dipakai bahan lain.';

    if ($galat) {
        flash('error', implode(' ', $galat));
        redirect('bahan.php?' . ($id ? 'edit=' . $id : 'aksi=tambah'));
    }

    if ($id) {
        q('UPDATE bahan SET kode = ?, nama = ?, kategori = ?, satuan = ?, stok_min = ?, harga = ? WHERE id = ?', [...array_values($data), $id]);
        flash('sukses', 'Data bahan diperbarui.');
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        q('INSERT INTO bahan (kode, nama, kategori, satuan, stok_min, harga, stok, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, ?)', [...array_values($data), sekarang()]);
        $baru = (int) $pdo->lastInsertId();
        if ($stokAwal > 0) {
            catat_mutasi($baru, 'penyesuaian', $stokAwal, date('Y-m-d'), ['harga' => $data['harga'], 'keterangan' => 'Stok awal']);
        }
        $pdo->commit();
        flash('sukses', 'Bahan baru ditambahkan.');
    }
    redirect('bahan.php');
}

$form = null;
if (isset($_GET['edit'])) {
    $form = satu('SELECT * FROM bahan WHERE id = ?', [(int) $_GET['edit']]);
} elseif (($_GET['aksi'] ?? '') === 'tambah') {
    $no = (int) nilai('SELECT COUNT(*) FROM bahan') + 1;
    do {
        $kode = sprintf('BB-%03d', $no++);
    } while (nilai('SELECT COUNT(*) FROM bahan WHERE kode = ?', [$kode]));
    $form = ['id' => 0, 'kode' => $kode, 'nama' => '', 'kategori' => KATEGORI_BAHAN[0], 'satuan' => 'kg', 'stok_min' => 0, 'harga' => 0];
}

$cari = trim($_GET['cari'] ?? '');
$kat = $_GET['kategori'] ?? '';
$where = [];
$par = [];
if ($cari !== '') { $where[] = '(nama LIKE ? OR kode LIKE ?)'; $par[] = "%$cari%"; $par[] = "%$cari%"; }
if ($kat !== '') { $where[] = 'kategori = ?'; $par[] = $kat; }
$daftar = semua('SELECT * FROM bahan' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY kategori, nama', $par);

$judul = 'Bahan Baku';
include __DIR__ . '/inc/header.php';
?>
<?php if ($form): ?>
<section class="kartu kartu-form">
  <h2><?= $form['id'] ? 'Ubah bahan' : 'Tambah bahan baku' ?></h2>
  <form method="post" class="form grid-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
    <label>Kode<input name="kode" required maxlength="20" value="<?= e($form['kode']) ?>"></label>
    <label class="span-2">Nama bahan<input name="nama" required maxlength="100" value="<?= e($form['nama']) ?>" placeholder="mis. Tepung terigu protein tinggi"></label>
    <label>Kategori
      <select name="kategori"><?php foreach (KATEGORI_BAHAN as $k): ?><option <?= $form['kategori'] === $k ? 'selected' : '' ?>><?= e($k) ?></option><?php endforeach; ?></select>
    </label>
    <label>Satuan
      <input name="satuan" list="daftar-satuan" required maxlength="20" value="<?= e($form['satuan']) ?>">
      <datalist id="daftar-satuan"><?php foreach (SATUAN as $s): ?><option value="<?= e($s) ?>"><?php endforeach; ?></datalist>
    </label>
    <label>Stok minimum<input type="number" step="0.001" min="0" name="stok_min" value="<?= e((float) $form['stok_min']) ?>"></label>
    <label>Harga per satuan (Rp)<input type="number" step="1" min="0" name="harga" value="<?= e((float) $form['harga']) ?>"></label>
    <?php if (!$form['id']): ?>
      <label>Stok awal<input type="number" step="0.001" min="0" name="stok_awal" value="0"></label>
    <?php else: ?>
      <p class="muted span-2">Stok saat ini: <strong><?= angka($form['stok']) ?> <?= e($form['satuan']) ?></strong>. Stok hanya berubah lewat transaksi masuk, keluar, produksi, atau stok opname.</p>
    <?php endif; ?>
    <div class="aksi-form span-3">
      <button class="btn btn-primary">Simpan</button>
      <a href="bahan.php" class="btn btn-ghost">Batal</a>
    </div>
  </form>
</section>
<?php endif; ?>

<section class="kartu">
  <div class="kartu-kepala">
    <form class="filter" method="get">
      <input name="cari" placeholder="Cari nama / kode…" value="<?= e($cari) ?>">
      <select name="kategori" onchange="this.form.submit()">
        <option value="">Semua kategori</option>
        <?php foreach (KATEGORI_BAHAN as $k): ?><option <?= $kat === $k ? 'selected' : '' ?>><?= e($k) ?></option><?php endforeach; ?>
      </select>
      <button class="btn btn-sm">Cari</button>
    </form>
    <a href="bahan.php?aksi=tambah" class="btn btn-primary btn-sm">+ Tambah bahan</a>
  </div>
  <div class="tabel-wrap">
  <table class="tabel">
    <thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th class="kanan">Stok</th><th class="kanan">Minimum</th><th class="kanan">Harga</th><th class="kanan">Nilai</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (!$daftar): ?><tr><td colspan="9" class="kosong">Belum ada bahan.</td></tr><?php endif; ?>
    <?php foreach ($daftar as $b): $tipis = (float) $b['stok'] <= (float) $b['stok_min']; ?>
      <tr>
        <td><code><?= e($b['kode']) ?></code></td>
        <td><?= e($b['nama']) ?></td>
        <td><?= e($b['kategori']) ?></td>
        <td class="kanan"><strong><?= angka($b['stok']) ?></strong> <?= e($b['satuan']) ?></td>
        <td class="kanan"><?= angka($b['stok_min']) ?></td>
        <td class="kanan"><?= rupiah($b['harga']) ?></td>
        <td class="kanan"><?= rupiah($b['stok'] * $b['harga']) ?></td>
        <td><span class="badge <?= $tipis ? 'badge-bahaya' : 'badge-ok' ?>"><?= $tipis ? 'Menipis' : 'Aman' ?></span></td>
        <td class="aksi">
          <a href="laporan.php?bahan=<?= (int) $b['id'] ?>" class="btn btn-sm btn-ghost">Kartu stok</a>
          <a href="bahan.php?edit=<?= (int) $b['id'] ?>" class="btn btn-sm btn-ghost">Ubah</a>
          <form method="post" data-konfirmasi="Hapus bahan <?= e($b['nama']) ?>?">
            <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
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
