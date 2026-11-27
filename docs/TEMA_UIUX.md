# Panduan Desain UI/UX & Design System
## Sistem Servis AC Masjid — "Hijau Khidmat Modern, Ramah Orang Tua"

Standar visual, tata letak, dan interaksi untuk seluruh halaman. Semua refaktor halaman berikutnya WAJIB mengikuti dokumen ini. Sumber token: `resources/css/app.css` (blok `@theme`, Tailwind CSS v4). Master layout: `resources/views/layouts/app.blade.php`.

---

## 1. Filosofi

1. **Mobile-first**: dirancang untuk HP dulu, desktop menyusul.
2. **Ramah orang tua**: teks besar, kontras tinggi, tombol besar, kalimat Indonesia sederhana.
3. **Elegan & berwibawa**: hijau tua dominan, putih-kehijauan lembut, aksen emas sangat minim (maks. 5%).
4. **Utilitarian**: tanpa glassmorphism, tanpa animasi berat, tanpa ikon dekoratif. Ikon hanya bila fungsional dan selalu disertai label teks.

---

## 2. Palet Warna (token `@theme`)

| Token (utility) | HEX | Peruntukan |
|---|---|---|
| `primary` | `#1B4332` | Deep Emerald: tombol utama, header aktif, judul penting |
| `primary-hover` | `#123023` | Hover / ditekan tombol utama |
| `primary-light` | `#2D6A4F` | Aksen sekunder, gradien halus, outline fokus |
| `primary-subtle` | `#EAF4EE` | Latar highlight, tab/menu aktif, kartu sorotan |
| `primary-border` | `#C4DFCE` | Garis tepi kartu/header bernuansa hijau |
| `accent-gold` | `#B08938` | Penanda khusus, maks. 5% area |
| `bg-main` | `#F4F7F5` | Latar halaman (tidak silau) |
| `surface` | `#FFFFFF` | Kartu, tabel, modal (solid, tanpa transparansi) |
| `text-main` | `#13221B` | Teks utama |
| `text-muted` | `#4A5D54` | Teks bantuan (tetap lolos kontras WCAG AA) |

Pemakaian di Blade: `bg-primary`, `hover:bg-primary-hover`, `bg-primary-subtle`, `border-primary-border`, `text-text-main`, `text-text-muted`, `bg-bg-main`, `bg-surface`.
**Halaman baru jangan lagi memakai hex hardcode** (`bg-[#1E3E34]`); gunakan token di atas.

### Warna status fungsional (tetap)

| Status | Latar | Teks | Garis |
|---|---|---|---|
| Pesanan Baru | `#FEF3C7` | `#92400E` | `#FCD34D` |
| Dijadwalkan / Dikerjakan / Verifikasi | `#E0F2FE` | `#0369A1` | `#BAE6FD` |
| Menunggu Pembayaran | `#FFEDD5` | `#C2410C` | `#FDBA74` |
| Selesai & Lunas | `#DCFCE7` | `#15803D` | `#86EFAC` |
| Dibatalkan / Error | `#FEE2E2` | `#B91C1C` | `#FCA5A5` |

---

## 3. Tipografi

- **Sans**: `Plus Jakarta Sans` (400, 500, 600, 700, 800), dimuat via Google Fonts di layout dengan `preconnect`. Fallback: `-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif`.
- **Mono**: `JetBrains Mono` (500, 600) untuk nomor pesanan, nomor WA, nominal. Gunakan `font-mono`.
- Dasar: `html` 16px, `line-height` 1.65.

| Elemen | Ukuran |
|---|---|
| Judul halaman | 24–28px, `font-extrabold`/`font-bold` |
| Judul bagian/kartu | 18–20px, `font-bold` |
| Label form, tombol, isi | **16px** (`text-base`), `font-semibold` untuk label/tombol |
| Catatan/tanggal | 14px (`text-sm`) |

> **Aturan**: tidak ada teks isi/label/tombol di bawah 14px. Hindari `text-xs` dan `text-[11px]` pada halaman hasil refaktor, kecuali label bottom nav (12px, bold, selalu dengan ikon + teks).
> Input otomatis minimal 16px (mencegah zoom iOS).

---

## 4. Standar Ramah Orang Tua (WAJIB)

1. Tombol aksi utama: tinggi **minimal 48px**, lebar penuh di HP, radius 12px, teks 16px semibold.
2. Seluruh target sentuh (tombol, link menu, tombol tutup): **minimal 44–48px** (utility `tap-target`).
3. Input: tinggi 48px, border 2px/1.5px jelas, fokus memakai ring hijau.
4. Jarak antar elemen interaktif lega (min. 8px) agar tidak salah sentuh.
5. Fokus keyboard: `:focus-visible` otomatis memakai outline 3px `primary-light` (global di `app.css`).
6. Informasi penting tidak hanya mengandalkan warna: sertakan ikon/teks (mis. banner flash punya ikon + teks).
7. Label tombol bahasa Indonesia yang jelas ("Keluar", "Masuk Petugas", "Kirim Pesanan Servis").

---

## 5. Master Layout (`layouts/app.blade.php`)

- **Header sticky** (tinggi 64px): logo gradien `primary → primary-light`, nama "Servis AC Masjid"; subjudul hanya tampil di ≥ `sm`.
- **Desktop (≥ `md`) Admin**: navigasi horizontal pill (Pesanan, Data Masjid & AC, Akun Petugas); halaman aktif = latar `primary` + teks putih + `aria-current="page"`.
- **Mobile Admin**: **Bottom Navigation** fixed (3 menu: Pesanan, Masjid, Petugas), tinggi ≥ 64px, ikon + label teks, aktif = `primary-subtle` + garis atas tebal. Memakai class `no-print` dan `safe-bottom` (safe area). `<body>` otomatis `pb-24` untuk admin di mobile agar konten tidak tertutup.
- **Tombol** "Masuk Petugas" (solid hijau) dan "Keluar" (outline hijau): `tap-target`.
- **Flash message**: banner besar (teks 16px semibold, ikon status, border 2px, tombol tutup 48px), `role="status"` untuk sukses dan `role="alert"` untuk error. Warna sukses hijau fungsional, error merah.
- **Footer**: satu baris ringkas, `no-print`.
- Kelas `no-print` dipakai untuk elemen yang disembunyikan saat cetak (header, footer, bottom nav, flash).

### Kontrak backend layout (JANGAN diubah)
`<meta name="csrf-token">`, `@vite(['resources/css/app.css', 'resources/js/app.js'])`, `{{ $slot ?? '' }}` + `@yield('content')`, `session('success')` / `session('error')`, form logout `POST route('logout')` + `@csrf`, logika `@auth` + `Auth::user()->isAdmin()`, dan cek `request()->routeIs('admin.dashboard' | 'admin.masjids.*' | 'admin.users.*')`.

---

## 6. Komponen

### Tombol utama
```html
<button class="w-full sm:w-auto min-h-[48px] px-6 bg-primary hover:bg-primary-hover text-white font-semibold text-base rounded-xl transition-colors">
  Simpan
</button>
```

### Tombol sekunder
```html
<button class="min-h-[48px] px-6 bg-white border-2 border-primary text-primary font-semibold text-base rounded-xl hover:bg-primary-subtle">
  Kembali
</button>
```

### Input
```html
<label class="block text-base font-semibold text-text-main">Nama Masjid</label>
<input class="w-full min-h-[48px] px-4 bg-white border-2 border-primary-border rounded-xl text-base placeholder-gray-400 focus:border-primary">
```

### Kartu
```html
<div class="bg-surface border border-primary-border rounded-2xl p-5 shadow-sm">...</div>
```

### Badge status
Kotak kecil bersudut tumpul, teks minimal 14px semibold, memakai warna status fungsional (bagian 2).

---

## 7. Aturan Cetak (Kuitansi)

`app.css` memuat `@media print` global: menyembunyikan `.no-print`, `header`, `footer`, `nav`; latar putih, teks hitam, tanpa bayangan. Tandai elemen interaktif (tombol cetak, tautan navigasi) dengan `no-print`.

---

## 8. Pantangan

1. Dilarang ikon/emoji dekoratif tanpa fungsi.
2. Dilarang teks abu-abu terang di atas latar putih.
3. Dilarang glassmorphism/blur/transparansi pada kartu.
4. Dilarang teks isi/tombol < 14px dan tombol < 44px.
5. Dilarang hex hijau hardcode baru; pakai token `primary*`.
6. Dilarang menghapus atribut `name`, `action`, `id`, atau script yang terhubung ke backend (lihat laporan audit bagian 5).
7. Aksen emas (`accent-gold`) maksimal 5% area.
