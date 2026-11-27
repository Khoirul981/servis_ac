@extends('layouts.app', ['title' => 'Dashboard Tugas Teknisi - Servis AC Masjid'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-6">

    <!-- Header Profil Teknisi -->
    <section class="rounded-2xl bg-gradient-to-br from-primary to-primary-light text-white p-5 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="space-y-1">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-accent-gold/60 text-sm font-semibold">
                    <span class="w-2 h-2 rounded-full bg-accent-gold shrink-0"></span>
                    Teknisi Lapangan
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-snug">
                    {{ Auth::user()->name }}
                </h1>
                <p class="text-sm sm:text-base font-mono text-white/90">
                    {{ Auth::user()->phone ?? Auth::user()->email }}
                </p>
            </div>
            <div class="flex sm:flex-col items-center sm:items-end justify-between gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-white/20">
                <span class="px-3.5 py-1.5 rounded-xl bg-white/15 border border-white/25 text-sm font-bold">
                    {{ now()->translatedFormat('d F Y') }}
                </span>
                <span class="text-sm font-semibold text-white/90">
                    {{ $activeOrders->count() }} Tugas Aktif
                </span>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- DAFTAR TUGAS AKTIF HARI INI                                               -->
    <!-- ========================================================================= -->
    <section class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2 px-1">
            <h2 class="text-xl font-extrabold text-text-main">
                Tugas Aktif ({{ $activeOrders->count() }})
            </h2>
            <span class="text-sm font-medium text-text-muted">Urut berdasarkan jadwal</span>
        </div>

        @forelse($activeOrders as $order)
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', (string) $order->masjid->pic_phone);
                if (str_starts_with($cleanPhone, '08')) {
                    $cleanPhone = '628' . substr($cleanPhone, 2);
                }
                $waText = urlencode("Assalamualaikum Pak {$order->masjid->pic_name}, saya " . Auth::user()->name . " dari tim Servis AC Masjid terkait jadwal servis di {$order->masjid->name}.");
                $waUrl = "https://wa.me/{$cleanPhone}?text={$waText}";

                $mapsQuery = urlencode($order->masjid->name . ', ' . $order->masjid->address);
                $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$mapsQuery}";
            @endphp

            <article class="bg-surface rounded-2xl border-2 {{ $order->status === 'dikerjakan' ? 'border-primary ring-2 ring-primary/15' : 'border-primary-border' }} p-5 sm:p-6 shadow-sm space-y-4">
                <!-- Baris Atas: Nomor Pesanan & Badge Status -->
                <div class="flex flex-wrap items-start justify-between gap-2 pb-3 border-b border-primary-border/70">
                    <div class="min-w-0">
                        <span class="block text-sm font-mono font-bold text-primary">{{ $order->order_number }}</span>
                        <h3 class="text-xl sm:text-2xl font-extrabold text-text-main leading-snug mt-0.5">
                            {{ $order->masjid->name }}
                        </h3>
                    </div>

                    @if($order->status === 'dikerjakan')
                        <span class="px-3.5 py-1.5 rounded-lg text-sm font-bold bg-primary text-white shrink-0">
                            Sedang Dikerjakan
                        </span>
                    @else
                        <span class="px-3.5 py-1.5 rounded-lg text-sm font-bold bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD] shrink-0">
                            Dijadwalkan
                        </span>
                    @endif
                </div>

                <!-- Jadwal Sesi & Layanan Mencolok -->
                <div class="p-4 rounded-xl bg-primary-subtle border border-primary-border space-y-1.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-sm font-bold text-text-muted uppercase tracking-wide">Jadwal Kunjungan</span>
                        <span class="px-2.5 py-0.5 rounded-md bg-primary text-white text-sm font-bold">
                            {{ $order->ac_count }} Unit AC
                        </span>
                    </div>
                    <p class="text-base sm:text-lg font-extrabold text-primary">
                        {{ $order->service_date ? $order->service_date->translatedFormat('l, d M Y') : 'Jadwal Menyusul' }}
                        @if($order->service_time)
                            &bull; {{ $order->service_time }}
                        @endif
                    </p>
                    <p class="text-sm sm:text-base font-semibold text-text-main">
                        Keluhan: {{ $order->dkm_complaint }}
                    </p>
                </div>

                <!-- Alamat & Kontak DKM -->
                <div class="space-y-1.5 text-base">
                    <p class="text-text-muted leading-relaxed">{{ $order->masjid->address }}</p>
                    <p class="font-bold text-text-main">
                        Pengurus: {{ $order->masjid->pic_name }}
                        <span class="font-mono text-primary font-semibold block sm:inline sm:ml-1">({{ $order->masjid->pic_phone }})</span>
                    </p>
                </div>

                <!-- 3 Tombol Besar Lapangan (Target Sentuh >= 48px, Berjarak Lega) -->
                <div class="space-y-3 pt-2">
                    <a href="{{ route('technician.order.show', $order->id) }}"
                       class="w-full min-h-[54px] px-5 py-3.5 bg-primary hover:bg-primary-hover text-white font-extrabold text-base sm:text-lg rounded-xl shadow-xs flex items-center justify-center gap-2 transition-colors">
                        <span>{{ $order->status === 'dikerjakan' ? 'Lanjutkan Lembar Kerja' : 'Buka Lembar Kerja' }}</span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <a href="{{ $mapsUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary text-base font-bold flex items-center justify-center gap-2 transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            <span>Peta Lokasi</span>
                        </a>

                        <a href="{{ $waUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary text-base font-bold flex items-center justify-center gap-2 transition-colors">
                            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12.031 2C6.51 2 2.02 6.489 2.02 12.011c0 1.763.461 3.485 1.337 5.002L2 22l5.118-1.342a9.97 9.97 0 0 0 4.913 1.296h.004c5.52 0 10.011-4.489 10.011-10.011C22.046 6.489 17.555 2 12.031 2Zm5.829 14.234c-.245.691-1.425 1.321-1.976 1.404-.512.077-1.178.109-1.901-.121-.438-.139-1.002-.326-1.724-.639-3.035-1.31-5.016-4.377-5.168-4.579-.151-.202-1.233-1.641-1.233-3.131 0-1.49.779-2.222 1.055-2.525.276-.303.603-.379.804-.379.201 0 .402.002.578.011.186.009.435-.071.681.519.251.605.854 2.084.929 2.235.075.151.126.328.025.53-.101.202-.151.328-.301.505-.151.177-.317.395-.452.53-.151.151-.308.315-.132.618.176.303.782 1.29 1.679 2.089 1.153 1.028 2.126 1.347 2.428 1.498.302.151.478.126.654-.076.176-.202.754-.883.955-1.186.201-.303.402-.252.678-.151.276.101 1.759.83 2.061.981.302.151.503.227.578.353.076.126.076.731-.169 1.422Z" />
                            </svg>
                            <span>Chat WA DKM</span>
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="bg-surface rounded-2xl border-2 border-dashed border-primary-border p-8 sm:p-10 text-center space-y-3">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-primary-subtle border border-primary-border text-primary flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <h3 class="text-xl font-extrabold text-text-main">Tidak Ada Tugas Aktif Saat Ini</h3>
                <p class="text-base text-text-muted max-w-md mx-auto leading-relaxed">
                    Alhamdulillah, seluruh jadwal servis yang ditugaskan kepada Anda telah selesai. Tugas baru dari Admin akan tampil otomatis di sini.
                </p>
            </div>
        @endforelse
    </section>

    <!-- ========================================================================= -->
    <!-- RIWAYAT 10 TUGAS TERAKHIR                                                 -->
    <!-- ========================================================================= -->
    @if($completedOrders->isNotEmpty())
        <section class="space-y-3 pt-4 border-t-2 border-primary-border/70">
            <h2 class="text-lg font-extrabold text-text-main px-1">
                Riwayat Tugas Terakhir ({{ $completedOrders->count() }})
            </h2>

            <div class="space-y-3">
                @foreach($completedOrders as $order)
                    <a href="{{ route('technician.order.payment', $order->id) }}"
                       class="block bg-surface rounded-2xl border-2 border-primary-border hover:border-primary p-4 sm:p-5 shadow-xs transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="min-w-0 space-y-1">
                                <span class="block text-base sm:text-lg font-extrabold text-text-main leading-snug">
                                    {{ $order->masjid->name }}
                                </span>
                                <span class="block text-sm text-text-muted">
                                    <span class="font-mono font-semibold text-primary">{{ $order->order_number }}</span>
                                    &bull; {{ $order->ac_count }} Unit AC
                                </span>
                            </div>

                            <div class="flex sm:flex-col items-center sm:items-end justify-between gap-2 shrink-0">
                                <span class="px-3 py-1 rounded-lg font-bold text-sm
                                    {{ $order->status === 'selesai' ? 'bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC]' : ($order->status === 'menunggu_verifikasi' ? 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]' : 'bg-[#FFEDD5] text-[#C2410C] border border-[#FDBA74]') }}">
                                    {{ $order->statusLabel() }}
                                </span>

                                @if(in_array($order->status, ['pekerjaan_selesai', 'menunggu_bayar']) && !$order->payment_proof)
                                    <span class="text-sm font-bold text-[#C2410C]">Upload Bukti Bayar &rarr;</span>
                                @elseif($order->status === 'menunggu_verifikasi')
                                    <span class="text-sm font-bold text-[#0369A1]">Dicek Admin &rarr;</span>
                                @else
                                    <span class="text-sm font-bold text-primary">Lihat Rincian &rarr;</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
