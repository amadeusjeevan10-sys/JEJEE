<?php
/** @var string $judul */
$halaman = basename($_SERVER['SCRIPT_NAME'], '.php');
$menu = [
    'Utama' => [
        'index'    => ['Dashboard', '▦'],
    ],
    'Data Master' => [
        'bahan'    => ['Bahan Baku', '◫'],
        'produk'   => ['Produk & Resep', '❒'],
        'supplier' => ['Supplier', '⛟'],
    ],
    'Transaksi' => [
        'masuk'    => ['Bahan Masuk', '↓'],
        'keluar'   => ['Bahan Keluar', '↑'],
        'produksi' => ['Produksi', '⚙'],
        'opname'   => ['Stok Opname', '✓'],
    ],
    'Laporan' => [
        'laporan'  => ['Kartu Stok & Mutasi', '☰'],
    ],
];
if ((pengguna()['peran'] ?? '') === 'admin') {
    $menu['Pengaturan'] = ['pengguna' => ['Pengguna', '☺']];
}
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul ?? 'Dashboard') ?> · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="layout">
  <aside class="sidebar" id="sidebar">
    <a href="index.php" class="brand">
      <span class="brand-logo">面</span>
      <span><strong><?= e(APP_NAME) ?></strong><small>Sistem Bahan Baku</small></span>
    </a>
    <nav>
      <?php foreach ($menu as $grup => $item): ?>
        <div class="nav-grup"><?= e($grup) ?></div>
        <?php foreach ($item as $file => [$label, $ikon]): ?>
          <a href="<?= $file ?>.php" class="<?= $halaman === $file ? 'aktif' : '' ?>">
            <span class="ikon"><?= $ikon ?></span><?= e($label) ?>
          </a>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </nav>
  </aside>
  <div class="konten">
    <header class="topbar">
      <button class="tombol-menu" type="button" onclick="document.getElementById('sidebar').classList.toggle('buka')" aria-label="Menu">☰</button>
      <h1><?= e($judul ?? 'Dashboard') ?></h1>
      <div class="akun">
        <span class="avatar"><?= e(strtoupper(substr(pengguna()['nama'] ?? '?', 0, 1))) ?></span>
        <span class="akun-nama"><?= e(pengguna()['nama'] ?? '') ?><small><?= e(pengguna()['peran'] ?? '') ?></small></span>
        <a href="akun.php" class="btn btn-ghost btn-sm">Akun</a>
        <a href="logout.php" class="btn btn-ghost btn-sm">Keluar</a>
      </div>
    </header>
    <main class="isi">
      <?php foreach (ambil_flash() as $f): ?>
        <div class="alert alert-<?= e($f['tipe']) ?>"><?= e($f['pesan']) ?></div>
      <?php endforeach; ?>
