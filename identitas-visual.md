# Identitas Visual — @beligaya

Aset dan prompt desain untuk akun TikTok affiliate `@beligaya`
("Beli Ga Ya"). Dokumen pendamping [README.md](README.md).

---

## Palet Warna

| Peran | Warna | Hex |
|---|---|---|
| Vonis **BELI** | Hijau terang | `#22C55E` |
| Vonis **SKIP** | Merah terang | `#EF4444` |
| Aksen / keranjang TikTok Shop | Kuning cerah | `#FFD400` |
| Teks utama | Putih | `#FFFFFF` |
| Outline / kontras | Hitam pekat | `#000000` |

Warna hijau dan merah dipakai konsisten di **tiga tempat**: foto profil,
stempel vonis di video, dan teks overlay. Ini yang membuat akun dikenali
sebelum namanya terbaca.

## Tipografi

**Font utama: Anton** (atau Archivo Black sebagai cadangan).

Alasan: bentuknya tinggi dan padat, jadi muat lebih banyak huruf dalam ruang
sempit tanpa mengecil. Aura "tegas memutuskan"-nya cocok untuk akun yang
tugasnya memberi vonis.

Alternatif sesuai selera:

| Font | Karakter |
|---|---|
| Bebas Neue | Ramping tinggi, bersih, agak elegan |
| Luckiest Guy | Gemuk, kartun, ceria, sangat "TikTok" |
| Poppins Black | Bulat geometris, modern, aman |
| Bungee | Kotak tebal gaya papan reklame, kesan stiker |

---

## Anatomi Prompt Logo

Prompt logo yang menghasilkan desain layak pakai punya 6 bagian. Kebanyakan
orang hanya menulis bagian 1, makanya hasilnya generik.

| # | Bagian | Fungsi |
|---|---|---|
| 1 | Objek utama | Apa bendanya |
| 2 | Makna/simbolisme | Kenapa benda itu — AI jadi paham maksudnya |
| 3 | Gaya visual | Flat, 3D, garis, maskot |
| 4 | Warna spesifik | Sebut hex-nya, jangan "warna cerah" |
| 5 | Komposisi | Terpusat, ada padding, siap crop lingkaran |
| 6 | **Batasan teknis** | **Paling sering dilupakan** — harus terbaca di 40px |

Bagian 6 adalah penentu antara logo yang bekerja di TikTok dan yang hanya
bagus dilihat di Canva.

### Aturan teks pada foto profil

Di FYP, foto profil tampil sekitar **40×40 piksel**. Teks mendatar 8 huruf
("BELIGAYA") menyisakan ~5px per huruf — tidak terbaca.

Solusinya **ditumpuk**, dengan aturan:
**maksimal 3 baris, maksimal 4 huruf per baris.**

```
Mendatar:  B E L I G A Y A   ← 8 huruf berebut ruang, semua kecil
Ditumpuk:   BELI              ← tiap baris 2-4 huruf,
            GA YA?               jadi tiap huruf bisa BESAR
```

---

## Prompt Terpilih — Vonis Terbelah + Wordmark

Konsep ini dipilih karena bekerja sebagai **sistem**, bukan sekadar logo:
warna hijau-merahnya sama persis dengan stempel vonis di tiap video, jadi
foto profil dan konten saling memperkuat. Sekaligus nama akun tetap terbaca —
penting untuk akun baru yang namanya belum dihafal orang.

Tiga keputusan teknis di dalamnya:
1. **Latar penuh edge-to-edge**, bukan lingkaran di atas latar — supaya crop
   lingkaran TikTok justru menghasilkan belahan yang sempurna
2. **Teks dikunci di zona aman tengah** (~65% lebar) agar tidak terpotong
3. **Tanda tanya diberi warna kuning** sebagai satu-satunya titik fokus

### Versi Indonesia (untuk Canva)

```
Logo foto profil untuk akun TikTok bernama "Beli Ga Ya".

KOMPOSISI:
Bidang persegi terisi penuh dari tepi ke tepi, dibelah satu garis diagonal
lurus dari sudut kiri bawah ke sudut kanan atas, menjadi dua bidang sama besar.
Bidang kiri atas berwarna hijau terang solid (#22C55E).
Bidang kanan bawah berwarna merah terang solid (#EF4444).
Tidak ada bingkai, tidak ada lingkaran, tidak ada latar tambahan — warna
mengisi seluruh bidang sampai ke tepi.

TEKS:
Tepat di tengah, menindih garis pembelah, terdapat tulisan "BELI GA YA?"
disusun bertumpuk menjadi dua baris rata tengah:
  baris pertama: BELI
  baris kedua: GA YA?
Huruf kapital semua, font sans-serif ultra tebal dan memanjang ke atas
(condensed extra bold, seperti Anton atau Archivo Black).
Jarak antar baris sangat rapat sehingga kedua baris terasa menyatu sebagai
satu blok padat.
Warna huruf putih bersih dengan outline hitam pekat yang sangat tebal, agar
tetap terbaca di atas bidang hijau maupun merah.
Khusus tanda tanya "?" berwarna kuning cerah (#FFD400) dengan outline hitam
tebal yang sama, sebagai satu-satunya titik fokus warna.
Blok teks menempati sekitar 65 persen lebar bidang dan berada di zona aman
tengah, dengan ruang kosong cukup di semua sisi agar tidak terpotong saat
gambar dipotong menjadi bentuk lingkaran.

GAYA:
Flat design, geometris, tegas, seperti stempel vonis atau ikon aplikasi mobile.
Kontras maksimal. Hanya lima warna: hijau, merah, putih, hitam, kuning.

BATASAN TEKNIS WAJIB:
Desain harus tetap terbaca jelas saat ditampilkan pada ukuran 40x40 piksel.
Tanpa gradasi, tanpa bayangan, tanpa tekstur, tanpa efek 3D, tanpa kilau,
tanpa garis tipis, tanpa outline tipis, tanpa detail halus, tanpa elemen
dekoratif tambahan, tanpa teks lain selain "BELI GA YA?".
```

### Versi Inggris (untuk Midjourney / DALL-E / Ideogram)

```
Flat vector profile picture logo. Square canvas filled edge to edge, split by
a single straight diagonal line from bottom-left corner to top-right corner
into two equal halves. Upper-left half solid bright green (#22C55E), lower-right
half solid bright red (#EF4444). No frame, no circle, no border.

Centered on top of the dividing line: the text "BELI GA YA?" stacked in two
centered lines — first line "BELI", second line "GA YA?" — all caps, ultra bold
condensed sans-serif typeface (Anton or Archivo Black style), very tight line
spacing forming one solid block. Letters pure white with very thick solid black
outline. The question mark "?" is bright yellow (#FFD400) with the same thick
black outline, as the single focal accent. Text block occupies about 65 percent
of the width, centered with generous safe margin so nothing is clipped when
cropped to a circle.

Style: flat design, geometric, bold, stamp-like, mobile app icon. Maximum
contrast. Only five colors: green, red, white, black, yellow.

Must remain legible at 40x40 pixels. No gradients, no shadows, no textures,
no 3D, no gloss, no thin lines, no fine details, no extra text or decoration.
```

### Kalimat perbaikan kalau hasilnya belum pas

Tambahkan ke akhir prompt sesuai masalahnya:

| Masalah | Kalimat tambahan |
|---|---|
| Teks terlalu kecil | `Perbesar blok teks hingga memenuhi 80 persen lebar bidang.` |
| Belahan tidak rapi | `Garis pembelah harus lurus sempurna, tajam, tanpa gradasi di perbatasan.` |
| Terlalu ramai | `Hilangkan aksen kuning, buat tanda tanya berwarna putih seperti huruf lain.` |
| Huruf terlalu kurus | `Gunakan huruf yang jauh lebih tebal dan padat, hampir saling bersentuhan.` |

---

## Konsep Cadangan

Kalau konsep terpilih tidak memuaskan, ini alternatif yang sudah dipikirkan.

### A — Keranjang Bertanya
Keranjang belanja hitam tebal, di dalamnya tanda tanya besar menggantung.
Latar kuning TikTok Shop.
**Plus:** terjemahan visual paling harfiah dari nama akun.
**Minus:** konsep keranjang sudah banyak dipakai akun jualan.

### B — Vonis Terbelah (ikon murni)
Lingkaran terbelah diagonal hijau-merah, tanda tanya putih tebal di tengah,
tanpa teks.
**Plus:** paling terbaca di ukuran kecil.
**Minus:** orang tidak belajar nama akunmu.

### C — Tanda Tanya Murni
Tanda tanya hitam raksasa di latar kuning, titik bawahnya diganti keranjang mungil.
**Plus:** paling tidak mungkin gagal di ukuran kecil.
**Minus:** bisa terbaca sebagai akun kuis atau misteri.

### D — Maskot Keranjang
Keranjang belanja bermata besar dengan alis terangkat sebelah (ekspresi ragu).
**Plus:** pengganti wajah untuk akun faceless, bisa berkembang jadi stiker
dan karakter di thumbnail.
**Minus:** mata dan wajah itu detail halus — paling berisiko hancur di 40px.

### F — Balon Chat
Balon percakapan kuning berisi teks tumpuk, latar hitam.
**Plus:** siluet balon tetap dikenali walau teksnya kabur di ukuran kecil.
Maknanya pas — nama akun ini memang isi kepala orang.

---

## Ikon vs Wordmark

| | Ikon murni | Wordmark |
|---|---|---|
| Terbaca di 40px | Sangat baik | Cukup, kalau ditumpuk |
| Orang ingat namamu | Lemah | **Kuat** |
| Nyambung dengan stempel video | Kuat | Kuat |

Untuk akun **baru**, wordmark lebih menguntungkan — orang perlu menghafal nama
dulu sebelum bisa mencarimu. Ikon murni baru lebih berguna setelah akun dikenal.

---

## Aset Yang Perlu Dibuat

- [ ] Foto profil (dari prompt terpilih)
- [ ] Stempel vonis `BELI` — kotak hijau `#22C55E`, huruf putih outline hitam
- [ ] Stempel vonis `SKIP` — kotak merah `#EF4444`, huruf putih outline hitam
- [ ] Template teks overlay "BELI GA YA?" untuk hook detik 0-2
- [ ] Template penomoran seri ("#1", "#2", ...) untuk pojok video
