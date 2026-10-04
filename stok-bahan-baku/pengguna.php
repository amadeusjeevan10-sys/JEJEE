<?php
require_once __DIR__ . '/inc/auth.php';
cek_instalasi();
wajib_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $id = (int) post('id');

    if (post('aksi') === 'hapus') {
        if ($id === pengguna()['id']) {
            flash('error', 'Tidak bisa menghapus akun sendiri.');
        } else {
            q('DELETE FROM users WHERE id = ?', [$id]);
            flash('sukses', 'Pengguna dihapus.');
        }
        redirect('pengguna.php');
    }

    $username = post('username');
    $nama = post('nama');
    $peran = post('peran') === 'admin' ? 'admin' : 'staf';
    $password = (string) ($_POST['password'] ?? '');

    $galat = null;
    if (!preg_match('/^[a-zA-Z0-9_.]{3,50}$/', $username) || $nama === '') {
        $galat = 'Username 3–50 karakter (huruf, angka, titik, garis bawah) dan nama wajib diisi.';
    } elseif (nilai('SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?', [$username, $id])) {
        $galat = 'Username sudah dipakai.';
    } elseif ((!$id || $password !== '') && strlen($password) < 6) {
        $galat = 'Password minimal 6 karakter.';
    } elseif ($id === pengguna()['id'] && $peran !== 'admin') {
        $galat = 'Tidak bisa menurunkan peran akun sendiri.';
    }
    if ($galat) {
        flash('error', $galat);
        redirect('pengguna.php?' . ($id ? 'edit=' . $id : 'aksi=tambah'));
    }

    if ($id) {
        q('UPDATE users SET username = ?, nama = ?, peran = ? WHERE id = ?', [$username, $nama, $peran, $id]);
        if ($password !== '') {
            q('UPDATE users SET password = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $id]);
        }
        if ($id === pengguna()['id']) {
            $_SESSION['user']['username'] = $username;
            $_SESSION['user']['nama'] = $nama;
        }
        flash('sukses', 'Pengguna diperbarui.');
    } else {
        q('INSERT INTO users (username, nama, peran, password, created_at) VALUES (?, ?, ?, ?, ?)',
            [$username, $nama, $peran, password_hash($password, PASSWORD_DEFAULT), sekarang()]);
        flash('sukses', 'Pengguna ditambahkan.');
    }
    redirect('pengguna.php');
}

$form = null;
if (isset($_GET['edit'])) {
    $form = satu('SELECT id, username, nama, peran FROM users WHERE id = ?', [(int) $_GET['edit']]);
} elseif (($_GET['aksi'] ?? '') === 'tambah') {
    $form = ['id' => 0, 'username' => '', 'nama' => '', 'peran' => 'staf'];
}
$daftar = semua('SELECT id, username, nama, peran, created_at FROM users ORDER BY nama');

$judul = 'Pengguna';
include __DIR__ . '/inc/header.php';
?>
<?php if ($form): ?>
<section class="kartu kartu-form">
  <h2><?= $form['id'] ? 'Ubah pengguna' : 'Tambah pengguna' ?></h2>
  <form method="post" class="form grid-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
    <label>Nama<input name="nama" required maxlength="100" value="<?= e($form['nama']) ?>"></label>
    <label>Username<input name="username" required maxlength="50" value="<?= e($form['username']) ?>"></label>
    <label>Peran
      <select name="peran">
        <option value="staf" <?= $form['peran'] === 'staf' ? 'selected' : '' ?>>Staf gudang</option>
        <option value="admin" <?= $form['peran'] === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
    </label>
    <label>Password<?= $form['id'] ? ' <small class="muted">(kosongkan jika tidak diganti)</small>' : '' ?><input type="password" name="password" <?= $form['id'] ? '' : 'required' ?> minlength="6"></label>
    <div class="aksi-form span-3">
      <button class="btn btn-primary">Simpan</button>
      <a href="pengguna.php" class="btn btn-ghost">Batal</a>
    </div>
  </form>
</section>
<?php endif; ?>

<section class="kartu">
  <div class="kartu-kepala"><h2>Daftar pengguna</h2><a href="pengguna.php?aksi=tambah" class="btn btn-primary btn-sm">+ Tambah pengguna</a></div>
  <p class="muted">Staf bisa mencatat transaksi dan mengelola data master. Hanya admin yang bisa mengelola pengguna dan membatalkan produksi.</p>
  <table class="tabel">
    <thead><tr><th>Nama</th><th>Username</th><th>Peran</th><th>Dibuat</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($daftar as $u): ?>
      <tr>
        <td><?= e($u['nama']) ?></td>
        <td><code><?= e($u['username']) ?></code></td>
        <td><span class="badge <?= $u['peran'] === 'admin' ? 'badge-info' : 'badge-ok' ?>"><?= e($u['peran']) ?></span></td>
        <td><?= tgl($u['created_at']) ?></td>
        <td class="aksi">
          <a href="pengguna.php?edit=<?= (int) $u['id'] ?>" class="btn btn-sm btn-ghost">Ubah</a>
          <?php if ((int) $u['id'] !== pengguna()['id']): ?>
          <form method="post" data-konfirmasi="Hapus pengguna <?= e($u['username']) ?>?">
            <?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
            <button class="btn btn-sm btn-hapus">Hapus</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</section>
<?php include __DIR__ . '/inc/footer.php'; ?>
