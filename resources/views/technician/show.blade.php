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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-lg bg-[#DCFCE7] border border-[#86EFAC] text-xs">
                <span class="text-[#15803D] font-semibold">
                    Status: Pekerjaan sedang berlangsung di lokasi masjid.
                </span>
                @if($order->status === 'dikerjakan')
                    <form method="POST" action="{{ route('technician.order.revert', $order->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan status tiba dan mengembalikannya ke status dijadwalkan?');" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-md border border-red-200 bg-white hover:bg-red-50 text-red-700 font-medium text-xs transition-colors cursor-pointer whitespace-nowrap self-start sm:self-auto">
                            Batalkan Status Tiba
                        </button>
                    </form>
                @endif
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
            <div class="space-y-2 pt-1">
                <span class="text-xs font-semibold text-gray-700 block uppercase tracking-wider">
                    Daftar Foto Bukti ({{ $order->photos->count() }}):
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($order->photos as $photo)
                        <div class="rounded-xl border border-gray-200 overflow-hidden bg-white shadow-xs flex flex-col justify-between">
                            <div class="relative cursor-pointer group bg-gray-100" @click="openLightbox('{{ $photo->url }}', '{{ addslashes($photo->caption ?? '') }}')">
                                <img src="{{ $photo->url }}" alt="Bukti Servis" class="w-full h-36 object-cover group-hover:opacity-90 transition-opacity">
                                <span class="absolute top-2 left-2 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider shadow-sm {{ $photo->photo_type === 'sebelum' ? 'bg-amber-100 text-amber-800 border border-amber-300' : ($photo->photo_type === 'sesudah' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-blue-100 text-blue-800 border border-blue-300') }}">
                                    {{ $photo->photo_type }}
                                </span>
                                <span class="absolute bottom-2 right-2 px-2 py-0.5 rounded bg-black/60 text-white text-[10px] font-medium">
                                    Ketuk untuk Perbesar
                                </span>
                            </div>

                            <div class="p-3 text-xs space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-gray-900 font-medium leading-snug">
                                        {{ $photo->caption ?: 'Tanpa keterangan' }}
                                    </p>
                                    <span class="text-[10px] text-gray-400 whitespace-nowrap">
                                        {{ $photo->created_at->translatedFormat('d M, H:i') }}
                                    </span>
                                </div>

                                <!-- Aksi CRUD: Edit & Hapus -->
                                <div class="flex items-center justify-end gap-1.5 pt-2 border-t border-gray-100">
                                    <button type="button" 
                                            @click="openEditPhoto({{ $photo->id }}, '{{ $photo->photo_type }}', '{{ addslashes($photo->caption ?? '') }}')"
                                            class="px-2.5 py-1.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium text-xs transition-colors cursor-pointer">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('technician.order.photo.destroy', $photo->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto bukti servis ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 font-medium text-xs transition-colors cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
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

                <!-- Pratinjau Gambar yang Baru Dipilih -->
                <div x-show="previewUrl" class="mt-3 p-3 bg-white border border-gray-200 rounded-lg space-y-2 text-left shadow-xs">
                    <div class="flex items-center justify-between text-xs pb-1 border-b border-gray-100">
                        <span class="font-semibold text-gray-900">Pratinjau Foto Terpilih:</span>
                        <button type="button" @click="clearSelectedPhoto" class="text-xs text-red-600 hover:text-red-800 font-semibold cursor-pointer">
                            Batal / Ganti
                        </button>
                    </div>
                    <div class="flex items-start gap-3">
                        <img :src="previewUrl" alt="Foto Terpilih" class="w-24 h-24 sm:w-28 sm:h-28 object-cover rounded-lg border border-gray-200 shadow-xs">
                        <div class="space-y-1.5 text-xs flex-1">
                            <span class="inline-block px-2 py-0.5 rounded font-semibold uppercase text-[10px]"
                                  :class="photoType === 'sebelum' ? 'bg-amber-100 text-amber-800 border border-amber-300' : (photoType === 'sesudah' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-blue-100 text-blue-800 border border-blue-300')"
                                  x-text="'Kategori: ' + photoType"></span>
                            <p class="text-gray-600 text-[11px] leading-relaxed" x-text="compressionStatus"></p>
                        </div>
                    </div>
                </div>
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
    <!-- LANGKAH 3: BIAYA TAMBAHAN SPAREPART / SUKU CADANG (LEBIH DARI 1 ITEM) -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-4" 
         x-data="{ 
             showAddForm: false,
             newItemName: '',
             newItemQty: 1,
             newItemPrice: 0,
             get newSubtotal() {
                 return (parseInt(this.newItemQty) || 0) * (parseFloat(this.newItemPrice) || 0);
             },
             formatRupiah(val) {
                 return new Intl.NumberFormat('id-ID').format(val);
             }
         }">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-md {{ $order->additional_amount > 0 ? 'bg-[#1E3E34] text-white' : 'bg-gray-200 text-gray-700' }} flex items-center justify-center font-bold text-xs">
                    3
                </span>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Tambahan Suku Cadang / Freon</h2>
                    <span class="text-xs text-gray-500">Bisa menambahkan lebih dari satu item</span>
                </div>
            </div>
            
            <button type="button" 
                    @click="showAddForm = !showAddForm"
                    class="text-xs font-semibold text-[#1E3E34] hover:underline cursor-pointer">
                <span x-text="showAddForm ? 'Tutup Form' : '+ Tambah Suku Cadang'"></span>
            </button>
        </div>

        <!-- DAFTAR SUKU CADANG YANG TELAH DITAMBAHKAN -->
        @if($order->spareparts->isNotEmpty())
            <div class="space-y-2">
                <span class="text-xs font-semibold text-gray-700 uppercase tracking-wider block">
                    Daftar Suku Cadang Tercatat ({{ $order->spareparts->count() }}):
                </span>

                <div class="divide-y divide-gray-100 rounded-lg border border-gray-200 bg-gray-50 overflow-hidden text-xs">
                    @foreach($order->spareparts as $item)
                        <div class="p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 hover:bg-gray-100/70 transition-colors">
                            <div class="space-y-0.5">
                                <strong class="text-sm font-semibold text-gray-900 block">{{ $item->item_name }}</strong>
                                <span class="text-gray-500 text-xs">
                                    {{ $item->quantity }} unit &times; Rp {{ number_format($item->price, 0, ',', '.') }} = 
                                    <span class="font-semibold text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </span>
                            </div>

                            <div class="flex items-center gap-1.5 self-end sm:self-auto">
                                <button type="button" 
                                        @click="openEditSparepart({{ $item->id }}, '{{ addslashes($item->item_name) }}', {{ $item->quantity }}, {{ (int)$item->price }})"
                                        class="px-2.5 py-1 rounded border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-medium text-xs transition-colors cursor-pointer">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('technician.order.sparepart.item.destroy', $item->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus suku cadang {{ addslashes($item->item_name) }} ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded border border-red-200 text-red-600 hover:bg-red-50 font-medium text-xs transition-colors cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <!-- Baris Total Biaya Tambahan -->
                    <div class="p-3.5 bg-white flex items-center justify-between font-bold text-xs border-t border-gray-200">
                        <span class="text-gray-700">Total Biaya Tambahan:</span>
                        <span class="text-sm text-[#1E3E34]">Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1 text-xs">
                    <button type="button" 
                            @click="showAddForm = true"
                            class="text-xs font-semibold text-[#1E3E34] hover:underline cursor-pointer">
                        + Tambah Suku Cadang Lainnya
                    </button>
                    <form method="POST" action="{{ route('technician.order.sparepart.destroy', $order->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh catatan suku cadang tambahan ini?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:underline cursor-pointer">
                            Hapus Semua Suku Cadang
                        </button>
                    </form>
                </div>
            </div>
        @elseif($order->additional_amount > 0)
            <!-- Tampilan Cadangan untuk Data Warisan / Legacy -->
            <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 text-xs space-y-2">
                <div class="flex justify-between font-semibold text-gray-900">
                    <span>Biaya Tambahan Tercatat:</span>
                    <span class="text-[#1E3E34] text-sm">Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</span>
                </div>
                <p class="text-gray-600 leading-relaxed">{{ $order->additional_notes }}</p>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200">
                    <form method="POST" action="{{ route('technician.order.sparepart.destroy', $order->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan biaya tambahan ini?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-2.5 py-1.5 rounded-md border border-red-200 text-red-600 hover:bg-red-50 font-semibold text-xs transition-colors cursor-pointer">
                            Hapus Biaya
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- FORM INPUT TAMBAH SUKU CADANG BARU -->
        <div x-show="showAddForm || {{ $order->spareparts->isEmpty() && $order->additional_amount == 0 ? 'true' : 'false' }}" class="pt-3 border-t border-gray-100">
            <h3 class="text-xs font-semibold text-gray-800 uppercase tracking-wider mb-2.5">
                {{ $order->spareparts->isNotEmpty() ? '+ Tambah Suku Cadang Berikutnya:' : 'Catat Suku Cadang / Tindakan Tambahan:' }}
            </h3>

            <form method="POST" action="{{ route('technician.order.sparepart', $order->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-800 mb-1">
                        Nama Suku Cadang / Tindakan: <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="item_name" 
                           x-model="newItemName"
                           required
                           placeholder="Contoh: Kapasitor 35uF, Tambah Freon R32 1kg, Pipa AC" 
                           class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1">
                            Jumlah (Qty): <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="quantity" 
                               x-model="newItemQty"
                               min="1" 
                               max="999" 
                               required
                               class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-800 mb-1">
                            Harga Satuan (Rp): <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="price" 
                               x-model="newItemPrice"
                               step="1000" 
                               min="0" 
                               required
                               placeholder="Contoh: 75000" 
                               class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white font-mono">
                    </div>
                </div>

                <!-- Preview Subtotal Otomatis -->
                <div class="p-2.5 bg-[#EBF2EE] rounded-lg border border-[#1E3E34]/20 text-xs flex justify-between items-center text-[#1E3E34]">
                    <span class="font-medium">Subtotal Item Ini:</span>
                    <span class="font-bold text-sm" x-text="'Rp ' + formatRupiah(newSubtotal)"></span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    @if($order->spareparts->isNotEmpty() || $order->additional_amount > 0)
                        <button type="button" @click="showAddForm = false" class="px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 cursor-pointer">
                            Batal
                        </button>
                    @endif
                    <button type="submit" 
                            class="px-4 py-2 bg-[#1E3E34] hover:bg-[#142B24] text-white font-semibold text-xs rounded-lg transition-colors cursor-pointer">
                        + Simpan Suku Cadang ke Daftar
                    </button>
                </div>
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

    <!-- ========================================================================= -->
    <!-- MODAL EDIT KETERANGAN & KATEGORI FOTO -->
    <!-- ========================================================================= -->
    <div x-show="showEditPhotoModal" x-cloak class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showEditPhotoModal = false" class="bg-white rounded-xl border border-gray-200 max-w-md w-full p-6 space-y-4 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Perbarui Foto Bukti Servis</h3>
                <button type="button" @click="showEditPhotoModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold cursor-pointer">&times;</button>
            </div>

            <form :action="'/teknisi/order/foto/' + editPhotoId" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                @csrf
                @method('PUT')

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">Kategori Foto:</label>
                    <select name="photo_type" x-model="editPhotoType" required class="w-full min-h-[42px] px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                        <option value="sebelum">Sebelum Servis</option>
                        <option value="sesudah">Sesudah Servis</option>
                        <option value="sparepart">Sparepart / Komponen</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">Keterangan Foto:</label>
                    <input type="text" name="caption" x-model="editPhotoCaption" placeholder="Contoh: AC Shaf Depan Kanan" class="w-full min-h-[42px] px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">Ganti File Gambar (Opsional):</label>
                    <input type="file" name="photo" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                    <span class="text-[11px] text-gray-500 block">Pilih file baru jika ingin mengganti foto yang sudah ada.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="showEditPhotoModal = false" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1E3E34] hover:bg-[#142B24] text-white text-xs font-semibold cursor-pointer transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL LIGHTBOX / PRATINJAU BESAR FOTO -->
    <!-- ========================================================================= -->
    <div x-show="activeLightboxUrl" x-cloak class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4" @click.self="activeLightboxUrl = ''">
        <div class="bg-white rounded-xl border border-gray-200 max-w-lg w-full overflow-hidden shadow-2xl">
            <div class="p-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-800 truncate" x-text="lightboxCaption || 'Foto Bukti Servis'"></span>
                <button type="button" @click="activeLightboxUrl = ''" class="text-gray-500 hover:text-gray-800 text-lg font-bold cursor-pointer">&times;</button>
            </div>
            <div class="p-2 bg-black flex items-center justify-center">
                <img :src="activeLightboxUrl" alt="Foto Penuh" class="max-h-[70vh] object-contain w-auto rounded">
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL EDIT SUKU CADANG / SPAREPART -->
    <!-- ========================================================================= -->
    <div x-show="showEditSparepartModal" x-cloak class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showEditSparepartModal = false" class="bg-white rounded-xl border border-gray-200 max-w-md w-full p-6 space-y-4 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Perbarui Suku Cadang</h3>
                <button type="button" @click="showEditSparepartModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold cursor-pointer">&times;</button>
            </div>

            <form :action="'/teknisi/order/sparepart/' + editSparepartId" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">Nama Suku Cadang / Tindakan: <span class="text-red-500">*</span></label>
                    <input type="text" name="item_name" x-model="editSparepartName" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-gray-800">Jumlah (Qty): <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" x-model="editSparepartQty" min="1" max="999" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white font-mono">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-gray-800">Harga Satuan (Rp): <span class="text-red-500">*</span></label>
                        <input type="number" name="price" x-model="editSparepartPrice" step="1000" min="0" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white font-mono">
                    </div>
                </div>

                <div class="p-2.5 bg-[#EBF2EE] rounded-lg border border-[#1E3E34]/20 text-xs flex justify-between items-center text-[#1E3E34]">
                    <span class="font-medium">Subtotal Item:</span>
                    <span class="font-bold text-sm" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format((parseInt(editSparepartQty) || 0) * (parseFloat(editSparepartPrice) || 0))"></span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="showEditSparepartModal = false" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1E3E34] hover:bg-[#142B24] text-white text-xs font-semibold cursor-pointer transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Script Kompresi & Pratinjau Foto di Browser (Client-side HTML5 Canvas) -->
<script>
function technicianWork() {
    return {
        photoType: 'sebelum',
        readyToUpload: false,
        isUploading: false,
        compressionStatus: '',
        compressedFile: null,
        previewUrl: '',
        activeLightboxUrl: '',
        lightboxCaption: '',
        showEditPhotoModal: false,
        editPhotoId: null,
        editPhotoType: 'sebelum',
        editPhotoCaption: '',
        showEditSparepartModal: false,
        editSparepartId: null,
        editSparepartName: '',
        editSparepartQty: 1,
        editSparepartPrice: 0,

        openEditSparepart(id, name, qty, price) {
            this.editSparepartId = id;
            this.editSparepartName = name;
            this.editSparepartQty = qty;
            this.editSparepartPrice = price;
            this.showEditSparepartModal = true;
        },

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

                        this.previewUrl = URL.createObjectURL(blob);
                        this.compressionStatus = `Foto siap! Ukuran disusutkan dari ${originalSizeMb} MB menjadi ${compressedSizeKb} KB (Hemat kuota).`;
                        this.readyToUpload = true;
                    }, 'image/jpeg', 0.75);
                };
            };
        },

        clearSelectedPhoto() {
            this.previewUrl = '';
            this.compressionStatus = '';
            this.readyToUpload = false;
            this.compressedFile = null;
            const camInput = document.getElementById('cameraInput');
            if (camInput) camInput.value = '';
            const compInput = document.getElementById('compressedPhotoInput');
            if (compInput) compInput.value = '';
        },

        openLightbox(url, caption) {
            this.activeLightboxUrl = url;
            this.lightboxCaption = caption;
        },

        openEditPhoto(id, type, caption) {
            this.editPhotoId = id;
            this.editPhotoType = type;
            this.editPhotoCaption = caption;
            this.showEditPhotoModal = true;
        },

        handleSubmitPhoto() {
            this.isUploading = true;
        }
    };
}
</script>
@endsection
