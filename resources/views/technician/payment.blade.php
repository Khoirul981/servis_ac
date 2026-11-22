@extends('layouts.app', ['title' => 'Pelaporan Pembayaran - ' . $order->masjid->name])

@section('content')
<div class="max-w-xl mx-auto space-y-5 py-2" x-data="paymentReport()">

    <!-- Tombol Navigasi Atas -->
    <div class="flex items-center justify-between">
        <a href="{{ route('technician.order.show', $order->id) }}" class="inline-flex items-center text-xs font-semibold text-gray-600 hover:text-gray-900 transition-colors">
            &larr; Lihat Lembar Kerja Servis (Arsip)
        </a>
        <a href="{{ route('technician.dashboard') }}" class="text-xs font-medium text-gray-500 hover:text-gray-800">
            Beranda Tugas
        </a>
    </div>

    <!-- Header Kartu Masjid & Status Terkunci -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-3">
        <div class="flex items-start justify-between gap-2">
            <div>
                <span class="text-xs font-mono font-medium text-gray-500 block">{{ $order->order_number }}</span>
                <h1 class="text-xl font-bold text-gray-900 mt-0.5">{{ $order->masjid->name }}</h1>
            </div>
            <span class="px-3 py-1 rounded-md text-xs font-semibold 
                {{ $order->isPaid() ? 'bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC]' : ($order->status === 'menunggu_verifikasi' ? 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]' : 'bg-[#FEF3C7] text-[#92400E] border border-[#FCD34D]') }}">
                {{ $order->statusLabel() }}
            </span>
        </div>

        <p class="text-xs text-gray-600 leading-relaxed">{{ $order->masjid->address }}</p>

        <div class="pt-2 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs">
            <div>
                <span class="block text-gray-400">Pengurus (DKM):</span>
                <strong class="text-gray-800">{{ $order->masjid->pic_name }} ({{ $order->masjid->pic_phone }})</strong>
            </div>
            <div>
                <span class="block text-gray-400">Pengerjaan:</span>
                <strong class="text-gray-800">{{ $order->ac_count }} Unit AC &bull; {{ $order->dkm_complaint }}</strong>
            </div>
        </div>

        <!-- Banner Pekerjaan Fisik Telah Selesai & Terkunci -->
        <div class="p-3 bg-[#DCFCE7] rounded-lg border border-[#86EFAC] text-xs flex items-center justify-between">
            <span class="text-[#15803D] font-bold flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#15803D]"></span>
                Pekerjaan fisik telah selesai & data pengerjaan terkunci.
            </span>
            <span class="text-[#15803D] font-mono text-[11px] font-semibold">TERKUNCI</span>
        </div>
    </div>

    <!-- Rincian Biaya Servis Akhir -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-3">
        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">
            Rincian Tagihan Servis
        </h2>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-xs space-y-2">
            @if($order->serviceItems->isNotEmpty())
                <div class="space-y-1 pb-2 border-b border-gray-200">
                    <span class="text-gray-500 font-semibold block text-[11px] uppercase">Rincian Servis AC:</span>
                    @foreach($order->serviceItems as $sItem)
                        <div class="flex justify-between text-gray-700">
                            <span>{{ $sItem->quantity }}x {{ $sItem->ac_type }} - {{ $sItem->service_category }} @if($sItem->room_name)({{ $sItem->room_name }})@endif</span>
                            <span class="font-medium">Rp {{ number_format($sItem->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="flex justify-between text-gray-600">
                <span>Biaya Servis Dasar ({{ $order->ac_count }} Unit AC):</span>
                <span>Rp {{ number_format($order->base_amount, 0, ',', '.') }}</span>
            </div>
            @if($order->additional_amount > 0)
                <div class="flex justify-between text-gray-800 font-medium">
                    <span>Tambahan Suku Cadang / Tindakan:</span>
                    <span>Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</span>
                </div>
                @if($order->additional_notes)
                    <p class="text-[11px] text-gray-500 italic pl-2 border-l-2 border-gray-300">
                        Rincian: {{ $order->additional_notes }}
                    </p>
                @endif
            @endif
            <div class="pt-2 border-t border-gray-200 flex justify-between font-bold text-base text-gray-900">
                <span>Total Tagihan Pelunasan:</span>
                <span class="text-[#1E3E34]">Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BAGIAN STATUS PEMBAYARAN & UPLOAD BUKTI FOTO -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm space-y-4">
        <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-md {{ $order->isPaid() ? 'bg-[#15803D]' : ($order->status === 'menunggu_verifikasi' ? 'bg-[#0369A1]' : 'bg-[#D97706]') }} text-white flex items-center justify-center font-bold text-xs">
                @if($order->isPaid())
                    &#10003;
                @else
                    $
                @endif
            </span>
            <div>
                <h2 class="text-base font-semibold text-gray-900">Pelaporan Bukti Pembayaran</h2>
                <span class="text-xs text-gray-500">Kirim foto bukti transfer/pembayaran dari DKM ke Admin</span>
            </div>
        </div>

        @if($order->isPaid())
            <!-- KASUS 1: SUDAH DIBAYAR & DIVERIFIKASI ADMIN -->
            <div class="p-4 rounded-xl bg-[#DCFCE7] border border-[#86EFAC] space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#15803D] uppercase tracking-wider">
                        Pembayaran Telah Diverifikasi LUNAS
                    </span>
                    <span class="px-2.5 py-1 rounded text-xs font-bold bg-[#15803D] text-white">
                        {{ $order->paymentMethodLabel() }}
                    </span>
                </div>
                <p class="text-xs text-[#15803D] leading-relaxed">
                    Pembayaran telah diverifikasi oleh Admin pada <strong>{{ $order->paid_at ? $order->paid_at->translatedFormat('d F Y, H:i') : '-' }} WIB</strong>. Kuitansi resmi telah terbit.
                </p>
                <div class="pt-2 border-t border-[#86EFAC]/60">
                    <a href="{{ route('order.public_receipt', ['order_number' => $order->order_number]) }}" target="_blank"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-[#1E3E34] hover:bg-[#142B24] text-white font-semibold text-xs text-decoration-none transition-colors">
                        Buka Kuitansi Resmi &rarr;
                    </a>
                </div>
            </div>

        @elseif($order->status === 'menunggu_verifikasi' || $order->payment_proof)
            <!-- KASUS 2: BUKTI SUDAH DIUNGGAH, MENUNGGU VERIFIKASI ADMIN -->
            <div class="p-4 rounded-xl bg-[#FFF7ED] border border-[#FDBA74] space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-pulse"></span>
                        <span class="text-xs font-bold text-[#C2410C] uppercase tracking-wider">
                            Menunggu Verifikasi Admin
                        </span>
                    </div>
                    <span class="px-2.5 py-1 rounded text-xs font-bold bg-[#FFEDD5] text-[#9A3412] border border-[#FDBA74]">
                        {{ $order->paymentMethodLabel() }}
                    </span>
                </div>

                <p class="text-xs text-gray-700 leading-relaxed">
                    Foto bukti pembayaran telah berhasil dikirimkan ke meja kerja Admin. Admin sedang memeriksa mutasi rekening dan menerbitkan kuitansi resmi.
                </p>

                @if($order->payment_proof)
                    <div class="pt-2 border-t border-[#FDBA74]/50 flex items-center gap-3">
                        <img src="{{ asset('storage/' . $order->payment_proof) }}" 
                             alt="Bukti Transfer" 
                             @click="openLightbox('{{ asset('storage/' . $order->payment_proof) }}', 'Bukti Transfer - {{ $order->order_number }}')"
                             class="w-16 h-16 object-cover rounded-lg border border-gray-300 cursor-pointer shadow-xs hover:opacity-90 transition-opacity">
                        <div class="text-xs">
                            <span class="font-bold text-gray-900 block">Foto Bukti Terlampir</span>
                            <button type="button" 
                                    @click="openLightbox('{{ asset('storage/' . $order->payment_proof) }}', 'Bukti Transfer - {{ $order->order_number }}')"
                                    class="text-[#1E3E34] hover:underline font-semibold mt-0.5 inline-block cursor-pointer">
                                Lihat Resolusi Penuh &rarr;
                            </button>
                        </div>
                    </div>
                @endif

                <div class="pt-2 border-t border-[#FDBA74]/50">
                    <button type="button" @click="showForm = !showForm" class="text-xs font-semibold text-[#1E3E34] hover:underline cursor-pointer flex items-center gap-1">
                        <span x-text="showForm ? '- Sembunyikan Form Perubahan' : '+ Ganti / Perbarui Foto Bukti Pembayaran'"></span>
                    </button>
                </div>
            </div>

        @else
            <!-- KASUS 3: STATUS PEKERJAAN_SELESAI (BELUM ADA BUKTI FOTO DARI TEKNISI) -->
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span class="text-xs font-bold text-amber-900 uppercase tracking-wider">
                            Pekerjaan Selesai &bull; Belum Ada Bukti Pembayaran
                        </span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-amber-200 text-amber-900">
                        Nunggu Bukti Foto
                    </span>
                </div>
                <p class="text-xs text-amber-800 leading-relaxed">
                    Pekerjaan fisik telah selesai dan data servis telah dikunci. Jika pengurus DKM telah mentransfer atau menyerahkan pembayaran, silakan pilih metode dan unggah bukti pembayaran di bawah ini agar Admin dapat memverifikasi.
                </p>
            </div>
        @endif

        <!-- Formulir Upload Bukti Pembayaran -->
        @if(!$order->isPaid())
            <div x-show="showForm" class="space-y-4 pt-1">
                <form method="POST" action="{{ route('technician.order.payment_proof', $order->id) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <!-- Pilihan Metode Pembayaran -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-gray-800">
                            Metode Pembayaran yang Digunakan: <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="border rounded-lg p-3 text-center cursor-pointer text-xs font-medium transition-all"
                                   :class="paymentMethod === 'transfer' ? 'border-[#1E3E34] bg-[#EBF2EE] text-[#1E3E34] font-bold shadow-xs' : 'border-gray-200 hover:border-gray-300 text-gray-700 bg-white'">
                                <input type="radio" name="payment_method" value="transfer" x-model="paymentMethod" class="sr-only">
                                <span class="block">Transfer Bank</span>
                            </label>
                            <label class="border rounded-lg p-3 text-center cursor-pointer text-xs font-medium transition-all"
                                   :class="paymentMethod === 'qris' ? 'border-[#1E3E34] bg-[#EBF2EE] text-[#1E3E34] font-bold shadow-xs' : 'border-gray-200 hover:border-gray-300 text-gray-700 bg-white'">
                                <input type="radio" name="payment_method" value="qris" x-model="paymentMethod" class="sr-only">
                                <span class="block">QRIS</span>
                            </label>
                            <label class="border rounded-lg p-3 text-center cursor-pointer text-xs font-medium transition-all"
                                   :class="paymentMethod === 'cash' ? 'border-[#1E3E34] bg-[#EBF2EE] text-[#1E3E34] font-bold shadow-xs' : 'border-gray-200 hover:border-gray-300 text-gray-700 bg-white'">
                                <input type="radio" name="payment_method" value="cash" x-model="paymentMethod" class="sr-only">
                                <span class="block">Tunai (Cash)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Unggah Foto Bukti Transfer / Pembayaran -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-800">
                            Foto Bukti Transfer / Pembayaran:
                            <span class="text-gray-500 font-normal" x-text="paymentMethod === 'transfer' || paymentMethod === 'qris' ? '(Wajib dilampirkan)' : '(Opsional jika tunai)'"></span>
                        </label>

                        <!-- Preview Foto jika sudah dipilih -->
                        <template x-if="paymentProofPreview">
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <img :src="paymentProofPreview" alt="Pratinjau Bukti" class="w-16 h-16 object-cover rounded-lg border border-gray-300 shadow-xs">
                                    <div class="text-xs">
                                        <span class="font-semibold text-gray-900 block">Foto Bukti Terpilih</span>
                                        <span class="text-gray-500 text-[11px] block">Akan dikirim ke Admin untuk dicek</span>
                                    </div>
                                </div>
                                <button type="button" @click="clearPaymentProof()" class="px-2.5 py-1.5 rounded-md border border-red-200 text-red-600 hover:bg-red-50 text-xs font-medium cursor-pointer">
                                    Ganti Foto
                                </button>
                            </div>
                        </template>

                        <!-- Input File / Kamera -->
                        <div x-show="!paymentProofPreview" class="border-2 border-dashed border-gray-300 hover:border-[#1E3E34] rounded-xl p-4 text-center bg-gray-50/50 transition-colors">
                            <label class="cursor-pointer block space-y-2">
                                <div class="mx-auto w-10 h-10 rounded-full bg-[#EBF2EE] text-[#1E3E34] flex items-center justify-center font-bold text-xs uppercase tracking-wider">
                                    FOTO
                                </div>
                                <div class="text-xs text-gray-700">
                                    <span class="font-bold text-[#1E3E34]">Pilih dari Galeri</span> atau <span class="font-bold text-[#1E3E34]">Ambil Foto Kamera</span>
                                </div>
                                <span class="text-[11px] text-gray-500 block">Struk transfer ATM, screenshot m-banking, atau kuitansi serah terima fisik (Maks. 5 MB)</span>
                                <input type="file" 
                                       id="paymentProofInput"
                                       name="payment_proof" 
                                       accept="image/*" 
                                       @change="handlePaymentProofChange($event)"
                                       class="hidden">
                            </label>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full min-h-[48px] py-3.5 px-6 bg-[#1E3E34] hover:bg-[#142B24] text-white font-semibold text-sm rounded-lg shadow-xs transition-colors flex items-center justify-center cursor-pointer">
                        Kirim Bukti Pembayaran ke Admin
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- MODAL LIGHTBOX / PRATINJAU BESAR FOTO -->
    <div x-show="activeLightboxUrl" x-cloak class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4" @click.self="activeLightboxUrl = ''">
        <div class="bg-white rounded-xl border border-gray-200 max-w-lg w-full overflow-hidden shadow-2xl">
            <div class="p-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-800 truncate" x-text="lightboxCaption || 'Foto Bukti Pembayaran'"></span>
                <button type="button" @click="activeLightboxUrl = ''" class="text-gray-500 hover:text-gray-800 text-lg font-bold cursor-pointer">&times;</button>
            </div>
            <div class="p-2 bg-black flex items-center justify-center">
                <img :src="activeLightboxUrl" alt="Foto Penuh" class="max-h-[70vh] object-contain w-auto rounded">
            </div>
        </div>
    </div>

</div>

<script>
function paymentReport() {
    return {
        paymentMethod: '{{ $order->payment_method ?? "transfer" }}',
        paymentProofPreview: '{{ $order->payment_proof ? asset("storage/" . $order->payment_proof) : "" }}',
        showForm: {{ in_array($order->status, ['menunggu_verifikasi', 'selesai']) ? 'false' : 'true' }},
        activeLightboxUrl: '',
        lightboxCaption: '',

        handlePaymentProofChange(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.paymentProofPreview = URL.createObjectURL(file);
        },

        clearPaymentProof() {
            this.paymentProofPreview = '';
            const input = document.getElementById('paymentProofInput');
            if (input) input.value = '';
        },

        openLightbox(url, caption) {
            this.activeLightboxUrl = url;
            this.lightboxCaption = caption;
        }
    };
}
</script>
@endsection
