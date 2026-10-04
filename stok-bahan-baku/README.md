# Gudang Mie & Pangsit — Sistem Pengelolaan Bahan Baku

Aplikasi web **PHP native** (tanpa framework) untuk mengelola stok bahan baku
produksi **mie** dan **kulit pangsit**: tepung, telur, garam, air abu, minyak,
pewarna, sampai plastik kemasan.

## Fitur

| Menu | Fungsi |
|---|---|
| **Dashboard** | Ringkasan stok, nilai persediaan, belanja bulan ini, daftar bahan yang perlu dibeli, dan **kapasitas produksi** (berapa batch mie / kulit pangsit yang masih bisa dibuat dari stok sekarang). |
| **Bahan Baku** | Tambah/ubah/hapus bahan, kategori, satuan, stok minimum, harga. Status *Menipis* otomatis. |
| **Produk & Resep** | Resep per batch (mis. 1 batch mie = 10 kg terigu + 20 butir telur + …). Biaya bahan per batch dan per kg dihitung otomatis. |
| **Supplier** | Data pemasok + total belanja per supplier. |
| **Bahan Masuk** | Catat pembelian beberapa bahan sekaligus (harga terakhir ikut diperbarui). |
| **Bahan Keluar** | Bahan rusak, tumpah, retur, dll. |
| **Produksi** | Pilih produk + jumlah batch → stok semua bahan di resep **berkurang otomatis**. Pratinjau kebutuhan bahan, dan produksi ditolak bila stok tidak cukup. Admin bisa membatalkan produksi (stok dikembalikan). |
| **Stok Opname** | Masukkan hasil hitung fisik, selisih dicatat sebagai penyesuaian. |
| **Kartu Stok & Mutasi** | Rekap stok awal–masuk–produksi–keluar–akhir per periode, kartu stok per bahan, **unduh CSV** (bisa dibuka di Excel), dan **cetak**. |
| **Pengguna** | Peran *admin* dan *staf gudang*. |

Setiap perubahan stok selalu tercatat di tabel `mutasi`, jadi riwayat stok
setiap bahan bisa dilacak dan selalu cocok dengan stoknya.

## Kebutuhan

- PHP 7.4+ (sudah dites di PHP 8.3) dengan ekstensi PDO
- MySQL/MariaDB (XAMPP, Laragon, hosting) **atau** SQLite (tanpa server database)

## Cara menjalankan

### Dengan XAMPP / Laragon (MySQL)

1. Salin folder `stok-bahan-baku` ke `htdocs` (XAMPP) atau `www` (Laragon).
2. Buka phpMyAdmin, buat database bernama `stok_bahan_baku`.
3. Sesuaikan user/password database di `config.php` bila perlu (default `root` tanpa password).
4. Buka `http://localhost/stok-bahan-baku/` → otomatis diarahkan ke halaman
   instalasi. Buat akun admin, centang *Isi data contoh* bila ingin langsung mencoba.

Alternatif: impor `database.sql` lewat phpMyAdmin (login awal `admin` / `admin123`, segera ganti password di menu Akun).

### Tanpa MySQL (SQLite)

Ubah `DB_DRIVER` di `config.php` menjadi `'sqlite'`, lalu:

```bash
cd stok-bahan-baku
php -S localhost:8000
```

Buka `http://localhost:8000`. File database dibuat di `data/stok.sqlite`
(folder `data/` harus bisa ditulis).

## Struktur folder

```
stok-bahan-baku/
├── config.php        # pengaturan database
├── install.php       # instalasi pertama + data contoh
├── index.php         # dashboard
├── bahan.php  produk.php  supplier.php
├── masuk.php  keluar.php  produksi.php  opname.php
├── laporan.php  pengguna.php  akun.php  login.php  logout.php
├── database.sql      # skema MySQL untuk impor manual
├── inc/              # koneksi DB, helper, autentikasi, layout, logika stok
├── assets/           # style.css, app.js
└── data/             # file SQLite (bila dipakai)
```

## Keamanan

- Password di-hash dengan `password_hash()`
- Semua query memakai *prepared statement* (aman dari SQL injection)
- Semua output di-*escape* (aman dari XSS)
- Token CSRF di setiap form
- Folder `inc/` dan `data/` ditutup dari akses langsung lewat `.htaccess` (Apache)
