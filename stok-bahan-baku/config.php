<?php
/**
 * Konfigurasi aplikasi.
 *
 * DB_DRIVER:
 *   'mysql'  -> untuk XAMPP/Laragon/hosting (buat database dulu, lalu buka install.php
 *               atau impor database.sql lewat phpMyAdmin)
 *   'sqlite' -> tanpa server database, file disimpan di folder data/
 */
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'mysql');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'stok_bahan_baku');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

define('SQLITE_PATH', __DIR__ . '/data/stok.sqlite');

define('APP_NAME', 'Gudang Mie & Pangsit');
date_default_timezone_set('Asia/Jakarta');
