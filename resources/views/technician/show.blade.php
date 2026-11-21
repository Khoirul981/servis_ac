@extends('layouts.app', ['title' => 'Lembar Kerja Servis - ' . $order->masjid->name])

@section('content')
<div class="max-w-xl mx-auto space-y-5 py-2" x-data="technicianWork()">

    <!-- Tombol Kembali -->
    <a href="{{ route('technician.dashboard') }}" class="inline-flex items-center text-xs font-medium text-gray-600 hover:text-gray-900 transition-colors">
        &larr; Kembali ke Daftar Tugas
    </a>

    <!-- Header Kartu Masjid Solid -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-3">
        <div class="flex items-start justify-between gap-2">
            <div>
                <span class="text-xs font-mono font-medium text-gray-500 block">{{ $order->order_number }}</span>
                <h1 class="text-xl font-bold text-gray-900 mt-0.5">{{ $order->masjid->name }}</h1>
            </div>
            <span class="px-3 py-1 rounded-md text-xs font-semibold {{ $order->status === 'dikerjakan' ? 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]' : 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]' }}">
                {{ $order->statusLabel() }}
            </span>
        </div>

        <p class="text-xs text-gray-600 leading-relaxed">{{ $order->masjid->address }}</p>

        <div class="pt-2 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs">
            <div>
                <span class="block text-gray-400">Pengurus:</span>
                <strong class="text-gray-800">{{ $order->masjid->pic_name }}</strong>
            </div>
            <div>
                <span class="block text-gray-400">Keluhan:</span>
                <strong class="text-gray-800">{{ $order->dkm_complaint }} ({{ $order->ac_count }} Unit)</strong>
            </div>
        </div>

        <!-- Dua Tombol Navigasi Cepat -->
        <div class="grid grid-cols-2 gap-2 pt-2">
            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" 
               class="min-h-[44px] px-3 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-800 text-xs font-medium flex items-center justify-center">
                Buka Google Maps
            </a>
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
               class="min-h-[44px] px-3 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-800 text-xs font-medium flex items-center justify-center">
                Chat WhatsApp DKM
            </a>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- LANGKAH 1: MULAI KERJA DI LOKASI -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-3">
        <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-md {{ $order->status === 'dikerjakan' ? 'bg-[#1E3E34] text-white' : 'bg-gray-200 text-gray-700' }} flex items-center justify-center font-bold text-xs">
                1
            </span>
            <h2 class="text-base font-semibold text-gray-900">Kedatangan di Lokasi</h2>
        </div>

        @if($order->status === 'dijadwalkan')
            <p class="text-xs text-gray-600 leading-relaxed">
                Tekan tombol di bawah saat Anda telah tiba di masjid untuk menandai bahwa pekerjaan servis dimulai.
            </p>
            <form method="POST" action="{{ route('technician.order.start', $order->id) }}">
                @csrf
                <button type="submit" 
                        class="w-full min-h-[48px] py-3 px-5 bg-[#1E3E34] hover:bg-[#142B24] text-white font-medium text-sm rounded-lg shadow-xs flex items-center justify-center cursor-pointer transition-colors">
                    Saya Sudah Tiba & Mulai Bekerja
                </button>
            </form>
        @else
            <div class="p-3 rounded-lg bg-[#DCFCE7] border border-[#86EFAC] text-[#15803D] text-xs font-semibold">
                Status: Pekerjaan sedang berlangsung di lokasi masjid.
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- LANGKAH 2: FOTO BUKTI SERVIS DENGAN KOTAK PUTUS-PUTUS (DASHED) -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-md {{ $order->photos->isNotEmpty() ? 'bg-[#1E3E34] text-white' : 'bg-gray-200 text-gray-700' }} flex items-center justify-center font-bold text-xs">
                    2
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Foto Bukti Servis</h2>
                    <span class="text-xs text-gray-500">Wajib minimal 1 foto fisik</span>
                </div>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-md {{ $order->photos->isNotEmpty() ? 'bg-[#DCFCE7] text-[#15803D]' : 'bg-[#FFEDD5] text-[#C2410C]' }}">
                {{ $order->photos->count() }} Foto Diunggah
            </span>
        </div>

        <!-- Pratinjau Galeri Mini -->
        @if($order->photos->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 pt-1">
                @foreach($order->photos as $photo)
                    <div class="rounded-lg border border-gray-200 overflow-hidden bg-gray-50 relative">
                        <img src="{{ $photo->url }}" alt="Bukti Servis" class="w-full h-24 object-cover">
                        <div class="p-2 text-[11px]">
                            <span class="inline-block px-1.5 py-0.5 rounded font-semibold uppercase {{ $photo->photo_type === 'sebelum' ? 'bg-amber-100 text-amber-800' : ($photo->photo_type === 'sesudah' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800') }}">
                                {{ $photo->photo_type }}
                            </span>
                            @if($photo->caption)
                                <p class="text-gray-600 truncate mt-1">{{ $photo->caption }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Form Upload Foto dengan Kotak Putus-Putus Sesuai Panduan -->
        <form method="POST" action="{{ route('technician.order.photo', $order->id) }}" enctype="multipart/form-data" @submit="handleSubmitPhoto" class="space-y-3 pt-2 border-t border-gray-100">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-800 mb-1">Kategori Foto:</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="border rounded-lg p-2.5 text-center cursor-pointer text-xs font-medium transition-all"
                           :class="photoType === 'sebelum' ? 'border-[#1E3E34] bg-[#EBF2EE] text-[#1E3E34] font-semibold' : 'border-gray-200 text-gray-700 bg-white'">
                        <input type="radio" name="photo_type" value="sebelum" x-model="photoType" class="sr-only">
                        <span>Sebelum</span>
                    </label>
                    <label class="border rounded-lg p-2.5 text-center cursor-pointer text-xs font-medium transition-all"
                           :class="photoType === 'sesudah' ? 'border-[#1E3E34] bg-[#EBF2EE] text-[#1E3E34] font-semibold' : 'border-gray-200 text-gray-700 bg-white'">
                        <input type="radio" name="photo_type" value="sesudah" x-model="photoType" class="sr-only">
                        <span>Sesudah</span>
                    </label>
                    <label class="border rounded-lg p-2.5 text-center cursor-pointer text-xs font-medium transition-all"
                           :class="photoType === 'sparepart' ? 'border-[#1E3E34] bg-[#EBF2EE] text-[#1E3E34] font-semibold' : 'border-gray-200 text-gray-700 bg-white'">
                        <input type="radio" name="photo_type" value="sparepart" x-model="photoType" class="sr-only">
                        <span>Sparepart</span>
                    </label>
                </div>
            </div>

            <!-- Kotak Upload Garis Putus-Putus (Sesuai Panduan Section 5.2) -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 text-center hover:bg-gray-100 transition-colors">
                <label for="cameraInput" class="block cursor-pointer">
                    <span class="block text-sm font-semibold text-gray-800">Ambil Foto Kamera</span>
                    <span class="block text-xs text-gray-500 mt-0.5">Sentuh untuk memanggil kamera atau galeri</span>
                    <input type="file" 
                           id="cameraInput"
                           accept="image/*" 
                           capture="environment"
                           @change="handleFileSelect"
                           class="sr-only">
                </label>

                <input type="file" name="photo" id="compressedPhotoInput" class="hidden">

                <!-- Pratinjau & Status Kompresi -->
                <div x-show="compressionStatus" class="mt-2 text-xs font-medium text-[#1E3E34]" x-text="compressionStatus"></div>
            </div>

            <!-- Keterangan Singkat -->
            <div>
                <input type="text" 
                       name="caption" 
                       placeholder="Keterangan foto (contoh: AC Shaf Depan)" 
                       class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
            </div>

            <button type="submit" 
                    :disabled="!readyToUpload || isUploading"
                    class="w-full min-h-[46px] py-2.5 px-4 bg-gray-900 hover:bg-black disabled:opacity-40 disabled:cursor-not-allowed text-white font-medium text-xs rounded-lg transition-colors cursor-pointer">
                <span x-text="isUploading ? 'Sedang Mengunggah...' : 'Unggah Foto Ini'"></span>
            </button>
        </form>
    </div>

    <!-- ========================================================================= -->
    <!-- LANGKAH 3: BIAYA TAMBAHAN SPAREPART (OPSIONAL) -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-3" x-data="{ openSparepart: {{ $order->additional_amount > 0 ? 'true' : 'false' }} }">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-md bg-gray-200 text-gray-700 flex items-center justify-center font-bold text-xs">
                    3
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Tambahan Suku Cadang / Freon</h2>
                    <span class="text-xs text-gray-500">Opsional jika ada biaya tak terduga</span>
                </div>
            </div>
            <button type="button" 
                    @click="openSparepart = !openSparepart"
                    class="text-xs font-semibold text-[#1E3E34] hover:underline cursor-pointer">
                <span x-text="openSparepart ? 'Tutup' : '+ Buka Form'"></span>
            </button>
        </div>

        @if($order->additional_amount > 0)
            <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 text-xs space-y-1">
                <div class="flex justify-between font-semibold text-gray-900">
                    <span>Biaya Tambahan Tercatat:</span>
                    <span>Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</span>
                </div>
                <p class="text-gray-600">{{ $order->additional_notes }}</p>
            </div>
        @endif

        <div x-show="openSparepart" class="pt-2 border-t border-gray-100">
            <form method="POST" action="{{ route('technician.order.sparepart', $order->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label for="additional_amount" class="block text-xs font-medium text-gray-800 mb-1">
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
                           class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>

                <div>
                    <label for="additional_notes" class="block text-xs font-medium text-gray-800 mb-1">
                        Rincian Suku Cadang:
                    </label>
                    <textarea name="additional_notes" 
                              id="additional_notes" 
                              rows="2" 
                              required
                              placeholder="Contoh: Tambah Freon R32 1 kg & Ganti Kapasitor Outdoor"
                              class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">{{ old('additional_notes', $order->additional_notes) }}</textarea>
                </div>

                <button type="submit" 
                        class="w-full min-h-[44px] py-2 px-4 bg-gray-800 hover:bg-gray-900 text-white font-medium text-xs rounded-lg transition-colors cursor-pointer">
                    Simpan Rincian Tambahan
                </button>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- LANGKAH 4: SELESAIKAN PEKERJAAN -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-4">
        <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-md bg-[#1E3E34] text-white flex items-center justify-center font-bold text-xs">
                4
            </span>
            <div>
                <h2 class="text-base font-semibold text-gray-900">Pekerjaan Selesai</h2>
                <span class="text-xs text-gray-500">Kirim laporan ke admin untuk proses pelunasan</span>
            </div>
        </div>

        <!-- Rincian Biaya Akhir -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-xs space-y-1.5">
            <div class="flex justify-between text-gray-600">
                <span>Biaya Servis Dasar ({{ $order->ac_count }} Unit):</span>
                <span>Rp {{ number_format($order->base_amount, 0, ',', '.') }}</span>
            </div>
            @if($order->additional_amount > 0)
                <div class="flex justify-between text-gray-800 font-medium">
                    <span>Tambahan Sparepart:</span>
                    <span>Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="pt-2 border-t border-gray-200 flex justify-between font-bold text-sm text-gray-900">
                <span>Total Tagihan:</span>
                <span class="text-[#1E3E34]">Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</span>
            </div>
        </div>

        @if($order->photos->isEmpty())
            <div class="p-3 rounded-lg bg-[#FFEDD5] border border-[#FDBA74] text-[#C2410C] text-xs font-medium">
                Peringatan: Tombol selesai terkunci. Anda wajib mengunggah minimal 1 foto bukti fisik sebelum menyelesaikan pekerjaan.
            </div>
        @endif

        <form method="POST" action="{{ route('technician.order.complete', $order->id) }}">
            @csrf
            <button type="submit" 
                    @if($order->photos->isEmpty()) disabled @endif
                    class="w-full min-h-[48px] py-3.5 px-6 bg-[#1E3E34] hover:bg-[#142B24] disabled:opacity-40 disabled:cursor-not-allowed text-white font-semibold text-base rounded-lg shadow-xs transition-colors flex items-center justify-center cursor-pointer">
                Pekerjaan Servis Selesai
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

                    canvas.toBlob((blob) => {
                        const originalSizeMb = (file.size / (1024 * 1024)).toFixed(2);
                        const compressedSizeKb = Math.round(blob.size / 1024);
                        
                        this.compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });

                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(this.compressedFile);
                        document.getElementById('compressedPhotoInput').files = dataTransfer.files;

                        this.compressionStatus = `Foto siap! Ukuran disusutkan dari ${originalSizeMb} MB menjadi ${compressedSizeKb} KB (Hemat kuota).`;
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
