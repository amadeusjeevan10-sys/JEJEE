<?php
/**
 * Semua perubahan stok lewat fungsi ini supaya stok bahan dan riwayat mutasi selalu sinkron.
 * $jumlah positif = stok bertambah, negatif = stok berkurang.
 * Panggil di dalam transaksi database.
 */
function catat_mutasi(int $bahanId, string $jenis, float $jumlah, string $tanggal, array $ekstra = []): void
{
    $bahan = satu('SELECT id, nama, stok, satuan FROM bahan WHERE id = ?', [$bahanId]);
    if (!$bahan) {
        throw new RuntimeException('Bahan tidak ditemukan.');
    }
    $stokBaru = round((float) $bahan['stok'] + $jumlah, 3);
    if ($stokBaru < 0) {
        throw new RuntimeException(sprintf(
            'Stok %s tidak cukup (tersedia %s %s, dibutuhkan %s %s).',
            $bahan['nama'], angka($bahan['stok']), $bahan['satuan'], angka(-$jumlah), $bahan['satuan']
        ));
    }

    q('UPDATE bahan SET stok = ? WHERE id = ?', [$stokBaru, $bahanId]);
    q(
        'INSERT INTO mutasi (tanggal, bahan_id, jenis, jumlah, stok_akhir, harga, supplier_id, produksi_id, keterangan, user_id, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $tanggal, $bahanId, $jenis, $jumlah, $stokBaru,
            $ekstra['harga'] ?? null,
            $ekstra['supplier_id'] ?? null,
            $ekstra['produksi_id'] ?? null,
            $ekstra['keterangan'] ?? null,
            pengguna()['id'] ?? null,
            sekarang(),
        ]
    );
}

const LABEL_JENIS = [
    'masuk'       => 'Masuk',
    'keluar'      => 'Keluar',
    'produksi'    => 'Produksi',
    'penyesuaian' => 'Penyesuaian',
];
