@extends('layouts.app', ['title' => 'Pelaporan Pembayaran - ' . $order->masjid->name])

@section('content')
<div class="max-w-2xl mx-auto space-y-5 pb-6"
     x-data="paymentReport()"
     @keydown.escape.window="activeLightboxUrl = ''">

    <!-- Navigasi Atas -->
    <div class="flex flex-wrap items-center justify-between gap-2">
        <a href="{{ route('technician.order.show', $order->id) }}"
           class="min-h-[44px] inline-flex items-center gap-1.5 text-base font-bold text-primary hover:text-primary-hover transition-colors">
            <span aria-hidden="true">&larr;</span>
            <span>Lembar Kerja (Arsip)</span>
        </a>
        <a href="{{ route('technician.dashboard') }}"
           class="min-h-[44px] inline-flex items-center text-base font-semibold text-text-muted hover:text-primary transition-colors">
            Beranda Tugas
        </a>
    </div>

    <!-- ========================================================================= -->
    <!-- KARTU GRAND TOTAL PALING ATAS (UNTUK DITUNJUKKAN LANGSUNG KE DKM)         -->
    <!-- ========================================================================= -->
    <section class="bg-surface rounded-2xl border-2 border-primary-border shadow-sm overflow-hidden">
        <!-- Blok Total Tagihan Kontras Tinggi -->
        <div class="bg-gradient-to-br from-primary to-primary-light text-white p-5 sm:p-6 space-y-3">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <span class="block text-sm font-mono font-semibold text-white/85">{{ $order->order_number }}</span>
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight mt-0.5">{{ $order->masjid->name }}</h1>
                </div>
                <span class="px-3 py-1.5 rounded-lg text-sm font-bold shrink-0
                    {{ $order->isPaid() ? 'bg-[#DCFCE7] text-[#15803D]' : ($order->status === 'menunggu_verifikasi' ? 'bg-[#E0F2FE] text-[#0369A1]' : 'bg-[#FEF3C7] text-[#92400E]') }}">
                    {{ $order->statusLabel() }}
                </span>
            </div>

            <div class="pt-3 border-t border-white/20 space-y-1">
                <span class="block text-sm font-bold uppercase tracking-wider text-white/85">
                    Total Tagihan Pelunasan
                </span>
                <div class="text-3xl sm:text-4xl font-extrabold font-mono tracking-tight text-white">
                    Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}
                </div>
                <p class="text-sm text-white/90">
                    Tunjukkan layar ini kepada pengurus DKM ({{ $order->masjid->pic_name }}) sebagai rincian tagihan resmi.
                </p>
            </div>
        </div>

        <!-- Rincian Tagihan & Info Masjid -->
        <div class="p-5 sm:p-6 space-y-4">
            <div class="p-3.5 rounded-xl bg-[#DCFCE7] border border-[#86EFAC] flex flex-wrap items-center justify-between gap-2 text-sm">
                <span class="text-[#14532D] font-bold">
                    &#10003; Pekerjaan fisik selesai &amp; lembar kerja telah dikunci.
                </span>
                <span class="px-2.5 py-0.5 rounded bg-[#15803D] text-white font-mono text-sm font-bold">TERKUNCI</span>
            </div>

            <div class="p-4 rounded-xl bg-bg-main border border-primary-border/70 space-y-2.5 text-sm sm:text-base">
                <span class="block text-sm font-extrabold text-primary uppercase tracking-wide">Rincian Biaya Servis</span>

                @if($order->serviceItems->isNotEmpty())
                    <div class="space-y-2 pb-2.5 border-b border-primary-border/60">
                        @foreach($order->serviceItems as $sItem)
                            <div class="flex items-start justify-between gap-3">
                                <span class="text-text-main">
                                    <strong>{{ $sItem->quantity }}x {{ $sItem->ac_type }}</strong> &mdash; {{ $sItem->service_category }}
                                    @if($sItem->room_name)
                                        <span class="text-text-muted block text-sm">Ruang: {{ $sItem->room_name }}</span>
                                    @endif
                                </span>
                                <span class="font-bold font-mono text-text-main whitespace-nowrap">
                                    Rp {{ number_format($sItem->subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-between gap-2 text-text-muted">
                    <span>Biaya Servis Dasar ({{ $order->ac_count }} Unit AC)</span>
                    <span class="font-mono font-semibold text-text-main whitespace-nowrap">Rp {{ number_format($order->base_amount, 0, ',', '.') }}</span>
                </div>

                @if($order->additional_amount > 0)
                    <div class="flex justify-between gap-2 text-text-main font-semibold">
                        <span>Tambahan Suku Cadang / Tindakan</span>
                        <span class="font-mono whitespace-nowrap">Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</span>
                    </div>
                    @if($order->additional_notes)
                        <p class="text-sm text-text-muted pl-3 border-l-2 border-primary-border">
                            Rincian: {{ $order->additional_notes }}
                        </p>
                    @endif
                @endif

                <div class="pt-2.5 border-t border-primary-border flex items-center justify-between font-extrabold text-base sm:text-lg text-text-main">
                    <span>Total Akhir</span>
                    <span class="text-xl font-mono text-primary">Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm pt-1">
                <div>
                    <span class="block text-text-muted">Pengurus (DKM / Marbot):</span>
                    <strong class="text-text-main font-bold">{{ $order->masjid->pic_name }}</strong>
                    <span class="font-mono text-primary block">{{ $order->masjid->pic_phone }}</span>
                </div>
                <div>
                    <span class="block text-text-muted">Alamat Masjid:</span>
                    <span class="text-text-main font-medium">{{ $order->masjid->address }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- BAGIAN PELAPORAN BUKTI PEMBAYARAN                                         -->
    <!-- ========================================================================= -->
    <section class="bg-surface rounded-2xl border-2 border-primary-border p-5 sm:p-6 shadow-sm space-y-5">
        <div class="flex items-center gap-3 pb-3 border-b border-primary-border/70">
            <span class="w-10 h-10 rounded-xl {{ $order->isPaid() ? 'bg-[#15803D]' : ($order->status === 'menunggu_verifikasi' ? 'bg-[#0369A1]' : 'bg-primary') }} text-white flex items-center justify-center font-extrabold text-lg shrink-0">
                @if($order->isPaid()) &#10003; @else Rp @endif
            </span>
            <div>
                <h2 class="text-xl font-extrabold text-text-main leading-tight">Pelaporan Bukti Pembayaran</h2>
                <p class="text-sm text-text-muted mt-0.5">Kirim metode dan foto bukti pembayaran dari DKM ke Admin</p>
            </div>
        </div>

        @if($order->isPaid())
            <!-- KASUS 1: SUDAH DIVERIFIKASI LUNAS ADMIN -->
            <div class="p-5 rounded-2xl bg-[#DCFCE7] border-2 border-[#86EFAC] space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-base sm:text-lg font-extrabold text-[#14532D]">
                        &#10003; Pembayaran Diverifikasi LUNAS
                    </span>
                    <span class="px-3 py-1 rounded-lg text-sm font-bold bg-[#15803D] text-white">
                        {{ $order->paymentMethodLabel() }}
                    </span>
                </div>
                <p class="text-sm sm:text-base text-[#14532D] leading-relaxed">
                    Pelunasan telah diverifikasi oleh Admin pada <strong>{{ $order->paid_at ? $order->paid_at->translatedFormat('d F Y, H:i') : '-' }} WIB</strong>. Kuitansi resmi siap dibuka dan dicetak.
                </p>
                <a href="{{ route('order.public_receipt', ['order_number' => $order->order_number]) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="w-full min-h-[54px] px-5 py-3.5 rounded-xl bg-primary hover:bg-primary-hover text-white border-2 border-accent-gold font-extrabold text-base sm:text-lg transition-colors flex items-center justify-center gap-2 shadow-xs">
                    <span>Lihat Kuitansi Resmi</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

        @elseif($order->status === 'menunggu_verifikasi' || $order->payment_proof)
            <!-- KASUS 2: BUKTI SUDAH DIUNGGAH, MENUNGGU VERIFIKASI ADMIN -->
            <div class="p-5 rounded-2xl bg-[#E0F2FE] border-2 border-[#BAE6FD] space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-base font-extrabold text-[#0C4A6E]">
                        Menunggu Verifikasi Admin
                    </span>
                    <span class="px-3 py-1 rounded-lg text-sm font-bold bg-surface text-[#0369A1] border border-[#BAE6FD]">
                        {{ $order->paymentMethodLabel() }}
                    </span>
                </div>

                <p class="text-sm sm:text-base text-[#0C4A6E] leading-relaxed">
                    Laporan bukti pembayaran sudah masuk ke meja kerja Admin untuk dicek mutasi dan diterbitkan kuitansi resminya.
                </p>

                @if($order->payment_proof)
                    <div class="p-3.5 rounded-xl bg-surface border border-[#BAE6FD] flex items-center gap-3.5">
                        <img src="{{ asset('storage/' . $order->payment_proof) }}"
                             alt="Bukti Pembayaran"
                             @click="openLightbox('{{ asset('storage/' . $order->payment_proof) }}', 'Bukti Pembayaran - {{ $order->order_number }}')"
                             class="w-20 h-20 object-cover rounded-xl border border-primary-border cursor-pointer hover:opacity-90 transition-opacity shrink-0">
                        <div class="space-y-1">
                            <span class="font-bold text-base text-text-main block">Foto Bukti Terlampir</span>
                            <button type="button"
                                    @click="openLightbox('{{ asset('storage/' . $order->payment_proof) }}', 'Bukti Pembayaran - {{ $order->order_number }}')"
                                    class="min-h-[40px] inline-flex items-center text-sm font-bold text-primary hover:underline cursor-pointer">
                                Lihat Resolusi Penuh &rarr;
                            </button>
                        </div>
                    </div>
                @endif

                <div class="pt-1">
                    <button type="button"
                            @click="showForm = !showForm"
                            class="min-h-[48px] px-4 py-2.5 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-base cursor-pointer transition-colors">
                        <span x-text="showForm ? 'Sembunyikan Form Perubahan' : 'Ganti / Perbarui Bukti Pembayaran'"></span>
                    </button>
                </div>
            </div>

        @else
            <!-- KASUS 3: BELUM ADA BUKTI PEMBAYARAN -->
            <div class="p-4 sm:p-5 rounded-2xl bg-[#FEF3C7] border-2 border-[#FCD34D] space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <strong class="text-base font-extrabold text-[#78350F]">Belum Ada Bukti Pembayaran</strong>
                    <span class="px-3 py-1 rounded-lg text-sm font-bold bg-surface text-[#92400E] border border-[#FCD34D]">Menunggu Laporan</span>
                </div>
                <p class="text-sm sm:text-base text-[#78350F] leading-relaxed">
                    Jika pengurus DKM sudah mentransfer atau menyerahkan pembayaran, pilih metode bayar dan unggah bukti di bawah agar Admin segera memverifikasi.
                </p>
            </div>
        @endif

        <!-- Formulir Upload Bukti Pembayaran -->
        @if(!$order->isPaid())
            <div x-show="showForm" class="space-y-5 pt-2">
                <form method="POST"
                      action="{{ route('technician.order.payment_proof', $order->id) }}"
                      enctype="multipart/form-data"
                      class="space-y-5">
                    @csrf

                    <!-- Pilihan 3 Metode Pembayaran (Kartu Radio Besar >= 64px) -->
                    <div class="space-y-2.5">
                        <span class="block text-base font-bold text-text-main">
                            Metode Pembayaran yang Digunakan <span class="text-[#B91C1C]">*</span>
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="min-h-[68px] border-2 rounded-2xl p-4 cursor-pointer transition-colors flex sm:flex-col items-center sm:items-start gap-3 sm:gap-2"
                                   :class="paymentMethod === 'transfer' ? 'border-primary bg-primary-subtle text-primary ring-1 ring-primary' : 'border-primary-border bg-surface hover:bg-primary-subtle/40 text-text-main'">
                                <input type="radio" name="payment_method" value="transfer" x-model="paymentMethod" class="sr-only">
                                <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                      :class="paymentMethod === 'transfer' ? 'bg-primary text-white' : 'bg-bg-main text-primary border border-primary-border'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                    </svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-base font-extrabold leading-tight">Transfer Bank</span>
                                    <span class="block text-sm text-text-muted mt-0.5">Struk ATM / m-banking</span>
                                </span>
                            </label>

                            <label class="min-h-[68px] border-2 rounded-2xl p-4 cursor-pointer transition-colors flex sm:flex-col items-center sm:items-start gap-3 sm:gap-2"
                                   :class="paymentMethod === 'qris' ? 'border-primary bg-primary-subtle text-primary ring-1 ring-primary' : 'border-primary-border bg-surface hover:bg-primary-subtle/40 text-text-main'">
                                <input type="radio" name="payment_method" value="qris" x-model="paymentMethod" class="sr-only">
                                <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                      :class="paymentMethod === 'qris' ? 'bg-primary text-white' : 'bg-bg-main text-primary border border-primary-border'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 14.625h1.875m2.625 0h1.875m-6.375 3h1.875m2.625 0h1.875m-1.875-3v3m1.875 0v1.875" />
                                    </svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-base font-extrabold leading-tight">QRIS</span>
                                    <span class="block text-sm text-text-muted mt-0.5">Screenshot bayar QRIS</span>
                                </span>
                            </label>

                            <label class="min-h-[68px] border-2 rounded-2xl p-4 cursor-pointer transition-colors flex sm:flex-col items-center sm:items-start gap-3 sm:gap-2"
                                   :class="paymentMethod === 'cash' ? 'border-primary bg-primary-subtle text-primary ring-1 ring-primary' : 'border-primary-border bg-surface hover:bg-primary-subtle/40 text-text-main'">
                                <input type="radio" name="payment_method" value="cash" x-model="paymentMethod" class="sr-only">
                                <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                      :class="paymentMethod === 'cash' ? 'bg-primary text-white' : 'bg-bg-main text-primary border border-primary-border'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                    </svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-base font-extrabold leading-tight">Tunai (Cash)</span>
                                    <span class="block text-sm text-text-muted mt-0.5">Diterima langsung</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Area Unggah Foto Bukti Pembayaran -->
                    <div class="space-y-2.5">
                        <span class="block text-base font-bold text-text-main">
                            Foto Bukti Pembayaran
                            <span class="text-sm font-semibold text-text-muted block sm:inline sm:ml-1"
                                  x-text="paymentMethod === 'transfer' || paymentMethod === 'qris' ? '(Disarankan melampirkan bukti transfer/QRIS)' : '(Opsional jika pembayaran tunai)'"></span>
                        </span>

                        <!-- Preview Besar + Lightbox + Tombol Hapus Pilihan -->
                        <div x-show="paymentProofPreview" x-cloak class="p-4 bg-bg-main rounded-2xl border-2 border-primary-border space-y-3">
                            <div class="rounded-xl overflow-hidden bg-black flex items-center justify-center">
                                <img :src="paymentProofPreview"
                                     alt="Pratinjau Bukti Pembayaran"
                                     @click="openLightbox(paymentProofPreview, 'Pratinjau Bukti Pembayaran')"
                                     class="max-h-64 w-auto object-contain cursor-pointer hover:opacity-95 transition-opacity">
                            </div>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <button type="button"
                                        @click="openLightbox(paymentProofPreview, 'Pratinjau Bukti Pembayaran')"
                                        class="min-h-[44px] px-4 py-2 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-sm cursor-pointer">
                                    Perbesar Foto
                                </button>
                                <button type="button"
                                        @click="clearPaymentProof()"
                                        class="min-h-[44px] px-4 py-2 rounded-xl border border-[#FCA5A5] bg-surface text-[#B91C1C] hover:bg-[#FEE2E2] text-sm font-bold cursor-pointer">
                                    Hapus / Ganti Foto
                                </button>
                            </div>
                        </div>

                        <!-- Dropzone / Input File (WAJIB: id="paymentProofInput" name="payment_proof") -->
                        <div x-show="!paymentProofPreview" class="border-2 border-dashed border-primary/60 hover:bg-primary-subtle/70 rounded-2xl p-6 text-center bg-primary-subtle/40 transition-colors">
                            <label for="paymentProofInput" class="cursor-pointer block space-y-2.5">
                                <span class="mx-auto w-14 h-14 rounded-2xl bg-primary text-white flex items-center justify-center shadow-xs">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                    </svg>
                                </span>
                                <span class="block text-base sm:text-lg font-extrabold text-primary">
                                    Pilih dari Galeri atau Ambil Foto Kamera
                                </span>
                                <span class="block text-sm text-text-muted">
                                    Struk ATM, screenshot m-banking, bukti QRIS, atau nota serah terima (Maks. 5 MB)
                                </span>
                            </label>
                        </div>

                        <!-- Input file tetap berada di DOM (tidak di dalam x-if) agar file terkirim -->
                        <input type="file"
                               id="paymentProofInput"
                               name="payment_proof"
                               accept="image/*"
                               @change="handlePaymentProofChange($event)"
                               class="hidden">
                    </div>

                    <button type="submit"
                            class="w-full min-h-[54px] py-3.5 px-6 bg-primary hover:bg-primary-hover text-white font-extrabold text-base sm:text-lg rounded-xl shadow-xs transition-colors flex items-center justify-center cursor-pointer">
                        Kirim Bukti Pembayaran ke Admin
                    </button>
                </form>
            </div>
        @endif
    </section>

    <!-- MODAL LIGHTBOX PRATINJAU BESAR FOTO -->
    <div x-show="activeLightboxUrl" x-cloak role="dialog" aria-modal="true" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4" @click.self="activeLightboxUrl = ''">
        <div class="bg-surface rounded-2xl border border-primary-border max-w-xl w-full overflow-hidden shadow-2xl">
            <div class="px-4 py-3 bg-bg-main border-b border-primary-border flex items-center justify-between gap-3">
                <span class="text-base font-bold text-text-main truncate" x-text="lightboxCaption || 'Foto Bukti Pembayaran'"></span>
                <button type="button" @click="activeLightboxUrl = ''" aria-label="Tutup foto" class="tap-target inline-flex items-center justify-center rounded-xl bg-surface border border-primary-border text-text-main text-2xl font-bold cursor-pointer shrink-0">&times;</button>
            </div>
            <div class="p-3 bg-black flex items-center justify-center">
                <img :src="activeLightboxUrl" alt="Foto Penuh" class="max-h-[75vh] object-contain w-auto rounded-lg">
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
