# HPP Kedai Kopi Mingguan (CFD)

Perhitungan Harga Pokok Penjualan (HPP) untuk kedai kopi yang buka
seminggu sekali di Car Free Day (CFD). Modal utama: **moka pot**, stok
espresso diseduh di rumah dari **biji kopi 1 kg seharga Rp250.000**.

**Cara pakai dokumen ini:** semua angka bertanda 🔧 adalah **asumsi**
yang perlu diganti dengan harga/takaran asli kamu. Rumusnya tetap
berlaku walau angkanya diubah.

---

## Ringkasan Hasil (dengan asumsi default di bawah)

| Komponen | Biaya/cup |
|---|---|
| Bahan baku variabel | Rp9.100 |
| Biaya operasional (dibagi 40 cup/minggu) | Rp3.375 |
| Depresiasi alat (dibagi 40 cup/minggu) | Rp175 |
| **Total HPP** | **≈ Rp12.650 / cup** |
| Rekomendasi harga jual (margin ~50%) | **Rp20.000 – 25.000** |
| BEP (titik impas) | **≈ 13 cup/minggu** |

Detail perhitungan di bawah.

---

## Langkah 1 — HPP Kopi (bahan utama)

Ini bagian paling pasti karena kamu sudah kasih angkanya: **1 kg biji
kopi = Rp250.000**.

Yang menentukan HPP per cup adalah **dosis kopi per cup**, bukan cara
seduhnya (langsung per cup atau bikin stok pekat dulu lalu diencerkan).
Total biji kopi yang habis untuk 1 kg tetap sama, jadi hitung dari
massa, bukan volume stok.

| Dosis/cup 🔧 | Cup per kg | HPP kopi/cup |
|---|---|---|
| 15 g (ringan) | ~66 cup | Rp3.788 |
| **18 g (standar espresso/moka)** | **~55 cup** | **Rp4.545** |
| 20 g (lebih pekat) | 50 cup | Rp5.000 |

**Rumus:** `HPP kopi/cup = 250.000 ÷ (1000 ÷ dosis gram per cup)`

Dipakai **18 g/cup** sebagai default → **Rp4.500/cup** (dibulatkan).

> Kalau kamu bikin stok konsentrat di rumah (misal seduh 100 g kopi
> jadi ~1 liter stok, lalu tiap cup pakai 60–80 ml stok), hasil akhirnya
> tetap harus dicek balik ke "berapa gram kopi total per cup" —
> gunakan tabel di atas untuk kalibrasi takaran stoknya.

---

## Langkah 2 — Bahan Pendukung per Cup

Asumsi produk: **Es Kopi Susu ukuran ±250 ml**. Ganti sesuai menu
aslimu (kalau jual varian gula aren, americano, dll, buat baris
terpisah per varian).

| Item | Takaran 🔧 | Harga satuan 🔧 | Biaya/cup |
|---|---|---|---|
| Biji kopi | 18 g | Rp250.000/kg | Rp4.500 |
| Susu cair UHT | 100 ml | Rp18.000/liter | Rp1.800 |
| Gula aren cair (simple syrup) | 30 ml | Rp35.000/kg gula → syrup | Rp900 |
| Es batu | secukupnya | — | Rp500 |
| Cup plastik 16oz + tutup + sedotan | 1 set | Rp1.200/set | Rp1.200 |
| Plastik segel / label / tissue | 1 pcs | — | Rp200 |
| **Subtotal bahan baku variabel** | | | **Rp9.100** |

---

## Langkah 3 — Biaya Operasional Mingguan (CFD)

Biaya yang keluar tiap kali buka lapak, di luar bahan baku per cup.

| Item | Biaya/minggu 🔧 |
|---|---|
| Sewa lapak/space CFD (kalau ada) | Rp100.000 |
| Gas kompor portable (masak stok + syrup) | Rp5.000 |
| Air galon untuk bilas alat | Rp5.000 |
| Transport (bensin/parkir) | Rp20.000 |
| Kantong kresek pembeli | Rp5.000 |
| **Total operasional/minggu** | **Rp135.000** |

**Kalau jualan tanpa sewa lapak** (keliling / gratis), total turun
jadi **Rp35.000/minggu**.

Dibagi target penjualan **40 cup/minggu 🔧**:

`135.000 ÷ 40 = Rp3.375/cup`

---

## Langkah 4 — Depresiasi Alat

Modal alat dihitung menyusut per minggu berdasarkan umur pakai, bukan
dibebankan sekali di minggu pertama.

| Alat | Harga beli 🔧 | Umur pakai 🔧 | Biaya/minggu | Biaya/cup (÷40) |
|---|---|---|---|---|
| Moka pot | Rp150.000 | 100 minggu (~2 th) | Rp1.500 | Rp37,5 |
| Kompor portable + tabung gas | Rp250.000 | 100 minggu | Rp2.500 | Rp62,5 |
| Cooler box / termos air panas | Rp150.000 | 150 minggu (~3 th) | Rp1.000 | Rp25 |
| Meja lipat + banner/spanduk | Rp200.000 | 100 minggu | Rp2.000 | Rp50 |
| **Total depresiasi** | | | **Rp7.000/minggu** | **Rp175/cup** |

---

## Total HPP per Cup

```
Bahan baku variabel     Rp9.100
+ Operasional/cup       Rp3.375
+ Depresiasi/cup           Rp175
-----------------------------
= HPP                  Rp12.650 / cup
```

## Rekomendasi Harga Jual

| Skenario markup | Harga jual | Margin |
|---|---|---|
| Markup 2x (umum F&B kecil) | Rp25.300 → **Rp25.000** | ~50% |
| Margin 50% dari harga jual | `12.650 ÷ (1 − 0,5)` = **Rp25.300** | 50% |
| Sesuai harga pasar CFD (Rp15rb–25rb) | **Rp20.000** | ~37% |

**Rekomendasi: Rp20.000–25.000/cup**, sesuaikan dengan harga kompetitor
di lokasi CFD kamu dan daya beli target pasar.

---

## Titik Impas (Break-Even Point) Mingguan

Biaya tetap per minggu (operasional + depresiasi, tidak berubah walau
cup terjual sedikit/banyak):

```
Fixed cost/minggu = Rp135.000 (operasional) + Rp7.000 (depresiasi)
                   = Rp142.000
```

Margin kontribusi per cup (harga jual − biaya variabel bahan baku):

```
Rp20.000 − Rp9.100 = Rp10.900/cup
```

```
BEP = 142.000 ÷ 10.900 ≈ 13 cup/minggu
```

Artinya: **jual di atas ±13 cup/minggu sudah balik modal operasional**;
sisanya (target 40 cup) adalah profit kotor.

**Estimasi profit per minggu di 40 cup:**
```
(Rp20.000 − Rp12.650) × 40 cup = Rp294.000/minggu
```

---

## Yang Perlu Kamu Konfirmasi/Ubah

- [ ] Menu aktual & ukuran cup (kalau lebih dari 1 varian, hitung HPP terpisah per menu)
- [ ] Dosis kopi per cup yang benar-benar dipakai (15/18/20 g)
- [ ] Harga susu, gula, es, cup di lokasi belanjamu
- [ ] Ada/tidaknya biaya sewa lapak CFD
- [ ] Target jumlah cup terjual per minggu (realistis berdasarkan jam buka & jam ramai CFD)
- [ ] Harga alat (moka pot, kompor, cooler) sesuai yang kamu beli
