@extends('layouts.app', ['title' => 'Kuitansi Servis AC - ' . $order->order_number])

@section('content')
<div class="max-w-3xl mx-auto space-y-5 pb-6">

    <!-- ========================================================================= -->
    <!-- BILAH AKSI ATAS (HILANG SAAT CETAK — .no-print)                           -->
    <!-- ========================================================================= -->
    <div class="no-print bg-surface rounded-2xl border border-primary-border p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard', ['tab' => 'selesai']) }}"
                       class="min-h-[44px] inline-flex items-center gap-1.5 text-base font-bold text-text-muted hover:text-primary transition-colors">
                        <span aria-hidden="true">&larr;</span>
                        <span>Meja Admin</span>
                    </a>
                    <span class="text-primary-border" aria-hidden="true">|</span>
                @endif
            @endauth
            <a href="{{ route('order.status', ['order_number' => $order->order_number]) }}"
               class="min-h-[44px] inline-flex items-center gap-1.5 text-base font-bold text-primary hover:text-primary-hover transition-colors">
                <span aria-hidden="true">&larr;</span>
                <span>Halaman Status Pesanan</span>
            </a>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <button type="button"
                    onclick="window.print()"
                    class="min-h-[48px] px-5 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-base font-bold shadow-xs cursor-pointer transition-colors inline-flex items-center justify-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                <span>Cetak Kuitansi / Simpan PDF</span>
            </button>

            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ $whatsappUrl }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="min-h-[48px] px-5 py-2.5 rounded-xl bg-surface hover:bg-primary-subtle text-primary text-base font-bold border-2 border-primary shadow-xs transition-colors inline-flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12.031 2C6.51 2 2.02 6.489 2.02 12.011c0 1.763.461 3.485 1.337 5.002L2 22l5.118-1.342a9.97 9.97 0 0 0 4.913 1.296h.004c5.52 0 10.011-4.489 10.011-10.011C22.046 6.489 17.555 2 12.031 2Zm5.829 14.234c-.245.691-1.425 1.321-1.976 1.404-.512.077-1.178.109-1.901-.121-.438-.139-1.002-.326-1.724-.639-3.035-1.31-5.016-4.377-5.168-4.579-.151-.202-1.233-1.641-1.233-3.131 0-1.49.779-2.222 1.055-2.525.276-.303.603-.379.804-.379.201 0 .402.002.578.011.186.009.435-.071.681.519.251.605.854 2.084.929 2.235.075.151.126.328.025.53-.101.202-.151.328-.301.505-.151.177-.317.395-.452.53-.151.151-.308.315-.132.618.176.303.782 1.29 1.679 2.089 1.153 1.028 2.126 1.347 2.428 1.498.302.151.478.126.654-.076.176-.202.754-.883.955-1.186.201-.303.402-.252.678-.151.276.101 1.759.83 2.061.981.302.151.503.227.578.353.076.126.076.731-.169 1.422Z" />
                        </svg>
                        <span>Kirim ke WhatsApp DKM</span>
                    </a>
                @endif
            @endauth
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- LEMBAR KUITANSI RESMI (DOCUMEN LEMBAGA PROFESIONAL & RAMAH CETAK)         -->
    <!-- ========================================================================= -->
    <article class="receipt-sheet bg-surface rounded-2xl border-2 border-primary-border p-6 sm:p-10 shadow-md print:border-none print:shadow-none print:p-0 space-y-6">

        <!-- Kop Kuitansi (Garis Hijau Primary + Aksen Emas Tipis) -->
        <div class="space-y-2">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-4 border-b-4 border-primary">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl border-2 border-primary text-primary font-extrabold text-sm flex items-center justify-center shrink-0">
                            AC
                        </span>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-extrabold text-primary tracking-tight leading-tight">
                                KUITANSI SERVIS AC MASJID
                            </h1>
                            <p class="text-sm font-medium text-text-muted">
                                Layanan Perawatan &amp; Perbaikan Pendingin Ruang Ibadah
                            </p>
                        </div>
                    </div>
                </div>

                <div class="sm:text-right space-y-0.5">
                    <span class="block text-sm uppercase font-bold tracking-wider text-text-muted">Bukti Pelunasan Resmi</span>
                    <span class="block text-lg sm:text-xl font-extrabold text-text-main font-mono">{{ $order->order_number }}</span>
                </div>
            </div>
            <div class="h-0.5 w-full bg-accent-gold/80"></div>
        </div>

        <!-- Stempel Status LUNAS & Tautan Data Pesanan -->
        <div class="p-4 rounded-xl bg-primary-subtle/70 border border-primary-border print:bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#DCFCE7] text-[#15803D] border-2 border-[#15803D] font-extrabold text-sm tracking-wider uppercase">
                    &#10003; LUNAS &amp; BERES
                </span>
                <span class="text-sm sm:text-base font-semibold text-text-main">
                    Pembayaran telah diverifikasi lunas oleh Admin.
                </span>
            </div>

            <a href="{{ route('order.status', ['order_number' => $order->order_number]) }}"
               class="no-print text-sm font-bold text-primary hover:underline whitespace-nowrap">
                Lihat Data Pesanan &rarr;
            </a>
            <span class="hidden print:inline text-xs text-text-muted font-mono">
                {{ route('order.status', ['order_number' => $order->order_number]) }}
            </span>
        </div>

        <!-- Identitas Masjid & Informasi Pelunasan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm sm:text-base">
            <div class="p-4 rounded-xl bg-bg-main border border-primary-border/70 print:bg-white space-y-1.5">
                <span class="text-sm font-bold text-primary uppercase tracking-wide block">Ditagihkan Kepada</span>
                <strong class="text-lg font-extrabold text-text-main block">{{ $order->masjid->name }}</strong>
                <p class="text-text-muted leading-relaxed">{{ $order->masjid->address }}</p>
                <p class="text-text-main pt-1">
                    Pengurus: <strong>{{ $order->masjid->pic_name }}</strong>
                    <span class="font-mono text-text-muted block sm:inline">({{ $order->masjid->pic_phone }})</span>
                </p>
            </div>

            <div class="p-4 rounded-xl bg-bg-main border border-primary-border/70 print:bg-white sm:text-right space-y-1.5">
                <span class="text-sm font-bold text-primary uppercase tracking-wide block">Informasi Pembayaran</span>
                <p class="text-text-main">
                    Waktu Bayar: <strong>{{ $order->paid_at ? $order->paid_at->translatedFormat('d F Y, H:i') : '-' }} WIB</strong>
                </p>
                <p class="text-text-main">
                    Metode: <strong>{{ $metodeText }}</strong>
                </p>
                <p class="text-text-main">
                    Teknisi: <strong>{{ $order->technician->name ?? 'Tim Teknisi' }}</strong>
                </p>
                <div class="pt-1">
                    <span class="inline-block px-3 py-1 rounded-md border border-[#15803D] text-[#15803D] font-bold text-sm">
                        STATUS: SUDAH DIBAYAR
                    </span>
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Pekerjaan & Biaya -->
        <div class="border-2 border-primary-border rounded-xl overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-primary-subtle/70 print:bg-gray-100 text-primary font-bold border-b-2 border-primary-border uppercase text-sm">
                    <tr>
                        <th class="p-3.5 sm:p-4">Deskripsi Pekerjaan Servis</th>
                        <th class="p-3.5 sm:p-4 text-center whitespace-nowrap">Jumlah</th>
                        <th class="p-3.5 sm:p-4 text-right whitespace-nowrap">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-border/60 text-sm sm:text-base">
                    @if($order->serviceItems->isNotEmpty())
                        @foreach($order->serviceItems as $sItem)
                            <tr>
                                <td class="p-3.5 sm:p-4">
                                    <strong class="text-text-main block font-bold">
                                        {{ $sItem->service_category }} ({{ $sItem->ac_type }})
                                    </strong>
                                    <span class="text-text-muted text-sm block mt-0.5">
                                        @if($sItem->room_name) Lokasi: {{ $sItem->room_name }} &bull; @endif
                                        <span class="font-mono">@ Rp {{ number_format($sItem->price_per_unit, 0, ',', '.') }} / unit</span>
                                    </span>
                                </td>
                                <td class="p-3.5 sm:p-4 text-center font-bold text-text-main whitespace-nowrap">{{ $sItem->quantity }} Unit</td>
                                <td class="p-3.5 sm:p-4 text-right font-bold font-mono text-text-main whitespace-nowrap">
                                    Rp {{ number_format($sItem->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="p-3.5 sm:p-4">
                                <strong class="text-text-main block font-bold">{{ $order->dkm_complaint }}</strong>
                                <span class="text-text-muted text-sm block mt-0.5">Pembersihan filter, evaporator, blower indoor, kondensor outdoor &amp; pengecekan kelistrikan</span>
                            </td>
                            <td class="p-3.5 sm:p-4 text-center font-bold text-text-main whitespace-nowrap">{{ $order->ac_count }} Unit</td>
                            <td class="p-3.5 sm:p-4 text-right font-bold font-mono text-text-main whitespace-nowrap">
                                Rp {{ number_format($order->base_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif

                    @if($order->spareparts->isNotEmpty())
                        @foreach($order->spareparts as $item)
                            <tr class="bg-bg-main/40 print:bg-white">
                                <td class="p-3.5 sm:p-4">
                                    <strong class="text-text-main block font-bold">{{ $item->item_name }}</strong>
                                    <span class="text-text-muted text-sm font-mono">@ Rp {{ number_format($item->price, 0, ',', '.') }} / unit</span>
                                </td>
                                <td class="p-3.5 sm:p-4 text-center font-bold text-text-main whitespace-nowrap">{{ $item->quantity }}x</td>
                                <td class="p-3.5 sm:p-4 text-right font-bold font-mono text-text-main whitespace-nowrap">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    @elseif($order->additional_amount > 0)
                        <tr class="bg-bg-main/40 print:bg-white">
                            <td class="p-3.5 sm:p-4">
                                <strong class="text-text-main block font-bold">Suku Cadang / Tindakan Tambahan</strong>
                                <span class="text-text-muted text-sm">{{ $order->additional_notes }}</span>
                            </td>
                            <td class="p-3.5 sm:p-4 text-center font-bold text-text-main">1</td>
                            <td class="p-3.5 sm:p-4 text-right font-bold font-mono text-text-main whitespace-nowrap">
                                Rp {{ number_format($order->additional_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot class="bg-primary-subtle/70 print:bg-gray-100 border-t-2 border-primary-border">
                    <tr>
                        <td colspan="2" class="p-4 text-right font-extrabold text-text-main text-base sm:text-lg">
                            TOTAL DIBAYARKAN:
                        </td>
                        <td class="p-4 text-right font-extrabold font-mono text-primary text-xl sm:text-2xl whitespace-nowrap">
                            Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Catatan Penutup & Tanda Tangan -->
        <div class="pt-6 border-t border-primary-border flex flex-col sm:flex-row sm:items-end justify-between gap-6 text-sm sm:text-base">
            <p class="max-w-sm text-text-muted leading-relaxed italic">
                Terima kasih atas kepercayaannya. Semoga AC masjid senantiasa sejuk dan membawa kenyamanan bagi jamaah dalam beribadah.
            </p>
            <div class="text-left sm:text-center space-y-12 shrink-0">
                <span class="block text-text-muted font-medium">Pengelola Servis AC Masjid</span>
                <span class="inline-block font-extrabold text-text-main border-t-2 border-primary pt-1.5 px-6">
                    Admin Operasional
                </span>
            </div>
        </div>

    </article>

</div>

<style>
@media print {
    .no-print { display: none !important; }
    html, body { background-color: #ffffff !important; color: #000000 !important; }
    header, footer, nav { display: none !important; }
    .receipt-sheet { border: none !important; box-shadow: none !important; padding: 0 !important; }
}
</style>
@endsection
