<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/schema.php';

session_start();

$sudahTerpasang = false;
$galatKoneksi = null;
try {
    db()->query('SELECT 1 FROM users LIMIT 1');
    $sudahTerpasang = true;
} catch (PDOException $ex) {
    // Tabel belum ada (atau koneksi gagal) — bedakan untuk pesan yang jelas.
    try {
        db();
    } catch (PDOException $ex2) {
        $galatKoneksi = $ex2->getMessage();
    }
}

$pesan = null;
if (!$sudahTerpasang && !$galatKoneksi && $_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $username = post('username');
    $nama = post('nama');
    $password = (string) ($_POST['password'] ?? '');
    $contoh = !empty($_POST['contoh']);

    if ($username === '' || $nama === '' || strlen($password) < 6) {
        $pesan = 'Isi semua kolom. Password minimal 6 karakter.';
    } else {
        $pdo = db();
        foreach (skema(DB_DRIVER) as $sql) {
            $pdo->exec($sql);
        }
        $now = sekarang();
        q('INSERT INTO users (username, nama, peran, password, created_at) VALUES (?, ?, ?, ?, ?)',
            [$username, $nama, 'admin', password_hash($password, PASSWORD_DEFAULT), $now]);

        if ($contoh) {
            $data = data_awal();
            foreach ($data['supplier'] as $s) {
                q('INSERT INTO supplier (nama, telepon, alamat, keterangan) VALUES (?, ?, ?, ?)', $s);
            }
            $idBahan = [];
            foreach ($data['bahan'] as $b) {
                q('INSERT INTO bahan (kode, nama, kategori, satuan, stok, stok_min, harga, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                    [...$b, $now]);
                $idBahan[$b[0]] = (int) $pdo->lastInsertId();
                q("INSERT INTO mutasi (tanggal, bahan_id, jenis, jumlah, stok_akhir, harga, keterangan, user_id, created_at)
                   VALUES (?, ?, 'penyesuaian', ?, ?, ?, 'Stok awal', 1, ?)",
                    [date('Y-m-d'), $idBahan[$b[0]], $b[4], $b[4], $b[6], $now]);
            }
            foreach ($data['produk'] as [$nama, $hasil, $satuan, $ket, $resep]) {
                q('INSERT INTO produk (nama, hasil_per_batch, satuan_hasil, keterangan) VALUES (?, ?, ?, ?)', [$nama, $hasil, $satuan, $ket]);
                $pid = (int) $pdo->lastInsertId();
                foreach ($resep as $kode => $jml) {
                    q('INSERT INTO resep (produk_id, bahan_id, jumlah) VALUES (?, ?, ?)', [$pid, $idBahan[$kode], $jml]);
                }
            }
        }
        flash('sukses', 'Instalasi berhasil. Silakan masuk.');
        redirect('login.php');
    }
}
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Instalasi · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="halaman-auth">
<div class="kartu-auth lebar">
  <div class="brand brand-auth"><span class="brand-logo">面</span><span><strong><?= e(APP_NAME) ?></strong><small>Instalasi</small></span></div>

  <?php if ($galatKoneksi): ?>
    <div class="alert alert-error">Tidak bisa terhubung ke database: <?= e($galatKoneksi) ?></div>
    <p>Periksa pengaturan di <code>config.php</code>. Untuk MySQL, buat dulu database
       <code><?= e(DB_NAME) ?></code> (misalnya lewat phpMyAdmin), lalu muat ulang halaman ini.</p>
  <?php elseif ($sudahTerpasang): ?>
    <div class="alert alert-sukses">Aplikasi sudah terpasang.</div>
    <a class="btn btn-primary btn-blok" href="login.php">Ke halaman masuk</a>
  <?php else: ?>
    <p class="muted">Driver database: <strong><?= e(DB_DRIVER) ?></strong>. Buat akun admin pertama.</p>
    <?php if ($pesan): ?><div class="alert alert-error"><?= e($pesan) ?></div><?php endif; ?>
    <form method="post" class="form">
      <?= csrf_field() ?>
      <label>Nama lengkap<input name="nama" required value="<?= e(post('nama', 'Administrator')) ?>"></label>
      <label>Username<input name="username" required value="<?= e(post('username', 'admin')) ?>"></label>
      <label>Password<input type="password" name="password" required minlength="6"></label>
      <label class="cek"><input type="checkbox" name="contoh" value="1" checked> Isi data contoh (bahan, supplier, resep mie &amp; kulit pangsit)</label>
      <button class="btn btn-primary btn-blok">Pasang sekarang</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
