@extends('layouts.app', ['title' => 'Pesanan Servis AC - ' . $order->order_number])

@section('content')
@php
    $isCancelled = $order->status === 'batal';
    $isPaid = $order->isPaid();

    if ($isPaid || $order->status === 'selesai') {
        $progressStage = 4;
    } elseif (in_array($order->status, ['dikerjakan', 'pekerjaan_selesai', 'menunggu_bayar', 'menunggu_verifikasi'], true)) {
        $progressStage = 3;
    } elseif ($order->status === 'dijadwalkan') {
        $progressStage = 2;
    } else {
        $progressStage = 1;
    }

    $progressStages = [
        [
            'step' => 1,
            'label' => 'Pesanan Masuk',
            'desc' => 'Pesanan telah diterima di meja admin.',
        ],
        [
            'step' => 2,
            'label' => 'Teknisi Dijadwalkan',
            'desc' => 'Admin menetapkan jadwal & teknisi lapangan.',
        ],
        [
            'step' => 3,
            'label' => 'Sedang Dikerjakan',
            'desc' => 'Pengerjaan servis di masjid & pelaporan pembayaran.',
        ],
        [
            'step' => 4,
            'label' => 'Selesai & Lunas',
            'desc' => 'Pembayaran terverifikasi & kuitansi resmi terbit.',
        ],
    ];
@endphp

<div class="max-w-3xl mx-auto space-y-5 pb-6"
     x-data="{
        copied: false,
        lightboxUrl: '',
        lightboxCaption: '',
        lightboxType: '',

        copyOrderNumber(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }).catch(() => this.fallbackCopy(text));
            } else {
                this.fallbackCopy(text);
            }
        },

        fallbackCopy(text) {
            const el = document.createElement('textarea');
            el.value = text;
            el.setAttribute('readonly', '');
            el.style.position = 'absolute';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            try {
                document.execCommand('copy');
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            } catch (e) {}
            document.body.removeChild(el);
        },

        openLightbox(url, caption, type) {
            this.lightboxUrl = url;
            this.lightboxCaption = caption;
            this.lightboxType = type;
        },

        closeLightbox() {
            this.lightboxUrl = '';
            this.lightboxCaption = '';
            this.lightboxType = '';
        }
     }"
     @keydown.escape.window="closeLightbox()">

    <!-- Navigasi Atas -->
    <div class="flex flex-wrap items-center justify-between gap-2">
        <a href="{{ route('order.create') }}"
           class="min-h-[44px] inline-flex items-center gap-1.5 text-base font-bold text-primary hover:text-primary-hover transition-colors">
            <span aria-hidden="true">&larr;</span>
            <span>Formulir Pemesanan</span>
        </a>
        <span class="text-sm font-medium text-text-muted">
            Diperbarui: {{ $order->updated_at->translatedFormat('d M Y, H:i') }} WIB
        </span>
    </div>

    <!-- ========================================================================= -->
    <!-- KARTU NOMOR REGISTRASI + PROGRESS BAR 4 TAHAP                             -->
    <!-- ========================================================================= -->
    <section class="bg-surface rounded-2xl border-2 border-primary-border p-5 sm:p-7 shadow-sm space-y-6">

        <!-- Header Konfirmasi -->
        <div class="space-y-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-sm font-bold border
                    @if($order->status === 'selesai') bg-[#DCFCE7] text-[#15803D] border-[#86EFAC]
                    @elseif($order->status === 'menunggu_verifikasi') bg-[#E0F2FE] text-[#0369A1] border-[#BAE6FD]
                    @elseif($order->status === 'pekerjaan_selesai') bg-[#FEF3C7] text-[#92400E] border-[#FCD34D]
                    @elseif($order->status === 'menunggu_bayar') bg-[#FFEDD5] text-[#C2410C] border-[#FDBA74]
                    @elseif($order->status === 'dikerjakan' || $order->status === 'dijadwalkan') bg-[#E0F2FE] text-[#0369A1] border-[#BAE6FD]
                    @elseif($order->status === 'batal') bg-[#FEE2E2] text-[#B91C1C] border-[#FCA5A5]
                    @else bg-[#FEF3C7] text-[#92400E] border-[#FCD34D]
                    @endif">
                    Status: {{ $order->statusLabel() }}
                </span>

                @if(!$isCancelled)
                    <span class="text-sm font-semibold text-primary">
                        Tahap {{ $progressStage }} dari 4
                    </span>
                @endif
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-text-main tracking-tight">
                @if($isCancelled)
                    Pesanan Servis Dibatalkan
                @elseif($isPaid)
                    Servis Selesai &amp; Pembayaran Lunas
                @else
                    Pesanan Anda Berhasil Dikirim
                @endif
            </h1>
            <p class="text-base text-text-muted leading-relaxed">
                Simpan nomor registrasi di bawah ini untuk memantau jadwal teknisi dan bukti pelunasan kapan saja.
            </p>
        </div>

        <!-- Box Nomor Registrasi + Tombol Salin Nomor -->
        <div class="p-4 sm:p-5 rounded-2xl bg-primary-subtle border-2 border-primary-border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="min-w-0">
                <span class="block text-sm font-bold text-text-muted uppercase tracking-wide">
                    Nomor Registrasi Pesanan
                </span>
                <span class="block text-2xl sm:text-3xl font-extrabold text-primary font-mono tracking-tight mt-1 break-all">
                    {{ $order->order_number }}
                </span>
            </div>

            <button type="button"
                    @click="copyOrderNumber('{{ $order->order_number }}')"
                    :class="copied ? 'bg-[#15803D] text-white border-[#15803D]' : 'bg-surface hover:bg-primary hover:text-white text-primary border-primary'"
                    class="min-h-[48px] px-5 py-2.5 rounded-xl border-2 font-bold text-base inline-flex items-center justify-center gap-2 transition-colors cursor-pointer shrink-0 shadow-xs">
                <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                </svg>
                <svg x-show="copied" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <span x-text="copied ? 'Tersalin!' : 'Salin Nomor'"></span>
            </button>
        </div>

        <!-- Visual Progress Stepper 4 Tahap (atau Banner Pembatalan) -->
        @if($isCancelled)
            <div class="p-5 rounded-2xl bg-[#FEE2E2] border-2 border-[#FCA5A5] text-[#7F1D1D] flex items-start gap-3.5">
                <svg class="w-7 h-7 shrink-0 text-[#B91C1C] mt-0.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <div class="space-y-1">
                    <h2 class="text-lg font-extrabold">Pesanan Ini Telah Dibatalkan</h2>
                    <p class="text-base leading-relaxed">
                        Permohonan servis ini tidak lagi diproses. Jika Bapak/Ibu ingin menjadwalkan ulang, silakan buat pesanan baru atau hubungi Admin melalui WhatsApp di bawah.
                    </p>
                </div>
            </div>
        @else
            <div aria-label="Progres Pengerjaan Servis" class="space-y-3">
                <h2 class="text-base font-bold text-text-main">Progres Layanan Servis</h2>

                <!-- Tampilan Mobile: Vertikal Lega & Mudah Dibaca -->
                <ol class="sm:hidden space-y-3">
                    @foreach($progressStages as $stage)
                        @php
                            $isDone = $progressStage > $stage['step'] || ($stage['step'] === 4 && $progressStage === 4);
                            $isCurrent = $progressStage === $stage['step'] && !($stage['step'] === 4 && $progressStage === 4);
                        @endphp
                        <li class="flex items-start gap-3.5 p-3.5 rounded-xl border-2 {{ $isCurrent ? 'bg-primary-subtle border-primary' : ($isDone ? 'bg-[#DCFCE7]/50 border-[#86EFAC]' : 'bg-bg-main border-gray-200') }}">
                            <span class="w-9 h-9 rounded-full flex items-center justify-center font-extrabold text-base shrink-0 {{ $isDone ? 'bg-[#15803D] text-white' : ($isCurrent ? 'bg-primary text-white' : 'bg-surface text-text-muted border-2 border-gray-300') }}">
                                @if($isDone)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                @else
                                    {{ $stage['step'] }}
                                @endif
                            </span>
                            <div class="min-w-0 pt-0.5">
                                <span class="block text-base font-bold {{ $isCurrent ? 'text-primary' : ($isDone ? 'text-[#15803D]' : 'text-text-muted') }}">
                                    {{ $stage['label'] }}
                                    @if($isCurrent)
                                        <span class="ml-1.5 inline-block px-2 py-0.5 rounded bg-primary text-white text-sm font-bold align-middle">Saat Ini</span>
                                    @endif
                                </span>
                                @if($isCurrent || $isDone)
                                    <span class="block text-sm text-text-muted mt-0.5">{{ $stage['desc'] }}</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>

                <!-- Tampilan Desktop: Horizontal 4 Tahap -->
                <ol class="hidden sm:grid sm:grid-cols-4 gap-3 relative">
                    @foreach($progressStages as $stage)
                        @php
                            $isDone = $progressStage > $stage['step'] || ($stage['step'] === 4 && $progressStage === 4);
                            $isCurrent = $progressStage === $stage['step'] && !($stage['step'] === 4 && $progressStage === 4);
                        @endphp
                        <li class="p-3.5 rounded-2xl border-2 flex flex-col items-center text-center gap-2 {{ $isCurrent ? 'bg-primary-subtle border-primary shadow-xs' : ($isDone ? 'bg-[#DCFCE7]/50 border-[#86EFAC]' : 'bg-bg-main border-gray-200') }}">
                            <span class="w-10 h-10 rounded-full flex items-center justify-center font-extrabold text-base {{ $isDone ? 'bg-[#15803D] text-white' : ($isCurrent ? 'bg-primary text-white' : 'bg-surface text-text-muted border-2 border-gray-300') }}">
                                @if($isDone)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                @else
                                    {{ $stage['step'] }}
                                @endif
                            </span>
                            <span class="text-sm font-bold leading-snug {{ $isCurrent ? 'text-primary' : ($isDone ? 'text-[#15803D]' : 'text-text-muted') }}">
                                {{ $stage['label'] }}
                            </span>
                            @if($isCurrent)
                                <span class="px-2.5 py-0.5 rounded-full bg-primary text-white text-sm font-bold">Saat Ini</span>
                            @elseif($isDone)
                                <span class="text-sm font-semibold text-[#15803D]">Selesai</span>
                            @else
                                <span class="text-sm text-text-muted">Berikutnya</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif

        <!-- ========================================================================= -->
        <!-- STATUS PEMBAYARAN (BELUM DIBAYAR VS SUDAH DIBAYAR)                        -->
        <!-- ========================================================================= -->
        @if($isPaid)
            <div class="p-5 rounded-2xl bg-[#DCFCE7] border-2 border-[#86EFAC] space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <span class="w-10 h-10 rounded-full bg-[#15803D] text-white flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </span>
                        <div>
                            <span class="block text-sm font-bold uppercase tracking-wide text-[#15803D]">Status Pembayaran</span>
                            <h2 class="text-xl font-extrabold text-[#14532D] mt-0.5">SUDAH DIBAYAR (LUNAS)</h2>
                        </div>
                    </div>
                    <span class="px-3.5 py-1.5 rounded-lg bg-[#15803D] text-white font-bold text-sm self-start sm:self-center">
                        Terverifikasi Admin
                    </span>
                </div>

                <div class="text-sm sm:text-base text-[#14532D] pt-3 border-t border-[#86EFAC] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <span>Waktu Pelunasan: <strong>{{ $order->paid_at ? $order->paid_at->translatedFormat('d F Y, H:i') : '-' }} WIB</strong></span>
                    <span>Metode: <strong>{{ $order->paymentMethodLabel() }}</strong></span>
                </div>

                <!-- Tombol Besar Kuitansi Resmi (Hijau + Aksen Emas Elegan) -->
                <a href="{{ route('order.public_receipt', ['order_number' => $order->order_number]) }}"
                   class="w-full min-h-[56px] px-6 py-3.5 rounded-xl bg-primary hover:bg-primary-hover text-white border-2 border-accent-gold font-extrabold text-base sm:text-lg transition-colors flex flex-wrap items-center justify-center gap-2.5 shadow-sm">
                    <svg class="w-6 h-6 text-accent-gold shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                    </svg>
                    <span>Lihat Kuitansi Resmi</span>
                    <span class="px-2.5 py-0.5 rounded-md bg-accent-gold text-white text-sm font-bold">Siap Cetak</span>
                </a>
            </div>
        @elseif(!$isCancelled)
            <div class="p-5 rounded-2xl {{ $order->status === 'menunggu_verifikasi' ? 'bg-[#E0F2FE] border-2 border-[#BAE6FD]' : 'bg-[#FEF3C7] border-2 border-[#FCD34D]' }} space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <span class="w-10 h-10 rounded-full {{ $order->status === 'menunggu_verifikasi' ? 'bg-[#0369A1]' : 'bg-[#92400E]' }} text-white flex items-center justify-center font-extrabold text-lg shrink-0">
                            !
                        </span>
                        <div>
                            <span class="block text-sm font-bold uppercase tracking-wide {{ $order->status === 'menunggu_verifikasi' ? 'text-[#0369A1]' : 'text-[#92400E]' }}">
                                Status Pembayaran
                            </span>
                            <h2 class="text-xl font-extrabold {{ $order->status === 'menunggu_verifikasi' ? 'text-[#0C4A6E]' : 'text-[#78350F]' }} mt-0.5">
                                BELUM DIBAYAR
                            </h2>
                        </div>
                    </div>

                    <span class="px-3.5 py-1.5 rounded-lg text-sm font-bold self-start sm:self-center
                        @if($order->status === 'menunggu_verifikasi') bg-white text-[#0369A1] border border-[#BAE6FD]
                        @elseif($order->status === 'pekerjaan_selesai') bg-[#FFEDD5] text-[#C2410C] border border-[#FDBA74]
                        @else bg-white text-[#92400E] border border-[#FCD34D]
                        @endif">
                        @if($order->status === 'menunggu_verifikasi')
                            Menunggu Verifikasi Admin
                        @elseif($order->status === 'pekerjaan_selesai')
                            Menunggu Pelaporan Teknisi
                        @else
                            Menunggu Verifikasi Admin
                        @endif
                    </span>
                </div>

                @if($order->status === 'menunggu_verifikasi' || ($order->status === 'pekerjaan_selesai' && $order->payment_proof))
                    <div class="pt-3 border-t border-[#BAE6FD] text-sm sm:text-base text-[#0C4A6E] space-y-1">
                        <strong class="block font-bold">Bukti Pembayaran Telah Dikirim Teknisi</strong>
                        <p class="leading-relaxed">
                            Foto bukti pembayaran ({{ $order->paymentMethodLabel() }}) sudah masuk ke sistem dan saat ini sedang diperiksa oleh Admin untuk menerbitkan kuitansi resmi.
                        </p>
                    </div>
                @elseif($order->status === 'pekerjaan_selesai')
                    <div class="pt-3 border-t border-[#FCD34D] text-sm sm:text-base text-[#78350F] space-y-1">
                        <strong class="block font-bold">Pekerjaan Servis Fisik Sudah Selesai</strong>
                        <p class="leading-relaxed">
                            Teknisi telah merampungkan servis AC di lokasi masjid. Saat ini menunggu teknisi melaporkan bukti pembayaran dari pengurus DKM untuk diverifikasi Admin.
                        </p>
                    </div>
                @elseif($order->status === 'menunggu_bayar')
                    <div class="pt-3 border-t border-[#FCD34D] text-sm sm:text-base text-[#78350F] space-y-1">
                        <strong class="block font-bold">Menunggu Pelunasan Pembayaran</strong>
                        <p class="leading-relaxed">
                            Pengerjaan AC telah selesai. Silakan selesaikan pembayaran sesuai total tagihan di bawah ini melalui teknisi atau transfer yang diverifikasi Admin.
                        </p>
                    </div>
                @else
                    <p class="text-sm sm:text-base text-[#78350F] leading-relaxed pt-3 border-t border-[#FCD34D]">
                        Pembayaran dilakukan setelah teknisi selesai mengerjakan servis AC di lokasi masjid, baik secara tunai maupun transfer/QRIS yang diverifikasi Admin.
                    </p>
                @endif
            </div>
        @endif

        <!-- ========================================================================= -->
        <!-- CTA UTAMA KONFIRMASI WHATSAPP ADMIN                                       -->
        <!-- ========================================================================= -->
        <div class="pt-2 space-y-2.5">
            <a href="{{ $whatsappUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               class="w-full min-h-[56px] px-6 py-4 rounded-xl bg-primary hover:bg-primary-hover text-white font-extrabold text-base sm:text-lg transition-colors flex items-center justify-center gap-3 shadow-sm">
                <svg class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12.031 2C6.51 2 2.02 6.489 2.02 12.011c0 1.763.461 3.485 1.337 5.002L2 22l5.118-1.342a9.97 9.97 0 0 0 4.913 1.296h.004c5.52 0 10.011-4.489 10.011-10.011C22.046 6.489 17.555 2 12.031 2Zm5.829 14.234c-.245.691-1.425 1.321-1.976 1.404-.512.077-1.178.109-1.901-.121-.438-.139-1.002-.326-1.724-.639-3.035-1.31-5.016-4.377-5.168-4.579-.151-.202-1.233-1.641-1.233-3.131 0-1.49.779-2.222 1.055-2.525.276-.303.603-.379.804-.379.201 0 .402.002.578.011.186.009.435-.071.681.519.251.605.854 2.084.929 2.235.075.151.126.328.025.53-.101.202-.151.328-.301.505-.151.177-.317.395-.452.53-.151.151-.308.315-.132.618.176.303.782 1.29 1.679 2.089 1.153 1.028 2.126 1.347 2.428 1.498.302.151.478.126.654-.076.176-.202.754-.883.955-1.186.201-.303.402-.252.678-.151.276.101 1.759.83 2.061.981.302.151.503.227.578.353.076.126.076.731-.169 1.422Z" />
                </svg>
                <span>Konfirmasi Pesanan via WhatsApp Admin</span>
            </a>
            <p class="text-center text-sm text-text-muted leading-relaxed">
                Tekan tombol di atas setelah memesan untuk mengirim rincian ke WhatsApp Admin (<strong class="font-mono text-text-main">{{ $adminPhone }}</strong>) agar jadwal teknisi segera dikonfirmasi.
            </p>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- INFORMASI MASJID & PENUGASAN SERVIS                                       -->
    <!-- ========================================================================= -->
    <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-surface rounded-2xl border border-primary-border p-5 shadow-sm space-y-2.5">
            <span class="block text-sm font-bold text-primary uppercase tracking-wide">Lokasi Masjid / Musholla</span>
            <h2 class="text-lg font-extrabold text-text-main">{{ $order->masjid->name }}</h2>
            <p class="text-base text-text-muted leading-relaxed">{{ $order->masjid->address }}</p>
            <div class="pt-3 border-t border-primary-border/70 text-sm sm:text-base space-y-0.5">
                <span class="block text-text-muted">Pengurus (DKM / Marbot): <strong class="text-text-main">{{ $order->masjid->pic_name }}</strong></span>
                <span class="block font-mono font-semibold text-primary">{{ $order->masjid->pic_phone }}</span>
            </div>
        </div>

        <div class="bg-surface rounded-2xl border border-primary-border p-5 shadow-sm flex flex-col justify-between gap-3">
            <div class="space-y-3">
                <span class="block text-sm font-bold text-primary uppercase tracking-wide">Jadwal &amp; Teknisi</span>
                <div>
                    <span class="block text-sm text-text-muted">Jadwal Kunjungan:</span>
                    <strong class="block text-base font-bold text-text-main mt-0.5">
                        {{ $order->service_date ? $order->service_date->translatedFormat('l, d F Y') : 'Menunggu Konfirmasi' }}
                    </strong>
                    @if($order->service_time)
                        <span class="block text-sm font-semibold text-primary">{{ $order->service_time }}</span>
                    @endif
                </div>
                <div>
                    <span class="block text-sm text-text-muted">Teknisi Bertugas:</span>
                    <strong class="block text-base font-bold text-text-main mt-0.5">
                        {{ $order->technician->name ?? 'Belum Ditugaskan (Menunggu Admin)' }}
                    </strong>
                </div>
            </div>

            <div class="pt-3 border-t border-primary-border/70 text-sm">
                <span class="text-text-muted">Ringkasan Keluhan: <strong class="text-text-main">{{ $order->dkm_complaint }}</strong></span>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- RINCIAN BIAYA SERVIS (KARTU DI HP, TABEL BERSIH DI DESKTOP)               -->
    <!-- ========================================================================= -->
    <section class="bg-surface rounded-2xl border border-primary-border p-5 sm:p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between gap-2 pb-3 border-b border-primary-border/70">
            <h2 class="text-lg sm:text-xl font-extrabold text-text-main">Rincian Biaya Servis</h2>
            <span class="px-3 py-1 rounded-lg bg-primary-subtle text-primary text-sm font-bold">
                {{ $order->ac_count }} Unit AC
            </span>
        </div>

        <!-- Tampilan Mobile: Baris Kartu Lega (Tanpa Tabel Sempit) -->
        <div class="md:hidden divide-y divide-primary-border/70 border border-primary-border rounded-xl overflow-hidden bg-bg-main/40">
            @if($order->serviceItems->isNotEmpty())
                @foreach($order->serviceItems as $sItem)
                    <div class="p-4 space-y-1.5 bg-surface">
                        <div class="text-base font-bold text-text-main">
                            {{ $sItem->service_category }} ({{ $sItem->ac_type }})
                        </div>
                        @if($sItem->room_name)
                            <div class="text-sm text-text-muted">Ruangan: {{ $sItem->room_name }}</div>
                        @endif
                        <div class="flex items-center justify-between gap-2 pt-1 text-sm">
                            <span class="text-text-muted font-mono">
                                {{ $sItem->quantity }} Unit &times; Rp {{ number_format($sItem->price_per_unit, 0, ',', '.') }}
                            </span>
                            <span class="text-base font-extrabold text-text-main font-mono">
                                Rp {{ number_format($sItem->subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="p-4 space-y-1.5 bg-surface">
                    <div class="text-base font-bold text-text-main">{{ $order->dkm_complaint }}</div>
                    <div class="text-sm text-text-muted">Jasa perawatan pendingin ruang ibadah</div>
                    <div class="flex items-center justify-between gap-2 pt-1 text-sm">
                        <span class="text-text-muted font-semibold">{{ $order->ac_count }} Unit</span>
                        <span class="text-base font-extrabold text-text-main font-mono">
                            Rp {{ number_format($order->base_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @endif

            @if($order->spareparts->isNotEmpty())
                @foreach($order->spareparts as $item)
                    <div class="p-4 space-y-1.5 bg-primary-subtle/30">
                        <span class="inline-block px-2 py-0.5 rounded bg-primary-subtle text-primary text-sm font-bold">Suku Cadang</span>
                        <div class="text-base font-bold text-text-main">{{ $item->item_name }}</div>
                        <div class="flex items-center justify-between gap-2 pt-1 text-sm">
                            <span class="text-text-muted font-mono">
                                {{ $item->quantity }}x &times; Rp {{ number_format($item->price, 0, ',', '.') }}
                            </span>
                            <span class="text-base font-extrabold text-text-main font-mono">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            @elseif($order->additional_amount > 0)
                <div class="p-4 space-y-1.5 bg-primary-subtle/30">
                    <span class="inline-block px-2 py-0.5 rounded bg-primary-subtle text-primary text-sm font-bold">Tambahan</span>
                    <div class="text-base font-bold text-text-main">Tambahan Suku Cadang / Freon</div>
                    @if($order->additional_notes)
                        <div class="text-sm text-text-muted">{{ $order->additional_notes }}</div>
                    @endif
                    <div class="flex items-center justify-between gap-2 pt-1 text-sm">
                        <span class="text-text-muted font-semibold">1 Paket</span>
                        <span class="text-base font-extrabold text-text-main font-mono">
                            Rp {{ number_format($order->additional_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Tampilan Desktop: Tabel Rapi & Lega -->
        <div class="hidden md:block border border-primary-border rounded-xl overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-primary-subtle/70 text-primary font-bold border-b border-primary-border text-sm uppercase">
                    <tr>
                        <th class="p-4">Keterangan Layanan / Komponen</th>
                        <th class="p-4 text-center">Jumlah</th>
                        <th class="p-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-border/60 text-base">
                    @if($order->serviceItems->isNotEmpty())
                        @foreach($order->serviceItems as $sItem)
                            <tr>
                                <td class="p-4">
                                    <div class="font-bold text-text-main">
                                        {{ $sItem->service_category }} ({{ $sItem->ac_type }})
                                    </div>
                                    @if($sItem->room_name)
                                        <div class="text-sm text-text-muted">Ruangan: {{ $sItem->room_name }}</div>
                                    @endif
                                    <div class="text-sm text-text-muted font-mono">
                                        @ Rp {{ number_format($sItem->price_per_unit, 0, ',', '.') }} / unit
                                    </div>
                                </td>
                                <td class="p-4 text-center font-bold text-text-main whitespace-nowrap">{{ $sItem->quantity }} Unit</td>
                                <td class="p-4 text-right font-bold font-mono text-text-main whitespace-nowrap">
                                    Rp {{ number_format($sItem->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="p-4">
                                <strong class="text-text-main block">{{ $order->dkm_complaint }}</strong>
                                <span class="text-sm text-text-muted">Jasa perawatan pendingin ruang ibadah</span>
                            </td>
                            <td class="p-4 text-center font-bold text-text-main whitespace-nowrap">{{ $order->ac_count }} Unit</td>
                            <td class="p-4 text-right font-bold font-mono text-text-main whitespace-nowrap">
                                Rp {{ number_format($order->base_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif

                    @if($order->spareparts->isNotEmpty())
                        @foreach($order->spareparts as $item)
                            <tr class="bg-bg-main/40">
                                <td class="p-4">
                                    <strong class="text-text-main block">{{ $item->item_name }}</strong>
                                    <span class="text-sm text-text-muted font-mono">@ Rp {{ number_format($item->price, 0, ',', '.') }} / unit</span>
                                </td>
                                <td class="p-4 text-center font-bold text-text-main whitespace-nowrap">{{ $item->quantity }}x</td>
                                <td class="p-4 text-right font-bold font-mono text-text-main whitespace-nowrap">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    @elseif($order->additional_amount > 0)
                        <tr class="bg-bg-main/40">
                            <td class="p-4">
                                <strong class="text-text-main block">Tambahan Suku Cadang / Freon</strong>
                                <span class="text-sm text-text-muted">{{ $order->additional_notes }}</span>
                            </td>
                            <td class="p-4 text-center font-bold text-text-main">1</td>
                            <td class="p-4 text-right font-bold font-mono text-text-main whitespace-nowrap">
                                Rp {{ number_format($order->additional_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Kotak Grand Total Tegas & Kontras Tinggi -->
        <div class="p-4 sm:p-5 rounded-xl bg-primary-subtle border-2 border-primary-border space-y-2">
            @if($order->additional_amount > 0)
                <div class="flex items-center justify-between text-sm sm:text-base text-text-muted">
                    <span>Biaya Servis Awal</span>
                    <span class="font-mono font-semibold text-text-main">Rp {{ number_format($order->base_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm sm:text-base text-text-muted">
                    <span>Tambahan Suku Cadang</span>
                    <span class="font-mono font-semibold text-text-main">Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</span>
                </div>
                <div class="pt-3 border-t border-primary-border flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <span class="text-base sm:text-lg font-extrabold text-text-main">TOTAL AKHIR PEMBAYARAN</span>
                    <span class="text-2xl sm:text-3xl font-extrabold font-mono text-primary">
                        Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}
                    </span>
                </div>
            @else
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                    <span class="text-base sm:text-lg font-extrabold text-text-main">BIAYA AWAL (BELUM PASTI)</span>
                    <span class="text-2xl sm:text-3xl font-extrabold font-mono text-primary">
                        Rp {{ number_format($order->base_amount, 0, ',', '.') }}
                    </span>
                </div>
            @endif
        </div>

        <p class="text-sm text-text-muted leading-relaxed">
            * Biaya di atas merupakan perkiraan biaya awal. Biaya akhir dapat disesuaikan apabila terdapat penambahan freon, suku cadang, atau tindakan khusus setelah teknisi memeriksa unit AC di lokasi masjid.
        </p>
    </section>

    <!-- ========================================================================= -->
    <!-- GALERI FOTO DOKUMENTASI SERVIS (DENGAN LIGHTBOX)                          -->
    <!-- ========================================================================= -->
    @if($order->photos->isNotEmpty())
        <section class="bg-surface rounded-2xl border border-primary-border p-5 sm:p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between gap-2 pb-3 border-b border-primary-border/70">
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-text-main">
                        Foto Dokumentasi Pengerjaan
                    </h2>
                    <p class="text-sm text-text-muted">Ketuk foto untuk melihat tampilan lebih besar.</p>
                </div>
                <span class="px-3 py-1 rounded-lg bg-primary-subtle text-primary text-sm font-bold shrink-0">
                    {{ $order->photos->count() }} Foto
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($order->photos as $photo)
                    <button type="button"
                            @click="openLightbox('{{ asset('storage/' . $photo->photo_url) }}', '{{ addslashes($photo->caption ?: 'Dokumentasi Servis') }}', '{{ ucfirst($photo->photo_type) }}')"
                            class="group text-left rounded-2xl border-2 border-primary-border overflow-hidden bg-bg-main hover:border-primary transition-colors cursor-pointer flex flex-col">
                        <div class="relative w-full h-44 bg-gray-100 overflow-hidden">
                            <img src="{{ asset('storage/' . $photo->photo_url) }}"
                                 alt="Foto Servis {{ ucfirst($photo->photo_type) }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                            <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-lg text-sm font-bold shadow-xs border
                                @if($photo->photo_type === 'sebelum') bg-[#FEF3C7] text-[#92400E] border-[#FCD34D]
                                @elseif($photo->photo_type === 'sesudah') bg-[#DCFCE7] text-[#15803D] border-[#86EFAC]
                                @else bg-[#E0F2FE] text-[#0369A1] border-[#BAE6FD]
                                @endif">
                                {{ ucfirst($photo->photo_type) }}
                            </span>
                        </div>
                        <div class="p-3.5 bg-surface flex-1 flex flex-col justify-between gap-1.5">
                            <p class="text-sm font-semibold text-text-main leading-snug">
                                {{ $photo->caption ?: 'Dokumentasi pengerjaan di lokasi' }}
                            </p>
                            <span class="text-sm font-bold text-primary">Perbesar Foto &rarr;</span>
                        </div>
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    <!-- ========================================================================= -->
    <!-- KOTAK LACAK PESANAN LAIN & TAUTAN BUAT PESANAN BARU                       -->
    <!-- ========================================================================= -->
    <section class="bg-surface rounded-2xl border border-primary-border p-5 shadow-sm space-y-4">
        <div class="space-y-1">
            <h2 class="text-base font-extrabold text-text-main">Lacak Pesanan Lain</h2>
            <p class="text-sm text-text-muted">Masukkan nomor registrasi pesanan lain untuk melihat statusnya.</p>
        </div>
        <form method="GET" action="{{ route('order.track') }}" class="flex flex-col sm:flex-row gap-3">
            <label for="status_track_order_input" class="sr-only">Nomor Registrasi Pesanan</label>
            <input type="text"
                   id="status_track_order_input"
                   name="order_number"
                   required
                   placeholder="Contoh: SO-{{ now()->format('Ymd') }}-001"
                   class="flex-1 min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main font-mono placeholder-gray-400 focus:outline-none focus:border-primary bg-surface">
            <button type="submit"
                    class="min-h-[50px] px-6 py-3 bg-primary hover:bg-primary-hover text-white rounded-xl text-base font-bold cursor-pointer transition-colors whitespace-nowrap shadow-sm">
                Cari Pesanan
            </button>
        </form>

        <div class="pt-3 border-t border-primary-border/70 text-center">
            <a href="{{ route('order.create') }}"
               class="min-h-[44px] inline-flex items-center justify-center gap-1.5 text-base font-bold text-primary hover:text-primary-hover transition-colors">
                <span aria-hidden="true">&larr;</span>
                <span>Buat Pesanan Baru Lainnya</span>
            </a>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- MODAL LIGHTBOX PRATINJAU FOTO                                             -->
    <!-- ========================================================================= -->
    <div x-show="lightboxUrl"
         x-cloak
         role="dialog"
         aria-modal="true"
         aria-label="Pratinjau Foto Dokumentasi"
         @click.self="closeLightbox()"
         class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4">
        <div class="bg-surface rounded-2xl border border-primary-border max-w-2xl w-full overflow-hidden shadow-2xl">
            <div class="px-4 py-3 bg-bg-main border-b border-primary-border flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="inline-block px-2.5 py-0.5 rounded-md bg-primary-subtle text-primary text-sm font-bold" x-text="lightboxType"></span>
                    <p class="text-base font-bold text-text-main truncate mt-0.5" x-text="lightboxCaption"></p>
                </div>
                <button type="button"
                        @click="closeLightbox()"
                        aria-label="Tutup pratinjau foto"
                        class="tap-target inline-flex items-center justify-center rounded-xl bg-surface border border-primary-border text-text-main hover:bg-primary-subtle text-2xl font-bold cursor-pointer shrink-0">
                    &times;
                </button>
            </div>
            <div class="p-3 bg-black flex items-center justify-center">
                <img :src="lightboxUrl" :alt="lightboxCaption" class="max-h-[75vh] w-auto object-contain rounded-lg">
            </div>
        </div>
    </div>

</div>
@endsection
