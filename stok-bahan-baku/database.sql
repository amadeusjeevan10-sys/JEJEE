-- Skema MySQL untuk Gudang Mie & Pangsit.
-- Cara termudah: buat database stok_bahan_baku lalu buka install.php (otomatis membuat tabel + akun admin).
-- File ini untuk impor manual via phpMyAdmin. Login awal: admin / admin123 (segera ganti).

SET NAMES utf8mb4;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    peran VARCHAR(20) NOT NULL DEFAULT 'staf',
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE supplier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    telepon VARCHAR(30) NULL,
    alamat VARCHAR(255) NULL,
    keterangan VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE bahan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    stok DECIMAL(14,3) NOT NULL DEFAULT 0,
    stok_min DECIMAL(14,3) NOT NULL DEFAULT 0,
    harga DECIMAL(14,2) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    hasil_per_batch DECIMAL(14,3) NOT NULL DEFAULT 1,
    satuan_hasil VARCHAR(20) NOT NULL DEFAULT 'kg',
    keterangan VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE resep (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produk_id INT NOT NULL,
    bahan_id INT NOT NULL,
    jumlah DECIMAL(14,3) NOT NULL,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE,
    FOREIGN KEY (bahan_id) REFERENCES bahan(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE produksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    produk_id INT NOT NULL,
    batch DECIMAL(10,2) NOT NULL,
    hasil DECIMAL(14,3) NOT NULL,
    keterangan VARCHAR(255) NULL,
    user_id INT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (produk_id) REFERENCES produk(id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE mutasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_mutasi_tanggal ON mutasi (tanggal);

CREATE INDEX idx_mutasi_bahan ON mutasi (bahan_id, tanggal);

INSERT INTO users (username, nama, peran, password, created_at) VALUES
('admin', 'Administrator', 'admin', '$2y$10$0W9qHiPuQfEUOVbIaw0/H.vhOmcyTbzIQBAqHWTWUkXS.tnutdDV.', NOW());
