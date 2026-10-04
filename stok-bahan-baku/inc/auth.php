<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function pengguna(): ?array
{
    return $_SESSION['user'] ?? null;
}

function wajib_login(): void
{
    if (!pengguna()) {
        redirect('login.php');
    }
}

function wajib_admin(): void
{
    wajib_login();
    if (pengguna()['peran'] !== 'admin') {
        flash('error', 'Halaman itu hanya untuk admin.');
        redirect('index.php');
    }
}

/** Pastikan database sudah dipasang; kalau belum arahkan ke installer. */
function cek_instalasi(): void
{
    try {
        db()->query('SELECT 1 FROM users LIMIT 1');
    } catch (Throwable $ex) {
        redirect('install.php');
    }
}
