# Panduan Desain UI/UX & Design System
## Sistem Servis AC Masjid (Versi Bersih & Ramah Pengguna)

Dokumen ini memuat standar identitas visual, tata letak, palet warna, dan aturan interaksi untuk proyek baru. Panduan ini mengambil esensi ketenangan dan nuansa sejuk dari sistem sebelumnya, namun menyingkirkan elemen visual berlebihan (menghindari tren *glassmorphism* buram, efek animasi berat, dan penumpukan ikon dekoratif yang tidak fungsional).

---

## 1. Filosofi Desain

Prinsip dasar antarmuka ini adalah **Utilitarian, Sejuk, dan Mudah Dibaca**:

1. **Bebas Elemen Dekoratif Tak Berguna**: Tidak menggunakan rentetan ikon atau emoji dekoratif di judul, tombol, maupun tabel. Ikon hanya digunakan jika memiliki fungsi navigasi yang jelas (misalnya: ikon telepon untuk menelpon, kaca pembesar untuk mencari).
2. **Kontras Tinggi (Outdoor & Senior Friendly)**: Menghilangkan efek kartu transparan (*liquid glass/blur*). Teks harus berwarna gelap pekat di atas latar belakang solid terang agar tetap terbaca jelas di ponsel jadul, di bawah terik matahari oleh teknisi, maupun oleh mata pengurus masjid usia 50 tahun ke atas.
3. **Sentuhan Nyaman (Large Tap Target)**: Setiap tombol aksi utama memiliki tinggi minimal 48 piksel dengan jarak antar elemen yang cukup lega untuk mencegah salah sentuh.

---

## 2. Palet Warna (Color Palette)

Palet warna mempertahankan identitas "Masjid yang Sejuk & Bersih", menggunakan kombinasi warna hijau daun teduh, netral hangat, dan aksen fungsional.

### 2.1. Warna Utama (Brand & Surface)

| Nama Token | Kode HEX | Peruntukan |
|---|---|---|
| **Primary (Hijau Khidmat)** | `#1E3E34` | Warna tombol utama, bilah navigasi atas, dan judul penting. Memberi kesan resmi, terpercaya, dan tenang. |
| **Primary Hover** | `#142B24` | Kondisi tombol saat ditekan (*active/hover*). |
| **Primary Subtle** | `#EBF2EE` | Latar belakang kartu sorotan atau penanda aktif. |
| **Background (Latar Utama)** | `#F8F9FA` | Latar belakang seluruh halaman aplikasi. Bersih, tidak silau, dan netral. |
| **Surface (Kartu & Panel)** | `#FFFFFF` | Latar belakang kartu formulir, tabel data, dan popup modal. Menggunakan warna putih solid tanpa transparansi. |
| **Border (Garis Tepi)** | `#D1D5DB` | Garis pembatas kartu dan input form dengan ketebalan 1px solid yang tegas. |

### 2.2. Warna Teks (Typography Colors)

| Nama Token | Kode HEX | Peruntukan |
|---|---|---|
| **Text Main** | `#111827` | Teks utama, judul, angka nominal, dan isi label form. Kontras maksimal terhadap latar putih. |
| **Text Muted** | `#4B5563` | Teks bantuan (*helper text*), tanggal, dan deskripsi sekunder. Tetap memenuhi standar kontras WCAG AA. |
| **Text On Primary** | `#FFFFFF` | Teks putih di atas tombol hijau pekat. |

### 2.3. Warna Status Fungsional (Functional Status)

Status pesanan menggunakan warna penanda yang tegas tanpa efek neon:

| Status Pesanan | Warna Latar | Warna Teks | Warna Garis |
|---|---|---|---|
| **Pesanan Baru** | `#FEF3C7` (Kuning Pucat) | `#92400E` (Cokelat Emas) | `#FCD34D` |
| **Dijadwalkan / Dikerjakan** | `#E0F2FE` (Biru Lembut) | `#0369A1` (Biru Pekat) | `#BAE6FD` |
| **Menunggu Pembayaran** | `#FFEDD5` (Oranye Muda) | `#C2410C` (Oranye Terbakar) | `#FDBA74` |
| **Selesai & Lunas** | `#DCFCE7` (Hijau Muda) | `#15803D` (Hijau Daun) | `#86EFAC` |
| **Dibatalkan** | `#FEE2E2` (Merah Pucat) | `#B91C1C` (Merah Gelap) | `#FCA5A5` |

---

## 3. Tipografi (Typography)

Menggunakan jenis huruf sans-serif sistem yang tajam, proporsional, dan tersedia secara bawaan di peramban modern tanpa membebani kuota data.

- **Keluarga Huruf Utama**: `Inter`, `-apple-system`, `BlinkMacSystemFont`, `Segoe UI`, `sans-serif`.
- **Ketebalan (*Font Weight*)**:
  - `Regular (400)` untuk teks bacaan dan paragraf.
  - `Medium (500)` untuk label input dan penjelas.
  - `Semi-bold (600)` untuk tombol, sub-judul, dan penanda status.
  - `Bold (700)` untuk judul halaman dan nominal harga.

### Skala Ukuran Teks:

- **Judul Utama Halaman**: 22px – 24px (Mobile: 20px) | `font-bold`
- **Judul Bagian / Kartu**: 18px (Mobile: 17px) | `font-semibold`
- **Label Form & Tombol**: 16px | `font-medium`
- **Teks Tubuh / Keterangan**: 15px – 16px | `font-normal`
- **Teks Kecil (Catatan/Tanggal)**: 13px – 14px | `font-normal`

> **Aturan**: Tidak ada teks di antarmuka ponsel yang berukuran lebih kecil dari 13px.

---

## 4. Komponen Antarmuka (UI Components)

### 4.1. Tombol (Buttons)

Tombol dirancang dengan penekanan area sentuh yang luas (*touch-friendly*):

```text
Tombol Utama (Primary):
[ Simpan & Tugaskan Teknisi ]
- Latar: #1E3E34 (Hijau Khidmat)
- Teks: #FFFFFF (Putih Tebal, 16px)
- Tinggi: 48px
- Radius: 8px (sudut melengkung wajar, bukan kapsul berlebih)
- Efek: Tanpa animasi memantul/glow. Cukup perubahan warna sedikit lebih gelap saat disentuh.

Tombol Sekunder (Secondary / Outline):
[ Kembali ]
- Latar: #FFFFFF
- Garis: 1.5px solid #D1D5DB
- Teks: #374151
```

### 4.2. Formulir Input (Form Controls)

Input form mengutamakan keterbacaan tinggi dan penanda aktif yang jelas:

- **Tinggi Input**: 46px – 48px agar nyaman diketik di layar sentuh.
- **Warna Bingkai Normal**: `1.5px solid #D1D5DB`.
- **Warna Bingkai Fokus (Saat Diketik)**: `2px solid #1E3E34` dengan ring halus transparan (`#1E3E34` opacity 10%).
- **Latar Belakang**: Putih murni (`#FFFFFF`).
- **Placeholder**: Teks contoh berwarna abu-abu terang (`#9CA3AF`), menggunakan format kalimat riil (misal: `"Contoh: Masjid Al-Ikhlas"`).

### 4.3. Kartu Informasi (Cards)

Menggantikan konsep *glassmorphism* transparan dengan kartu solid klasik:

- **Latar Belakang**: Putih solid (`#FFFFFF`).
- **Garis Pembatas**: `1px solid #E5E7EB`.
- **Bayangan (Shadow)**: `box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06)` (sangat halus, hanya untuk memisahkan kartu dari latar abu-abu).
- **Padding Dalam**: 20px di desktop, 16px di ponsel.
- **Radius Sudut**: 12px.

### 4.4. Label Status (Badges)

Label status dibuat bersih berupa kotak kecil bersudut tumpul tanpa ikon dekoratif di dalamnya:

```text
Format Badge:
[ Baru ]           -> Latar Kuning Pucat, Teks Cokelat Tua
[ Dijadwalkan ]    -> Latar Biru Lembut, Teks Biru Pekat
[ Menunggu Bayar ] -> Latar Oranye Muda, Teks Oranye Gelap
[ Selesai ]        -> Latar Hijau Muda, Teks Hijau Tua
```

---

## 5. Tata Letak Berdasarkan Peran (Role-Based Layouts)

### 5.1. Formulir Tamu / DKM (Tampilan Ponsel Publik)
- **Struktur**: Satu kolom lurus terpusat (*Single Column Form*), lebar maksimal 520px.
- **Pencarian Masjid**:
  - Kolom teks pencarian sederhana dengan kotak daftar rekomendasi di bawahnya jika nama masjid sudah pernah ada di database.
  - Jika belum ada, tombol teks jelas: `+ Daftarkan nama masjid baru ini`.
- **Pilihan Jenis Kerusakan**:
  - Berbentuk kotak pilihan besar (*Card Radio Selector*) yang mudah ditekan jempol, bukan dropdown kecil yang sulit dibuka di ponsel.

### 5.2. Layar Teknisi (Mobile First Operasional)
- **Tampilan Beranda**:
  - Menampilkan 1 tugas utama yang sedang aktif di bagian paling atas.
  - Blok info masjid: Nama Masjid (tebal), Alamat, Nama Kontak DKM.
  - Dua tombol utilitas berdampingan:
    - `[ Buka Google Maps ]` (membuka aplikasi peta).
    - `[ Chat WhatsApp ]` (langsung membuka WhatsApp ke nomor DKM).
- **Layar Upload Foto Bukti**:
  - Tombol ambil foto berbentuk kotak dengan garis putus-putus dan tombol jelas: `Ambil Foto Kamera`.
  - Pratinjau foto langsung muncul dalam bentuk kotak mini dengan tombol hapus/ganti yang jelas jika gambar buram.

### 5.3. Meja Kerja Admin (Dashboard Desktop & Tablet)
- **Header Bersih**: Menampilkan nama sistem di kiri, tanggal hari ini, dan nama admin yang bertugas di kanan.
- **Bilah Tab Status**: Tab navigasi horizontal sederhana untuk memfilter data:
  - `Semua Pesanan`
  - `Pesanan Baru (3)`
  - `Sedang Berjalan (2)`
  - `Menunggu Pembayaran (1)`
  - `Selesai`
- **Tabel Data Rapi**:
  - Baris tabel berjarak longgar (*comfortable row padding* 14px).
  - Garis pemisah abu-abu tipis antar baris.
  - Kolom aksi langsung berupa tombol teks: `Tugaskan`, `Detail`, atau `Verifikasi`.

---

## 6. Contoh Implementasi Kelas Tailwind CSS

Konfigurasi kelas Tailwind yang direkomendasikan untuk proyek baru:

```html
<!-- Contoh Tombol Utama Bersih -->
<button class="w-full sm:w-auto px-6 py-3 bg-[#1E3E34] hover:bg-[#142B24] text-white font-medium text-base rounded-lg transition-colors focus:ring-2 focus:ring-[#1E3E34] focus:ring-offset-2">
  Simpan dan Tugaskan Teknisi
</button>

<!-- Contoh Input Teks Ramah Jempol -->
<div class="space-y-1.5">
  <label class="block text-sm font-medium text-gray-900">
    Nama Masjid atau Musholla
  </label>
  <input 
    type="text" 
    class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 text-base placeholder-gray-400 focus:outline-none focus:border-[#1E3E34] focus:ring-1 focus:ring-[#1E3E34]"
    placeholder="Contoh: Masjid Jamie Al-Barkah"
  >
</div>

<!-- Contoh Kartu Solid Tanpa Glassmorphism -->
<div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-4">
  <div class="flex items-center justify-between">
    <h3 class="text-lg font-semibold text-gray-900">Masjid Jamie Al-Barkah</h3>
    <span class="px-3 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">
      Pesanan Baru
    </span>
  </div>
  <p class="text-sm text-gray-600">Jalan Mawar No. 4, Kebayoran Baru</p>
</div>
```

---

## 7. Ringkasan Pantangan Desain (Design Don'ts)

1. **Dilarang menggunakan ikon dekoratif/emoji acak** di tengah kalimat atau judul tabel.
2. **Dilarang menggunakan teks abu-abu terang di atas latar putih** yang sulit dibaca mata orang tua.
3. **Dilarang menggunakan efek latar buram/transparan (*glassmorphism*)** yang membuat teks kabur dan rendering lambat di ponsel Android standar.
4. **Dilarang menggunakan dropdown berlapis** untuk aksi yang sebenarnya bisa diselesaikan dalam 1 klik tombol.
5. **Dilarang menggunakan istilah asing/singkatan sistem** yang tidak dimengerti marbot masjid.
