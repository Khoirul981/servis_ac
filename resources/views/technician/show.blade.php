@extends('layouts.app', ['title' => 'Lembar Kerja Servis - ' . $order->masjid->name])

@section('content')
<div class="max-w-xl mx-auto space-y-6 py-2" x-data="technicianWork()">

    <!-- Tombol Kembali -->
    <a href="{{ route('technician.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors">
        <span>&larr;</span>
        <span>Kembali ke Daftar Tugas</span>
    </a>

    <!-- Header Kartu Masjid -->
    <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-3">
        <div class="flex items-start justify-between gap-2">
            <div>
                <span class="text-xs font-mono font-bold text-blue-700 block">{{ $order->order_number }}</span>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900">{{ $order->masjid->name }}</h1>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-black {{ $order->status === 'dikerjakan' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                {{ $order->statusLabel() }}
            </span>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed">{{ $order->masjid->address }}</p>

        <div class="pt-2 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs">
            <div>
                <span class="block text-slate-400">Pengurus / PIC:</span>
                <strong class="text-slate-800">{{ $order->masjid->pic_name }}</strong>
            </div>
            <div>
                <span class="block text-slate-400">Keluhan:</span>
                <strong class="text-slate-800">{{ $order->dkm_complaint }} ({{ $order->ac_count }} Unit)</strong>
            </div>
        </div>

        <!-- Tombol Maps & WA Cepat -->
        <div class="grid grid-cols-2 gap-2 pt-2">
            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" 
               class="min-h-[44px] px-3 py-2 rounded-xl border border-blue-200 bg-blue-50 text-blue-800 text-xs font-bold flex items-center justify-center gap-1.5">
                <span>🗺️</span> Buka Peta GPS
            </a>
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
               class="min-h-[44px] px-3 py-2 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 text-xs font-bold flex items-center justify-center gap-1.5">
                <span>💬</span> Hubungi DKM
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- LANGKAH 1: MULAI KERJA DI LOKASI -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-3">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl {{ $order->status === 'dikerjakan' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700' }} flex items-center justify-center font-bold text-sm">
                1
            </span>
            <h2 class="text-base font-extrabold text-slate-900">Kedatangan di Lokasi</h2>
        </div>

        @if($order->status === 'dijadwalkan')
            <p class="text-xs text-slate-600 leading-relaxed">
                Jika Anda sudah sampai di masjid, klik tombol di bawah untuk menandai bahwa pengerjaan servis telah dimulai.
            </p>
            <form method="POST" action="{{ route('technician.order.start', $order->id) }}">
                @csrf
                <button type="submit" 
                        class="w-full min-h-[50px] py-3 px-5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black text-sm rounded-2xl shadow-sm flex items-center justify-center gap-2 cursor-pointer transition-all">
                    <span>🟢</span>
                    <span>Saya Sudah Tiba & Mulai Bekerja</span>
                </button>
            </form>
        @else
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center gap-2 text-emerald-900 text-xs font-bold">
                <span>✅</span>
                <span>Pekerjaan sedang berlangsung di lokasi masjid.</span>
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- LANGKAH 2: FOTO BUKTI SERVIS DENGAN KOMPRESI OTOMATIS BROWSER -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl {{ $order->photos->isNotEmpty() ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700' }} flex items-center justify-center font-bold text-sm">
                    2
                </span>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Foto Bukti Servis</h2>
                    <span class="text-xs text-slate-500">Wajib minimal 1 foto sebelum/sesudah</span>
                </div>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $order->photos->isNotEmpty() ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                {{ $order->photos->count() }} Foto Diunggah
            </span>
        </div>

        <!-- Galeri Foto Yang Sudah Diunggah -->
        @if($order->photos->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 pt-1">
                @foreach($order->photos as $photo)
                    <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-50 relative group">
                        <img src="{{ $photo->url }}" alt="Bukti Servis" class="w-full h-28 object-cover">
                        <div class="p-2 text-[11px]">
                            <span class="inline-block px-1.5 py-0.5 rounded font-bold uppercase {{ $photo->photo_type === 'sebelum' ? 'bg-amber-100 text-amber-800' : ($photo->photo_type === 'sesudah' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800') }}">
                                {{ $photo->photo_type }}
                            </span>
                            @if($photo->caption)
                                <p class="text-slate-600 truncate mt-1">{{ $photo->caption }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Form Upload Foto dengan Kompresi Otomatis di Browser -->
        <form method="POST" action="{{ route('technician.order.photo', $order->id) }}" enctype="multipart/form-data" @submit="handleSubmitPhoto" class="space-y-3 pt-2 border-t border-slate-100">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-800 mb-1">Kategori Foto:</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="border rounded-xl p-2.5 text-center cursor-pointer text-xs font-bold transition-all"
                           :class="photoType === 'sebelum' ? 'border-emerald-600 bg-emerald-50 text-emerald-900 ring-2 ring-emerald-500' : 'border-slate-200 text-slate-700'">
                        <input type="radio" name="photo_type" value="sebelum" x-model="photoType" class="sr-only">
                        <span>📸 Sebelum</span>
                    </label>
                    <label class="border rounded-xl p-2.5 text-center cursor-pointer text-xs font-bold transition-all"
                           :class="photoType === 'sesudah' ? 'border-emerald-600 bg-emerald-50 text-emerald-900 ring-2 ring-emerald-500' : 'border-slate-200 text-slate-700'">
                        <input type="radio" name="photo_type" value="sesudah" x-model="photoType" class="sr-only">
                        <span>✨ Sesudah</span>
                    </label>
                    <label class="border rounded-xl p-2.5 text-center cursor-pointer text-xs font-bold transition-all"
                           :class="photoType === 'sparepart' ? 'border-emerald-600 bg-emerald-50 text-emerald-900 ring-2 ring-emerald-500' : 'border-slate-200 text-slate-700'">
                        <input type="radio" name="photo_type" value="sparepart" x-model="photoType" class="sr-only">
                        <span>⚙️ Sparepart</span>
                    </label>
                </div>
            </div>

            <!-- Input Kamera / File Gambar -->
            <div>
                <label class="block text-xs font-bold text-slate-800 mb-1">Ambil Foto (Kamera HP):</label>
                <input type="file" 
                       id="cameraInput"
                       accept="image/*" 
                       capture="environment"
                       @change="handleFileSelect"
                       class="block w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                
                <input type="file" name="photo" id="compressedPhotoInput" class="hidden">

                <!-- Status Kompresi Gambar -->
                <div x-show="compressionStatus" class="mt-2 text-xs font-medium text-emerald-700 flex items-center gap-1.5" x-text="compressionStatus"></div>
            </div>

            <!-- Keterangan Singkat -->
            <div>
                <input type="text" 
                       name="caption" 
                       placeholder="Keterangan foto (contoh: AC Shaf Depan)" 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <button type="submit" 
                    :disabled="!readyToUpload || isUploading"
                    class="w-full min-h-[46px] py-2.5 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs rounded-xl shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                <span x-text="isUploading ? '⏳ Mengunggah...' : '📤 Simpan & Unggah Foto Ini'"></span>
            </button>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- LANGKAH 3: BIAYA TAMBAHAN SPAREPART (OPSIONAL) -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-3" x-data="{ openSparepart: {{ $order->additional_amount > 0 ? 'true' : 'false' }} }">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-sm">
                    3
                </span>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Ada Tambahan Biaya / Sparepart?</h2>
                    <span class="text-xs text-slate-500">Opsional jika ada pengisian freon / pergantian suku cadang</span>
                </div>
            </div>
            <button type="button" 
                    @click="openSparepart = !openSparepart"
                    class="text-xs font-bold text-blue-700 hover:underline cursor-pointer">
                <span x-text="openSparepart ? 'Tutup' : '+ Buka Form'"></span>
            </button>
        </div>

        @if($order->additional_amount > 0)
            <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1">
                <div class="flex justify-between font-bold">
                    <span>Biaya Tambahan Tercatat:</span>
                    <span>Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</span>
                </div>
                <p class="text-slate-600">{{ $order->additional_notes }}</p>
            </div>
        @endif

        <div x-show="openSparepart" class="pt-2 border-t border-slate-100">
            <form method="POST" action="{{ route('technician.order.sparepart', $order->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label for="additional_amount" class="block text-xs font-bold text-slate-800 mb-1">
                        Nominal Biaya Tambahan (Rp):
                    </label>
                    <input type="number" 
                           name="additional_amount" 
                           id="additional_amount" 
                           value="{{ old('additional_amount', (int)$order->additional_amount) }}"
                           step="1000"
                           min="0"
                           required
                           placeholder="Contoh: 150000" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold text-slate-900 outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="additional_notes" class="block text-xs font-bold text-slate-800 mb-1">
                        Rincian Suku Cadang / Tindakan:
                    </label>
                    <textarea name="additional_notes" 
                              id="additional_notes" 
                              rows="2" 
                              required
                              placeholder="Contoh: Tambah Freon R32 1 kg & Ganti Kapasitor Outdoor 35uF"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-emerald-500">{{ old('additional_notes', $order->additional_notes) }}</textarea>
                </div>

                <button type="submit" 
                        class="w-full min-h-[44px] py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer">
                    Simpan Catatan Sparepart
                </button>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- LANGKAH 4: SELESAIKAN PEKERJAAN -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">
                4
            </span>
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Pekerjaan Selesai</h2>
                <span class="text-xs text-slate-500">Kirim laporan ke admin untuk verifikasi pembayaran</span>
            </div>
        </div>

        <!-- Rincian Total Akhir -->
        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-xs space-y-1.5">
            <div class="flex justify-between text-slate-600">
                <span>Biaya Servis Dasar ({{ $order->ac_count }} Unit):</span>
                <span>Rp {{ number_format($order->base_amount, 0, ',', '.') }}</span>
            </div>
            @if($order->additional_amount > 0)
                <div class="flex justify-between text-amber-800 font-semibold">
                    <span>Tambahan Sparepart:</span>
                    <span>Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="pt-2 border-t border-slate-200 flex justify-between font-black text-sm text-slate-900">
                <span>Total Tagihan:</span>
                <span class="text-emerald-700">Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</span>
            </div>
        </div>

        @if($order->photos->isEmpty())
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                <span>⚠️</span>
                <span>Tombol selesai terkunci. Anda wajib mengunggah minimal 1 foto bukti pada Langkah 2 terlebih dahulu.</span>
            </div>
        @endif

        <form method="POST" action="{{ route('technician.order.complete', $order->id) }}">
            @csrf
            <button type="submit" 
                    @if($order->photos->isEmpty()) disabled @endif
                    class="w-full min-h-[52px] py-3.5 px-6 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-base rounded-2xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                <span>✅</span>
                <span>Pekerjaan Servis Selesai</span>
            </button>
        </form>
    </div>

</div>

<!-- Script Kompresi Foto Otomatis di Sisi Browser (Client-side HTML5 Canvas) -->
<script>
function technicianWork() {
    return {
        photoType: 'sebelum',
        readyToUpload: false,
        isUploading: false,
        compressionStatus: '',
        compressedFile: null,

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.compressionStatus = 'Mengompres foto di HP...';
            this.readyToUpload = false;

            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (e) => {
                const img = new Image();
                img.src = e.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

                    // Skala foto maksimal lebar/tinggi 1280px agar hemat memori & kuota
                    const maxDim = 1280;
                    if (width > maxDim || height > maxDim) {
                        if (width > height) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        } else {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    // Kompresi kualitas 75% JPEG
                    canvas.toBlob((blob) => {
                        const originalSizeMb = (file.size / (1024 * 1024)).toFixed(2);
                        const compressedSizeKb = Math.round(blob.size / 1024);
                        
                        this.compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });

                        // Set file ke hidden input form
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(this.compressedFile);
                        document.getElementById('compressedPhotoInput').files = dataTransfer.files;

                        this.compressionStatus = `✅ Foto siap! Ukuran awal ${originalSizeMb} MB disusutkan menjadi ${compressedSizeKb} KB (Hemat Kuota).`;
                        this.readyToUpload = true;
                    }, 'image/jpeg', 0.75);
                };
            };
        },

        handleSubmitPhoto() {
            this.isUploading = true;
        }
    };
}
</script>
@endsection
