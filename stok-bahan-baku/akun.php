<?php
require_once __DIR__ . '/inc/auth.php';
cek_instalasi();
wajib_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $u = satu('SELECT * FROM users WHERE id = ?', [pengguna()['id']]);
    $lama = (string) ($_POST['lama'] ?? '');
    $baru = (string) ($_POST['baru'] ?? '');
    if (!password_verify($lama, $u['password'])) {
        flash('error', 'Password lama salah.');
    } elseif (strlen($baru) < 6 || $baru !== ($_POST['ulang'] ?? '')) {
        flash('error', 'Password baru minimal 6 karakter dan harus sama dengan konfirmasi.');
    } else {
        q('UPDATE users SET password = ? WHERE id = ?', [password_hash($baru, PASSWORD_DEFAULT), $u['id']]);
        flash('sukses', 'Password berhasil diganti.');
    }
    redirect('akun.php');
}

$judul = 'Akun Saya';
include __DIR__ . '/inc/header.php';
?>
<section class="kartu kartu-form sempit">
  <h2>Ganti password</h2>
  <p class="muted">Masuk sebagai <strong><?= e(pengguna()['username']) ?></strong> (<?= e(pengguna()['peran']) ?>)</p>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <label>Password lama<input type="password" name="lama" required></label>
    <label>Password baru<input type="password" name="baru" required minlength="6"></label>
    <label>Ulangi password baru<input type="password" name="ulang" required minlength="6"></label>
    <button class="btn btn-primary">Simpan</button>
  </form>
</section>
<?php include __DIR__ . '/inc/footer.php'; ?>
