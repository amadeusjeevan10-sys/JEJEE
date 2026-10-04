<?php
function e($teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

/** Angka stok: buang nol di belakang koma, format Indonesia. */
function angka($n): string
{
    $n = round((float) $n, 3);
    $desimal = (floor($n) == $n) ? 0 : (round($n, 1) == $n ? 1 : (round($n, 2) == $n ? 2 : 3));
    return number_format($n, $desimal, ',', '.');
}

function rupiah($n): string
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}

function tgl($tanggal): string
{
    if (!$tanggal) {
        return '-';
    }
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($tanggal);
    return date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
}

function sekarang(): string
{
    return date('Y-m-d H:i:s');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $tipe, string $pesan): void
{
    $_SESSION['flash'][] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function ambil_flash(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function cek_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}

function post(string $kunci, $default = '')
{
    $v = $_POST[$kunci] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

/** Ubah input angka (boleh pakai koma) menjadi float. */
function ke_angka($v): float
{
    $v = str_replace(' ', '', (string) $v);
    if (strpos($v, ',') !== false) {
        $v = str_replace(['.', ','], ['', '.'], $v);
    }
    return is_numeric($v) ? (float) $v : 0.0;
}

function valid_tanggal(string $t): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $t);
    return $d && $d->format('Y-m-d') === $t;
}

const KATEGORI_BAHAN = ['Tepung', 'Telur', 'Bahan Pengembang & Garam', 'Minyak', 'Pewarna & Perasa', 'Kemasan', 'Lainnya'];
const SATUAN = ['kg', 'gram', 'liter', 'ml', 'butir', 'pcs', 'pak', 'sak', 'roll'];
