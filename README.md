# 🕌 Sistem Manajemen Servis AC Masjid & Musholla (Versi V2)

Sistem informasi operasional perawatan dan perbaikan AC masjid yang disederhanakan (*anti-overengineered*), berfokus pada kemudahan penggunaan bagi pengurus DKM/marbot (usia 40+), kemudahan teknisi lapangan (gaya aplikasi ojek online), dan meja pengelola satu pintu bagi admin.

Repository GitHub: [https://github.com/Khoirul981/servis_ac](https://github.com/Khoirul981/servis_ac)

---

## 🌟 Fitur Utama Berdasarkan Peran

### 1. Pengurus DKM / Marbot (Pemesan - Tanpa Login)
- **Akses Publik**: Buka langsung di browser HP tanpa perlu daftar atau login.
- **Pencarian Masjid Pintar**: Dilengkapi pencarian otomatis (*autocomplete*) untuk masjid lama yang pernah servis atau form instan untuk masjid baru.
- **Pilihan Keluhan Sentuh**: Tombol kartu sentuh besar (*Cuci Rutin*, *Kurang Dingin/Bocor*, *Mati Total*).
- **Counter Unit AC**: Tombol sentuh `+` dan `−` besar ramah jempol.
- **Estimasi Biaya Real-Time**: Perkiraan total biaya langsung tampil saat memilih keluhan dan jumlah AC.
- **Perlindungan Anti-Spam**: *Honeypot field* & *Rate limiting* (maksimal 3 pesanan/jam per nomor WA).
- **Integrasi WhatsApp**: Tombol satu klik untuk konfirmasi rincian pesanan ke WhatsApp Admin.

### 2. Teknisi Lapangan (Mobile-Friendly Dashboard)
- **Tampilan Khusus Smartphone**: Optimal di layar HP (360px – 420px).
- **Navigasi 1-Sentuh**: Tombol **Google Maps** langsung membuka rute GPS ke masjid.
- **Komunikasi Cepat**: Tombol **WhatsApp DKM** langsung membuka chat dengan kontak pengurus.
- **Alur Kerja 4 Langkah di Lokasi**:
  1. `[ 🟢 Saya Sudah Tiba & Mulai Bekerja ]` (Status $\rightarrow$ `dikerjakan`)
  2. `[ 📸 Kamera Foto Bukti ]` (Kompresi foto otomatis di browser HP $\le$ 500 KB agar hemat kuota seluler)
  3. `[ ➕ Tambah Biaya Sparepart/Freon ]` (Input komponen & biaya jika ada kerusakan tak terduga)
  4. `[ ✅ Pekerjaan Selesai ]` (Kunci pengaman: wajib ada minimal 1 foto bukti $\rightarrow$ Status $\rightarrow$ `menunggu_bayar`)

### 3. Meja Pengelola Admin (Pusat Kendali Operasional)
- **Papan Operasional 4 Tab Status**:
  - `📥 Pesanan Masuk`: Order baru dari web yang butuh penugasan teknisi & jam kedatangan.
  - `🚀 Sedang Berjalan`: Memantau teknisi mana yang sedang bertugas di lokasi masjid.
  - `💰 Menunggu Bayar`: Servis fisik beres, siap diverifikasi pembayarannya.
  - `✅ Riwayat Beres`: Arsip seluruh servis yang telah lunas.
- **Verifikasi Pelunasan**: Satu klik verifikasi metode bayar (Transfer Bank, QRIS, Tunai titip teknisi).
- **Kuitansi Resmi & Kirim WA**: Halaman kuitansi resmi siap cetak + tombol kirim teks kuitansi otomatis ke nomor WhatsApp DKM.
- **Master Data**: Kelola data masjid, penambahan unit AC per ruangan, dan akun petugas.

---

## 👥 Akun Uji Coba Default

| Peran | Email / Nomor Login | Kata Sandi | Akses Halaman |
|---|---|---|---|
| **Admin Pengelola** | `admin@servisac.com` *(atau `081234567890`)* | `password123` | `/admin` |
| **Teknisi Lapangan 1** | `budi@servisac.com` *(atau `081298765432`)* | `password123` | `/teknisi` |
| **Teknisi Lapangan 2** | `agus@servisac.com` *(atau `081311223344`)* | `password123` | `/teknisi` |
| **DKM / Marbot** | *Tanpa Login* | - | `/` atau `/pesan` |

---

## 🚀 Panduan Menjalankan di Komputer Lokal (Laragon)

### 1. Prasyarat
- **PHP $\ge$ 8.3**
- **Composer**
- **Node.js & NPM**
- **MySQL 8.0** (Laragon sudah otomatis menyediakannya)

### 2. Langkah Menjalankan:
1. Buka aplikasi **Laragon**, lalu klik tombol **Start All** (pastikan service MySQL dan Apache menyala).
2. Buka terminal di folder proyek (`C:\Folder-AplikasiAC\servis_ac`).
3. Jalankan server Laravel:
   ```bash
   php artisan serve
   ```
4. Buka browser Anda:
   - **Form Pemesanan DKM (Halaman Depan)**: `http://localhost:8000/`
   - **Halaman Login Petugas**: `http://localhost:8000/login`

---

## 🧪 Menjalankan Pengujian Otomatis

Seluruh logika alur kerja dilindungi oleh 30 pengujian otomatis (*Feature Tests*):

```bash
php artisan test
```

Semua pengujian mencakup:
- Pengujian autentikasi & pemisahan peran admin/teknisi.
- Pengujian form publik pemesanan, honeypot, dan rate limiter.
- Pengujian lembar kerja mobile teknisi dan kamera foto.
- Pengujian meja admin, penugasan jadwal, dan verifikasi pelunasan kuitansi.
- Pengujian simulasi siklus penuh (*End-to-End Workflow*).
