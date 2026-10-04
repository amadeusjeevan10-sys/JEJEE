<?php
/** Struktur tabel. {PK} dan {ENGINE} diganti sesuai driver database. */
function skema(string $driver): array
{
    $tabel = [
        "CREATE TABLE users (
            id {PK},
            username VARCHAR(50) NOT NULL UNIQUE,
            nama VARCHAR(100) NOT NULL,
            peran VARCHAR(20) NOT NULL DEFAULT 'staf',
            password VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL
        ){ENGINE}",
        "CREATE TABLE supplier (
            id {PK},
            nama VARCHAR(100) NOT NULL,
            telepon VARCHAR(30) NULL,
            alamat VARCHAR(255) NULL,
            keterangan VARCHAR(255) NULL
        ){ENGINE}",
        "CREATE TABLE bahan (
            id {PK},
            kode VARCHAR(20) NOT NULL UNIQUE,
            nama VARCHAR(100) NOT NULL,
            kategori VARCHAR(50) NOT NULL,
            satuan VARCHAR(20) NOT NULL,
            stok DECIMAL(14,3) NOT NULL DEFAULT 0,
            stok_min DECIMAL(14,3) NOT NULL DEFAULT 0,
            harga DECIMAL(14,2) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL
        ){ENGINE}",
        "CREATE TABLE produk (
            id {PK},
            nama VARCHAR(100) NOT NULL,
            hasil_per_batch DECIMAL(14,3) NOT NULL DEFAULT 1,
            satuan_hasil VARCHAR(20) NOT NULL DEFAULT 'kg',
            keterangan VARCHAR(255) NULL
        ){ENGINE}",
        "CREATE TABLE resep (
            id {PK},
            produk_id INT NOT NULL,
            bahan_id INT NOT NULL,
            jumlah DECIMAL(14,3) NOT NULL,
            FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE,
            FOREIGN KEY (bahan_id) REFERENCES bahan(id)
        ){ENGINE}",
        "CREATE TABLE produksi (
            id {PK},
            tanggal DATE NOT NULL,
            produk_id INT NOT NULL,
            batch DECIMAL(10,2) NOT NULL,
            hasil DECIMAL(14,3) NOT NULL,
            keterangan VARCHAR(255) NULL,
            user_id INT NULL,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (produk_id) REFERENCES produk(id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ){ENGINE}",
        "CREATE TABLE mutasi (
            id {PK},
            tanggal DATE NOT NULL,
            bahan_id INT NOT NULL,
            jenis VARCHAR(20) NOT NULL,
            jumlah DECIMAL(14,3) NOT NULL,
            stok_akhir DECIMAL(14,3) NOT NULL,
            harga DECIMAL(14,2) NULL,
            supplier_id INT NULL,
            produksi_id INT NULL,
            keterangan VARCHAR(255) NULL,
            user_id INT NULL,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (bahan_id) REFERENCES bahan(id),
            FOREIGN KEY (supplier_id) REFERENCES supplier(id) ON DELETE SET NULL,
            FOREIGN KEY (produksi_id) REFERENCES produksi(id) ON DELETE SET NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ){ENGINE}",
        "CREATE INDEX idx_mutasi_tanggal ON mutasi (tanggal)",
        "CREATE INDEX idx_mutasi_bahan ON mutasi (bahan_id, tanggal)",
    ];

    $ganti = $driver === 'sqlite'
        ? ['{PK}' => 'INTEGER PRIMARY KEY AUTOINCREMENT', '{ENGINE}' => '']
        : ['{PK}' => 'INT AUTO_INCREMENT PRIMARY KEY', '{ENGINE}' => ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'];

    return array_map(fn ($s) => strtr($s, $ganti), $tabel);
}

/** Data contoh untuk usaha mie & kulit pangsit. */
function data_awal(): array
{
    return [
        'supplier' => [
            ['Toko Tepung Sumber Rejeki', '0812-3456-7890', 'Pasar Induk Blok A No. 12', 'Tepung & tapioka'],
            ['Peternakan Telur Makmur', '0813-2222-1111', 'Jl. Raya Peternakan Km 5', 'Telur ayam negeri'],
            ['UD Kemasan Jaya', '0821-9999-8888', 'Jl. Industri No. 7', 'Plastik & label'],
        ],
        // kode, nama, kategori, satuan, stok, stok_min, harga
        'bahan' => [
            ['BB-001', 'Tepung terigu protein tinggi', 'Tepung', 'kg', 150, 50, 12500],
            ['BB-002', 'Tepung terigu protein sedang', 'Tepung', 'kg', 60, 25, 11000],
            ['BB-003', 'Tepung tapioka (kanji)', 'Tepung', 'kg', 40, 15, 9500],
            ['BB-004', 'Telur ayam', 'Telur', 'butir', 300, 120, 2000],
            ['BB-005', 'Garam halus', 'Bahan Pengembang & Garam', 'kg', 10, 3, 6000],
            ['BB-006', 'Air abu / kansui', 'Bahan Pengembang & Garam', 'liter', 4, 2, 25000],
            ['BB-007', 'Minyak sayur', 'Minyak', 'liter', 18, 10, 17000],
            ['BB-008', 'Pewarna kuning makanan', 'Pewarna & Perasa', 'ml', 300, 100, 150],
            ['BB-009', 'Plastik kemasan 1 kg', 'Kemasan', 'pcs', 400, 200, 350],
            ['BB-010', 'Plastik kemasan 250 gr', 'Kemasan', 'pcs', 150, 200, 200],
        ],
        // nama, hasil_per_batch, satuan_hasil, keterangan, resep [kode => jumlah per batch]
        'produk' => [
            ['Mie Telur Basah', 12, 'kg', 'Satu batch = 10 kg tepung', [
                'BB-001' => 10, 'BB-004' => 20, 'BB-005' => 0.15, 'BB-006' => 0.1,
                'BB-008' => 10, 'BB-007' => 0.3, 'BB-009' => 12,
            ]],
            ['Kulit Pangsit', 6, 'kg', 'Satu batch = 5 kg tepung, ±1.200 lembar', [
                'BB-002' => 4, 'BB-003' => 1, 'BB-004' => 8, 'BB-005' => 0.05,
                'BB-007' => 0.1, 'BB-010' => 24,
            ]],
        ],
    ];
}
