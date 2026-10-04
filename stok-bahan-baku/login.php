<?php
require_once __DIR__ . '/inc/auth.php';
cek_instalasi();

if (pengguna()) {
    redirect('index.php');
}

$galat = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $u = satu('SELECT * FROM users WHERE username = ?', [post('username')]);
    if ($u && password_verify((string) ($_POST['password'] ?? ''), $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int) $u['id'], 'username' => $u['username'], 'nama' => $u['nama'], 'peran' => $u['peran']];
        redirect('index.php');
    }
    $galat = 'Username atau password salah.';
}
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="halaman-auth">
<div class="kartu-auth">
  <div class="brand brand-auth"><span class="brand-logo">面</span><span><strong><?= e(APP_NAME) ?></strong><small>Sistem Pengelolaan Bahan Baku</small></span></div>
  <?php foreach (ambil_flash() as $f): ?>
    <div class="alert alert-<?= e($f['tipe']) ?>"><?= e($f['pesan']) ?></div>
  <?php endforeach; ?>
  <?php if ($galat): ?><div class="alert alert-error"><?= e($galat) ?></div><?php endif; ?>
  <form method="post" class="form">
    <?= csrf_field() ?>
    <label>Username<input name="username" required autofocus value="<?= e(post('username')) ?>"></label>
    <label>Password<input type="password" name="password" required></label>
    <button class="btn btn-primary btn-blok">Masuk</button>
  </form>
</div>
</body>
</html>
