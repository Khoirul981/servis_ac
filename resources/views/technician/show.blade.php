@extends('layouts.app', ['title' => 'Lembar Kerja Servis - ' . $order->masjid->name])

@section('content')
@php
    $isWorkCompleted = $order->isWorkCompleted();
    $step1Done = $order->status !== 'dijadwalkan';
    $step2Done = $order->photos->isNotEmpty();
    $step3Done = $order->spareparts->isNotEmpty() || $order->additional_amount > 0;
    $step4Done = $isWorkCompleted;

    $totalMasjidAc = $order->masjid->acUnits->sum('quantity') ?: $order->masjid->acUnits->count();
@endphp

<div class="max-w-2xl mx-auto space-y-5 pb-6"
     x-data="technicianWork()"
     @keydown.escape.window="showEditPhotoModal = false; activeLightboxUrl = ''; showEditSparepartModal = false; showAddAcModal = false; showEditAcModal = false;">

    <!-- Tombol Kembali -->
    <div>
        <a href="{{ route('technician.dashboard') }}"
           class="min-h-[44px] inline-flex items-center gap-1.5 text-base font-bold text-primary hover:text-primary-hover transition-colors">
            <span aria-hidden="true">&larr;</span>
            <span>Kembali ke Daftar Tugas</span>
        </a>
    </div>

    <!-- Banner Terkunci Jika Pekerjaan Fisik Telah Selesai -->
    @if($isWorkCompleted)
        <div class="p-5 rounded-2xl bg-[#DCFCE7] border-2 border-[#86EFAC] space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="text-base font-extrabold text-[#14532D] flex items-center gap-2">
                    <svg class="w-6 h-6 text-[#15803D] shrink-0" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>Pekerjaan Selesai &amp; Data Terkunci</span>
                </span>
                <span class="px-3 py-1 rounded-lg text-sm font-bold bg-[#15803D] text-white">TERKUNCI</span>
            </div>
            <p class="text-sm sm:text-base text-[#14532D] leading-relaxed">
                Pekerjaan servis AC di lokasi telah dikonfirmasi selesai. Data foto dan suku cadang sudah dikunci sebagai arsip resmi.
            </p>
            <a href="{{ route('technician.order.payment', $order->id) }}"
               class="w-full min-h-[52px] px-5 py-3 rounded-xl bg-primary hover:bg-primary-hover text-white font-extrabold text-base transition-colors flex items-center justify-center gap-2 shadow-xs">
                <span>Buka Halaman Pelaporan Pembayaran</span>
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- RINGKASAN TUGAS & PINTASAN PETA / WA DKM                                  -->
    <!-- ========================================================================= -->
    <section class="bg-surface rounded-2xl border-2 border-primary-border p-5 sm:p-6 shadow-sm space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-2 pb-3 border-b border-primary-border/70">
            <div class="min-w-0">
                <span class="block text-sm font-mono font-bold text-primary">{{ $order->order_number }}</span>
                <h1 class="text-2xl font-extrabold text-text-main leading-snug mt-0.5">{{ $order->masjid->name }}</h1>
            </div>
            <span class="px-3.5 py-1.5 rounded-lg text-sm font-bold shrink-0
                {{ $isWorkCompleted ? 'bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC]' : ($order->status === 'dikerjakan' ? 'bg-primary text-white' : 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]') }}">
                {{ $order->statusLabel() }}
            </span>
        </div>

        <p class="text-base text-text-muted leading-relaxed">{{ $order->masjid->address }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 rounded-xl bg-bg-main border border-primary-border/70 text-sm sm:text-base">
            <div>
                <span class="block text-sm text-text-muted">Pengurus (DKM / Marbot):</span>
                <strong class="text-text-main font-bold">{{ $order->masjid->pic_name }}</strong>
                <span class="block font-mono text-primary font-semibold text-sm">{{ $order->masjid->pic_phone }}</span>
            </div>
            <div>
                <span class="block text-sm text-text-muted">Keluhan ({{ $order->ac_count }} Unit AC):</span>
                <strong class="text-text-main font-bold">{{ $order->dkm_complaint }}</strong>
            </div>
        </div>

        @if($order->serviceItems->isNotEmpty())
            <div class="space-y-2 pt-1">
                <span class="text-sm font-bold text-text-muted uppercase tracking-wide block">
                    Daftar Permintaan Servis AC ({{ $order->serviceItems->sum('quantity') }} Unit):
                </span>
                <div class="space-y-2">
                    @foreach($order->serviceItems as $sItem)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-sm sm:text-base bg-primary-subtle/50 px-3.5 py-2.5 rounded-xl border border-primary-border">
                            <div>
                                <strong class="text-text-main font-bold">{{ $sItem->quantity }}x {{ $sItem->ac_type }}</strong>
                                <span class="text-text-main"> &bull; {{ $sItem->service_category }}</span>
                                @if($sItem->room_name)
                                    <span class="text-text-muted block sm:inline">({{ $sItem->room_name }})</span>
                                @endif
                            </div>
                            <span class="font-bold font-mono text-primary whitespace-nowrap">Rp {{ number_format($sItem->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Pintasan Google Maps & WhatsApp DKM -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer"
               class="min-h-[48px] px-4 py-2.5 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary text-base font-bold flex items-center justify-center gap-2 transition-colors">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                <span>Peta Lokasi</span>
            </a>
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
               class="min-h-[48px] px-4 py-2.5 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary text-base font-bold flex items-center justify-center gap-2 transition-colors">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12.031 2C6.51 2 2.02 6.489 2.02 12.011c0 1.763.461 3.485 1.337 5.002L2 22l5.118-1.342a9.97 9.97 0 0 0 4.913 1.296h.004c5.52 0 10.011-4.489 10.011-10.011C22.046 6.489 17.555 2 12.031 2Zm5.829 14.234c-.245.691-1.425 1.321-1.976 1.404-.512.077-1.178.109-1.901-.121-.438-.139-1.002-.326-1.724-.639-3.035-1.31-5.016-4.377-5.168-4.579-.151-.202-1.233-1.641-1.233-3.131 0-1.49.779-2.222 1.055-2.525.276-.303.603-.379.804-.379.201 0 .402.002.578.011.186.009.435-.071.681.519.251.605.854 2.084.929 2.235.075.151.126.328.025.53-.101.202-.151.328-.301.505-.151.177-.317.395-.452.53-.151.151-.308.315-.132.618.176.303.782 1.29 1.679 2.089 1.153 1.028 2.126 1.347 2.428 1.498.302.151.478.126.654-.076.176-.202.754-.883.955-1.186.201-.303.402-.252.678-.151.276.101 1.759.83 2.061.981.302.151.503.227.578.353.076.126.076.731-.169 1.422Z" />
                </svg>
                <span>Chat WA DKM</span>
            </a>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- ACCORDION INVENTARIS UNIT AC MASJID                                       -->
    <!-- ========================================================================= -->
    <section class="bg-surface rounded-2xl border-2 border-primary-border shadow-sm overflow-hidden" x-data="{ openAcSection: false }">
        <button type="button"
                @click="openAcSection = !openAcSection"
                :aria-expanded="openAcSection.toString()"
                class="w-full min-h-[64px] px-5 py-4 flex items-center justify-between gap-3 text-left hover:bg-primary-subtle/50 transition-colors cursor-pointer">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-11 h-11 rounded-xl bg-primary-subtle text-primary border border-primary-border flex items-center justify-center font-extrabold text-sm shrink-0">
                    AC
                </span>
                <div class="min-w-0">
                    <h2 class="text-base sm:text-lg font-extrabold text-text-main leading-tight">Inventaris Unit AC Masjid</h2>
                    <span class="block text-sm text-text-muted mt-0.5">
                        Total tercatat: <strong class="text-primary">{{ $totalMasjidAc }} Unit</strong>
                    </span>
                </div>
            </div>
            <span class="px-3.5 py-2 rounded-xl bg-primary-subtle text-primary text-sm font-bold shrink-0">
                <span x-text="openAcSection ? 'Tutup' : 'Kelola AC'"></span>
            </span>
        </button>

        <div x-show="openAcSection" x-cloak class="px-5 pb-5 pt-4 border-t border-primary-border bg-bg-main/50 space-y-4">
            @if($order->masjid->acUnits->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($order->masjid->acUnits as $unit)
                        <div class="p-4 rounded-xl border-2 border-primary-border bg-surface space-y-3 flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <strong class="text-base font-extrabold text-text-main leading-snug">{{ $unit->room_name }}</strong>
                                    <span class="px-2.5 py-1 rounded-lg bg-primary-subtle text-primary font-bold text-sm border border-primary-border shrink-0">
                                        {{ $unit->quantity ?? 1 }} Unit
                                    </span>
                                </div>
                                <div class="text-sm text-text-muted mt-1.5 space-y-0.5">
                                    <span class="block">Tipe / PK: <strong class="text-text-main">{{ $unit->ac_type }}</strong></span>
                                    @if($unit->brand)
                                        <span class="block">Merek: <strong class="text-text-main">{{ $unit->brand }}</strong></span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-2.5 border-t border-primary-border/60">
                                <button type="button"
                                        @click="openEditAc({{ $unit->id }}, '{{ addslashes($unit->room_name) }}', '{{ addslashes($unit->ac_type) }}', '{{ addslashes($unit->brand ?? '') }}', {{ $unit->quantity ?? 1 }})"
                                        class="flex-1 min-h-[44px] px-3 py-2 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-sm cursor-pointer transition-colors">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('technician.order.ac.destroy', ['id' => $order->id, 'unit_id' => $unit->id]) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit AC di {{ addslashes($unit->room_name) }} ini?');" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full min-h-[44px] px-3 py-2 rounded-xl border border-[#FCA5A5] bg-surface text-[#B91C1C] hover:bg-[#FEE2E2] font-bold text-sm cursor-pointer transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-5 rounded-xl bg-surface border-2 border-dashed border-primary-border text-center text-sm sm:text-base text-text-muted">
                    Belum ada data unit AC yang dicatat untuk masjid ini.
                </div>
            @endif

            <button type="button"
                    @click="showAddAcModal = true"
                    class="w-full min-h-[50px] py-3 px-4 bg-primary hover:bg-primary-hover text-white font-bold text-base rounded-xl transition-colors cursor-pointer flex items-center justify-center gap-2 shadow-xs">
                <span>+ Catat / Tambah Unit AC Masjid Ini</span>
            </button>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- INDIKATOR PROGRES 4 LANGKAH RINGKAS                                       -->
    <!-- ========================================================================= -->
    <nav aria-label="Progres Lembar Kerja" class="bg-surface rounded-2xl border border-primary-border p-3 shadow-sm">
        <ol class="grid grid-cols-4 gap-2">
            <li>
                <button type="button" @click="focusStep(1)"
                        class="w-full min-h-[58px] p-2 rounded-xl border-2 flex flex-col items-center justify-center gap-1 transition-colors cursor-pointer {{ $step1Done ? 'bg-[#DCFCE7]/70 border-[#86EFAC] text-[#15803D]' : 'bg-primary-subtle border-primary text-primary' }}">
                    <span class="w-6 h-6 rounded-full text-sm font-extrabold flex items-center justify-center {{ $step1Done ? 'bg-[#15803D] text-white' : 'bg-primary text-white' }}">
                        @if($step1Done) &#10003; @else 1 @endif
                    </span>
                    <span class="text-sm font-bold leading-none">Tiba</span>
                </button>
            </li>
            <li>
                <button type="button" @click="focusStep(2)"
                        class="w-full min-h-[58px] p-2 rounded-xl border-2 flex flex-col items-center justify-center gap-1 transition-colors cursor-pointer {{ $step2Done ? 'bg-[#DCFCE7]/70 border-[#86EFAC] text-[#15803D]' : ($step1Done ? 'bg-primary-subtle border-primary text-primary' : 'bg-bg-main border-gray-200 text-text-muted') }}">
                    <span class="w-6 h-6 rounded-full text-sm font-extrabold flex items-center justify-center {{ $step2Done ? 'bg-[#15803D] text-white' : ($step1Done ? 'bg-primary text-white' : 'bg-surface border border-gray-300 text-text-muted') }}">
                        @if($step2Done) &#10003; @else 2 @endif
                    </span>
                    <span class="text-sm font-bold leading-none">Foto</span>
                </button>
            </li>
            <li>
                <button type="button" @click="focusStep(3)"
                        class="w-full min-h-[58px] p-2 rounded-xl border-2 flex flex-col items-center justify-center gap-1 transition-colors cursor-pointer {{ $step3Done ? 'bg-[#DCFCE7]/70 border-[#86EFAC] text-[#15803D]' : ($step2Done && !$step4Done ? 'bg-primary-subtle border-primary text-primary' : 'bg-bg-main border-gray-200 text-text-muted') }}">
                    <span class="w-6 h-6 rounded-full text-sm font-extrabold flex items-center justify-center {{ $step3Done ? 'bg-[#15803D] text-white' : ($step2Done && !$step4Done ? 'bg-primary text-white' : 'bg-surface border border-gray-300 text-text-muted') }}">
                        @if($step3Done) &#10003; @else 3 @endif
                    </span>
                    <span class="text-sm font-bold leading-none">Part</span>
                </button>
            </li>
            <li>
                <button type="button" @click="focusStep(4)"
                        class="w-full min-h-[58px] p-2 rounded-xl border-2 flex flex-col items-center justify-center gap-1 transition-colors cursor-pointer {{ $step4Done ? 'bg-[#DCFCE7]/70 border-[#86EFAC] text-[#15803D]' : ($step2Done ? 'bg-primary-subtle border-primary text-primary' : 'bg-bg-main border-gray-200 text-text-muted') }}">
                    <span class="w-6 h-6 rounded-full text-sm font-extrabold flex items-center justify-center {{ $step4Done ? 'bg-[#15803D] text-white' : ($step2Done ? 'bg-primary text-white' : 'bg-surface border border-gray-300 text-text-muted') }}">
                        @if($step4Done) &#10003; @else 4 @endif
                    </span>
                    <span class="text-sm font-bold leading-none">Selesai</span>
                </button>
            </li>
        </ol>
    </nav>

    <!-- ========================================================================= -->
    <!-- LANGKAH 1: KONFIRMASI KEDATANGAN DI LOKASI                                -->
    <!-- ========================================================================= -->
    <section id="step-1" class="bg-surface rounded-2xl border-2 {{ !$step1Done ? 'border-primary shadow-sm' : 'border-primary-border' }} overflow-hidden">
        <button type="button"
                @click="openStep1 = !openStep1"
                class="w-full min-h-[64px] px-5 py-4 flex items-center justify-between gap-3 text-left cursor-pointer hover:bg-primary-subtle/40 transition-colors">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 rounded-xl {{ $step1Done ? 'bg-[#15803D] text-white' : 'bg-primary text-white' }} flex items-center justify-center font-extrabold text-base shrink-0">
                    @if($step1Done) &#10003; @else 1 @endif
                </span>
                <div>
                    <h2 class="text-lg font-extrabold text-text-main leading-tight">Kedatangan di Lokasi</h2>
                    <span class="text-sm text-text-muted">Konfirmasi saat sudah tiba di masjid</span>
                </div>
            </div>
            @if($step1Done)
                <span class="px-3 py-1 rounded-lg bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC] text-sm font-bold shrink-0">Selesai</span>
            @else
                <span class="px-3 py-1 rounded-lg bg-primary-subtle text-primary border border-primary-border text-sm font-bold shrink-0">Aktif</span>
            @endif
        </button>

        <div x-show="openStep1" class="px-5 pb-5 pt-3 border-t border-primary-border/70 space-y-4">
            @if($order->status === 'dijadwalkan')
                <p class="text-base text-text-muted leading-relaxed">
                    Tekan tombol di bawah saat Anda telah tiba di lokasi masjid untuk mulai mengerjakan servis AC.
                </p>
                <form method="POST" action="{{ route('technician.order.start', $order->id) }}">
                    @csrf
                    <button type="submit"
                            class="w-full min-h-[54px] py-3.5 px-5 bg-primary hover:bg-primary-hover text-white font-extrabold text-base sm:text-lg rounded-xl shadow-xs flex items-center justify-center cursor-pointer transition-colors">
                        Saya Sudah Tiba &amp; Mulai Bekerja
                    </button>
                </form>
            @else
                <div class="p-4 rounded-xl bg-[#DCFCE7] border border-[#86EFAC] text-[#14532D] text-base font-bold">
                    &#10003; Anda sudah tercatat tiba dan mengerjakan servis di lokasi masjid.
                </div>

                @if($order->status === 'dikerjakan')
                    <div class="pt-1 flex justify-end">
                        <form method="POST" action="{{ route('technician.order.revert', $order->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan status tiba dan mengembalikannya ke status dijadwalkan?');">
                            @csrf
                            <button type="submit"
                                    class="min-h-[44px] px-4 py-2 rounded-xl border border-[#FCA5A5] bg-surface hover:bg-[#FEE2E2] text-[#B91C1C] font-semibold text-sm transition-colors cursor-pointer">
                                Batalkan Status Tiba
                            </button>
                        </form>
                    </div>
                @endif
            @endif
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- LANGKAH 2: FOTO BUKTI SERVIS (KOMPRESI HTML5 CANVAS — KONTRAK KRITIS!)    -->
    <!-- ========================================================================= -->
    <section id="step-2" class="bg-surface rounded-2xl border-2 {{ $step1Done && !$step2Done ? 'border-primary shadow-sm' : 'border-primary-border' }} overflow-hidden">
        <button type="button"
                @click="openStep2 = !openStep2"
                class="w-full min-h-[64px] px-5 py-4 flex items-center justify-between gap-3 text-left cursor-pointer hover:bg-primary-subtle/40 transition-colors">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 rounded-xl {{ $step2Done ? 'bg-[#15803D] text-white' : ($step1Done ? 'bg-primary text-white' : 'bg-bg-main border border-primary-border text-text-muted') }} flex items-center justify-center font-extrabold text-base shrink-0">
                    @if($step2Done) &#10003; @else 2 @endif
                </span>
                <div>
                    <h2 class="text-lg font-extrabold text-text-main leading-tight">Foto Bukti Servis</h2>
                    <span class="text-sm text-text-muted">Wajib minimal 1 foto fisik pengerjaan</span>
                </div>
            </div>
            <span class="px-3 py-1 rounded-lg text-sm font-bold shrink-0 {{ $step2Done ? 'bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC]' : 'bg-[#FFEDD5] text-[#C2410C] border border-[#FDBA74]' }}">
                {{ $order->photos->count() }} Foto
            </span>
        </button>

        <div x-show="openStep2" class="px-5 pb-5 pt-4 border-t border-primary-border/70 space-y-5">
            <!-- Galeri Foto Terunggah -->
            @if($order->photos->isNotEmpty())
                <div class="space-y-3">
                    <h3 class="text-sm font-extrabold text-text-muted uppercase tracking-wide">
                        Foto Terunggah ({{ $order->photos->count() }})
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        @foreach($order->photos as $photo)
                            <div class="rounded-2xl border-2 border-primary-border overflow-hidden bg-surface flex flex-col justify-between">
                                <button type="button"
                                        @click="openLightbox('{{ $photo->url }}', '{{ addslashes($photo->caption ?? '') }}')"
                                        class="relative block w-full bg-bg-main cursor-pointer group text-left">
                                    <img src="{{ $photo->url }}" alt="Bukti Servis" class="w-full h-44 object-cover group-hover:opacity-95 transition-opacity">
                                    <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-lg text-sm font-bold uppercase shadow-xs border {{ $photo->photo_type === 'sebelum' ? 'bg-[#FEF3C7] text-[#92400E] border-[#FCD34D]' : ($photo->photo_type === 'sesudah' ? 'bg-[#DCFCE7] text-[#15803D] border-[#86EFAC]' : 'bg-[#E0F2FE] text-[#0369A1] border-[#BAE6FD]') }}">
                                        {{ $photo->photo_type }}
                                    </span>
                                    <span class="absolute bottom-2 right-2 px-2.5 py-1 rounded-lg bg-black/70 text-white text-sm font-semibold">
                                        Perbesar
                                    </span>
                                </button>

                                <div class="p-3.5 space-y-3">
                                    <div class="space-y-0.5">
                                        <p class="text-base font-bold text-text-main leading-snug">
                                            {{ $photo->caption ?: 'Tanpa keterangan' }}
                                        </p>
                                        <span class="block text-sm text-text-muted">
                                            {{ $photo->created_at->translatedFormat('d M Y, H:i') }} WIB
                                        </span>
                                    </div>

                                    @if(!$isWorkCompleted)
                                        <div class="flex items-center gap-2 pt-2 border-t border-primary-border/60">
                                            <button type="button"
                                                    @click="openEditPhoto({{ $photo->id }}, '{{ $photo->photo_type }}', '{{ addslashes($photo->caption ?? '') }}')"
                                                    class="flex-1 min-h-[44px] px-3 py-2 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-sm transition-colors cursor-pointer">
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('technician.order.photo.destroy', $photo->id) }}" class="flex-1" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto bukti servis ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full min-h-[44px] px-3 py-2 rounded-xl border border-[#FCA5A5] text-[#B91C1C] hover:bg-[#FEE2E2] font-bold text-sm transition-colors cursor-pointer">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!$isWorkCompleted)
                <!-- Form Upload Foto Bukti dengan Kompresi HTML5 Canvas -->
                <form method="POST"
                      action="{{ route('technician.order.photo', $order->id) }}"
                      enctype="multipart/form-data"
                      @submit="handleSubmitPhoto"
                      class="space-y-4 pt-3 {{ $order->photos->isNotEmpty() ? 'border-t-2 border-primary-border/70' : '' }}">
                    @csrf

                    <!-- Pilihan Kategori Foto Besar -->
                    <div class="space-y-2">
                        <span class="block text-base font-bold text-text-main">Kategori Foto Bukti:</span>
                        <div class="grid grid-cols-3 gap-2.5">
                            <label class="min-h-[52px] border-2 rounded-xl p-3 text-center cursor-pointer text-sm sm:text-base font-bold transition-colors flex items-center justify-center"
                                   :class="photoType === 'sebelum' ? 'border-primary bg-primary-subtle text-primary ring-1 ring-primary' : 'border-primary-border text-text-main bg-surface hover:bg-primary-subtle/40'">
                                <input type="radio" name="photo_type" value="sebelum" x-model="photoType" class="sr-only">
                                <span>Sebelum</span>
                            </label>
                            <label class="min-h-[52px] border-2 rounded-xl p-3 text-center cursor-pointer text-sm sm:text-base font-bold transition-colors flex items-center justify-center"
                                   :class="photoType === 'sesudah' ? 'border-primary bg-primary-subtle text-primary ring-1 ring-primary' : 'border-primary-border text-text-main bg-surface hover:bg-primary-subtle/40'">
                                <input type="radio" name="photo_type" value="sesudah" x-model="photoType" class="sr-only">
                                <span>Sesudah</span>
                            </label>
                            <label class="min-h-[52px] border-2 rounded-xl p-3 text-center cursor-pointer text-sm sm:text-base font-bold transition-colors flex items-center justify-center"
                                   :class="photoType === 'sparepart' ? 'border-primary bg-primary-subtle text-primary ring-1 ring-primary' : 'border-primary-border text-text-main bg-surface hover:bg-primary-subtle/40'">
                                <input type="radio" name="photo_type" value="sparepart" x-model="photoType" class="sr-only">
                                <span>Sparepart</span>
                            </label>
                        </div>
                    </div>

                    <!-- Kartu Unggah Kamera / Galeri -->
                    <div class="border-2 border-dashed border-primary/60 rounded-2xl p-5 bg-primary-subtle/40 hover:bg-primary-subtle/70 text-center transition-colors space-y-3">
                        <label for="cameraInput" class="block cursor-pointer space-y-2 py-2">
                            <span class="mx-auto w-14 h-14 rounded-2xl bg-primary text-white flex items-center justify-center shadow-xs">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                </svg>
                            </span>
                            <span class="block text-base sm:text-lg font-extrabold text-primary">Ambil Foto Kamera / Pilih Galeri</span>
                            <span class="block text-sm text-text-muted">Foto otomatis dikompres agar ringan dan hemat kuota</span>

                            <!-- WAJIB: id="cameraInput" TANPA atribut name -->
                            <input type="file"
                                   id="cameraInput"
                                   accept="image/*"
                                   capture="environment"
                                   @change="handleFileSelect"
                                   class="sr-only">
                        </label>

                        <!-- WAJIB: id="compressedPhotoInput" DENGAN name="photo" -->
                        <input type="file" name="photo" id="compressedPhotoInput" class="hidden">

                        <!-- Pratinjau Foto Hasil Kompresi -->
                        <div x-show="previewUrl" x-cloak class="p-4 bg-surface border-2 border-primary-border rounded-xl space-y-3 text-left shadow-xs">
                            <div class="flex items-center justify-between gap-2 pb-2 border-b border-primary-border/70">
                                <span class="text-base font-bold text-text-main">Pratinjau Foto Siap Unggah</span>
                                <button type="button" @click="clearSelectedPhoto" class="min-h-[44px] px-3 py-1.5 rounded-xl bg-[#FEE2E2] text-[#B91C1C] font-bold text-sm cursor-pointer">
                                    Batal / Ganti
                                </button>
                            </div>
                            <div class="flex flex-col sm:flex-row items-start gap-3.5">
                                <img :src="previewUrl" alt="Foto Terpilih" class="w-full sm:w-28 h-40 sm:h-28 object-cover rounded-xl border border-primary-border">
                                <div class="space-y-1.5 flex-1">
                                    <span class="inline-block px-2.5 py-1 rounded-lg font-bold uppercase text-sm border"
                                          :class="photoType === 'sebelum' ? 'bg-[#FEF3C7] text-[#92400E] border-[#FCD34D]' : (photoType === 'sesudah' ? 'bg-[#DCFCE7] text-[#15803D] border-[#86EFAC]' : 'bg-[#E0F2FE] text-[#0369A1] border-[#BAE6FD]')"
                                          x-text="'Kategori: ' + photoType"></span>
                                    <p class="text-sm font-medium text-text-muted leading-relaxed" x-text="compressionStatus"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan Foto -->
                    <div class="space-y-1.5">
                        <label for="photo_caption_input" class="block text-base font-bold text-text-main">Keterangan Foto (Opsional)</label>
                        <input type="text"
                               id="photo_caption_input"
                               name="caption"
                               placeholder="Contoh: AC Shaf Depan setelah dicuci bersih"
                               class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface">
                    </div>

                    <button type="submit"
                            :disabled="!readyToUpload || isUploading"
                            class="w-full min-h-[52px] py-3 px-5 bg-primary hover:bg-primary-hover disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-base rounded-xl transition-colors cursor-pointer shadow-xs">
                        <span x-text="isUploading ? 'Sedang Mengunggah Foto...' : 'Unggah Foto Bukti Ini'"></span>
                    </button>
                </form>
            @else
                <div class="p-4 bg-bg-main border border-primary-border rounded-xl text-sm sm:text-base text-text-muted font-medium">
                    Data foto bukti servis telah dikunci bersamaan dengan penyelesaian pekerjaan fisik.
                </div>
            @endif
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- LANGKAH 3: TAMBAHAN SUKU CADANG / FREON (MULTI-ITEM)                      -->
    <!-- ========================================================================= -->
    <section id="step-3"
             class="bg-surface rounded-2xl border-2 {{ $step2Done && !$step4Done ? 'border-primary shadow-sm' : 'border-primary-border' }} overflow-hidden"
             x-data="{
                 showAddForm: {{ $order->spareparts->isEmpty() && $order->additional_amount == 0 ? 'true' : 'false' }},
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
        <button type="button"
                @click="openStep3 = !openStep3"
                class="w-full min-h-[64px] px-5 py-4 flex items-center justify-between gap-3 text-left cursor-pointer hover:bg-primary-subtle/40 transition-colors">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 rounded-xl {{ $step3Done ? 'bg-[#15803D] text-white' : ($step2Done ? 'bg-primary text-white' : 'bg-bg-main border border-primary-border text-text-muted') }} flex items-center justify-center font-extrabold text-base shrink-0">
                    @if($step3Done) &#10003; @else 3 @endif
                </span>
                <div>
                    <h2 class="text-lg font-extrabold text-text-main leading-tight">Suku Cadang / Freon</h2>
                    <span class="text-sm text-text-muted">Opsional — jika ada penggantian komponen</span>
                </div>
            </div>
            @if($step3Done)
                <span class="px-3 py-1 rounded-lg bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC] text-sm font-bold shrink-0">
                    {{ $order->spareparts->count() ?: 1 }} Item
                </span>
            @else
                <span class="px-3 py-1 rounded-lg bg-bg-main text-text-muted border border-primary-border text-sm font-bold shrink-0">
                    Opsional
                </span>
            @endif
        </button>

        <div x-show="openStep3" class="px-5 pb-5 pt-4 border-t border-primary-border/70 space-y-5">
            <!-- Daftar Suku Cadang Tercatat (Kartu di HP, Tabel di Desktop) -->
            @if($order->spareparts->isNotEmpty())
                <div class="space-y-3">
                    <h3 class="text-sm font-extrabold text-text-muted uppercase tracking-wide">
                        Daftar Suku Cadang Tercatat ({{ $order->spareparts->count() }})
                    </h3>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-3">
                        @foreach($order->spareparts as $item)
                            <div class="p-4 rounded-xl border-2 border-primary-border bg-bg-main/60 space-y-2.5">
                                <div class="flex items-start justify-between gap-2">
                                    <strong class="text-base font-extrabold text-text-main">{{ $item->item_name }}</strong>
                                    <span class="font-extrabold font-mono text-base text-primary whitespace-nowrap">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </span>
                                </div>
                                <p class="text-sm font-mono text-text-muted">
                                    {{ $item->quantity }} unit &times; Rp {{ number_format($item->price, 0, ',', '.') }}
                                </p>

                                @if(!$isWorkCompleted)
                                    <div class="flex items-center gap-2 pt-2 border-t border-primary-border/60">
                                        <button type="button"
                                                @click="openEditSparepart({{ $item->id }}, '{{ addslashes($item->item_name) }}', {{ $item->quantity }}, {{ (int)$item->price }})"
                                                class="flex-1 min-h-[44px] px-3 py-2 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-sm cursor-pointer transition-colors">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('technician.order.sparepart.item.destroy', $item->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus suku cadang {{ addslashes($item->item_name) }} ini?');" class="flex-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full min-h-[44px] px-3 py-2 rounded-xl border border-[#FCA5A5] bg-surface text-[#B91C1C] hover:bg-[#FEE2E2] font-bold text-sm cursor-pointer transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden md:block border border-primary-border rounded-xl overflow-hidden">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-primary-subtle/70 text-primary font-bold uppercase border-b border-primary-border">
                                <tr>
                                    <th class="p-3.5">Suku Cadang / Tindakan</th>
                                    <th class="p-3.5 text-center">Qty</th>
                                    <th class="p-3.5 text-right">Subtotal</th>
                                    @if(!$isWorkCompleted)
                                        <th class="p-3.5 text-right">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-primary-border/60">
                                @foreach($order->spareparts as $item)
                                    <tr>
                                        <td class="p-3.5">
                                            <strong class="block text-base text-text-main">{{ $item->item_name }}</strong>
                                            <span class="font-mono text-text-muted">@ Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="p-3.5 text-center font-bold font-mono">{{ $item->quantity }}x</td>
                                        <td class="p-3.5 text-right font-extrabold font-mono text-primary whitespace-nowrap">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                        @if(!$isWorkCompleted)
                                            <td class="p-3.5 text-right whitespace-nowrap">
                                                <div class="inline-flex items-center gap-2">
                                                    <button type="button"
                                                            @click="openEditSparepart({{ $item->id }}, '{{ addslashes($item->item_name) }}', {{ $item->quantity }}, {{ (int)$item->price }})"
                                                            class="min-h-[40px] px-3 py-1.5 rounded-lg border-2 border-primary text-primary hover:bg-primary-subtle font-bold text-sm cursor-pointer">
                                                        Edit
                                                    </button>
                                                    <form method="POST" action="{{ route('technician.order.sparepart.item.destroy', $item->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus suku cadang {{ addslashes($item->item_name) }} ini?');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="min-h-[40px] px-3 py-1.5 rounded-lg border border-[#FCA5A5] text-[#B91C1C] hover:bg-[#FEE2E2] font-bold text-sm cursor-pointer">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Total Biaya Tambahan -->
                    <div class="p-4 rounded-xl bg-primary-subtle border border-primary-border flex items-center justify-between gap-2">
                        <span class="text-base font-bold text-text-main">Total Biaya Suku Cadang</span>
                        <span class="text-lg font-extrabold font-mono text-primary">Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</span>
                    </div>

                    @if(!$isWorkCompleted)
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                            <button type="button"
                                    @click="showAddForm = !showAddForm"
                                    class="min-h-[44px] px-4 py-2 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-sm cursor-pointer transition-colors">
                                <span x-text="showAddForm ? 'Tutup Form Tambah' : '+ Tambah Suku Cadang Lain'"></span>
                            </button>

                            <form method="POST" action="{{ route('technician.order.sparepart.destroy', $order->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh catatan suku cadang tambahan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl text-[#B91C1C] hover:bg-[#FEE2E2] font-bold text-sm cursor-pointer transition-colors">
                                    Hapus Semua Suku Cadang
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @elseif($order->additional_amount > 0)
                <!-- Tampilan Cadangan Data Legacy -->
                <div class="p-4 rounded-xl bg-bg-main border border-primary-border space-y-2">
                    <div class="flex justify-between font-bold text-base text-text-main">
                        <span>Biaya Tambahan Tercatat:</span>
                        <span class="font-mono text-primary">Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</span>
                    </div>
                    <p class="text-sm text-text-muted">{{ $order->additional_notes }}</p>
                    @if(!$isWorkCompleted)
                        <div class="flex justify-end pt-2 border-t border-primary-border/60">
                            <form method="POST" action="{{ route('technician.order.sparepart.destroy', $order->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan biaya tambahan ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl border border-[#FCA5A5] text-[#B91C1C] hover:bg-[#FEE2E2] font-bold text-sm cursor-pointer">
                                    Hapus Biaya
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Form Tambah Suku Cadang Baru -->
            @if(!$isWorkCompleted)
                <div x-show="showAddForm" x-cloak class="pt-3 {{ $order->spareparts->isNotEmpty() ? 'border-t-2 border-primary-border/70' : '' }} space-y-4">
                    <h3 class="text-base font-extrabold text-text-main">
                        {{ $order->spareparts->isNotEmpty() ? '+ Tambah Suku Cadang Berikutnya' : 'Catat Suku Cadang / Tindakan Tambahan' }}
                    </h3>

                    <form method="POST" action="{{ route('technician.order.sparepart', $order->id) }}" class="space-y-4">
                        @csrf
                        <div class="space-y-1.5">
                            <label for="sparepart_item_name" class="block text-base font-bold text-text-main">
                                Nama Suku Cadang / Tindakan <span class="text-[#B91C1C]">*</span>
                            </label>
                            <input type="text"
                                   id="sparepart_item_name"
                                   name="item_name"
                                   x-model="newItemName"
                                   required
                                   placeholder="Contoh: Kapasitor 35uF, Tambah Freon R32 1kg"
                                   class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label for="sparepart_qty" class="block text-base font-bold text-text-main">
                                    Jumlah (Qty) <span class="text-[#B91C1C]">*</span>
                                </label>
                                <input type="number"
                                       id="sparepart_qty"
                                       name="quantity"
                                       x-model="newItemQty"
                                       min="1"
                                       max="999"
                                       required
                                       class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface font-mono font-bold">
                            </div>

                            <div class="space-y-1.5">
                                <label for="sparepart_price" class="block text-base font-bold text-text-main">
                                    Harga Satuan (Rp) <span class="text-[#B91C1C]">*</span>
                                </label>
                                <input type="number"
                                       id="sparepart_price"
                                       name="price"
                                       x-model="newItemPrice"
                                       step="1000"
                                       min="0"
                                       required
                                       placeholder="Contoh: 75000"
                                       class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface font-mono font-bold">
                            </div>
                        </div>

                        <div class="p-3.5 bg-primary-subtle rounded-xl border border-primary-border flex justify-between items-center text-primary">
                            <span class="text-base font-bold">Subtotal Item Ini</span>
                            <span class="font-extrabold font-mono text-lg" x-text="'Rp ' + formatRupiah(newSubtotal)"></span>
                        </div>

                        <button type="submit"
                                class="w-full min-h-[52px] py-3 px-5 bg-primary hover:bg-primary-hover text-white font-extrabold text-base rounded-xl transition-colors cursor-pointer shadow-xs">
                            + Simpan Suku Cadang ke Daftar
                        </button>
                    </form>
                </div>
            @else
                <div class="p-4 bg-bg-main border border-primary-border rounded-xl text-sm sm:text-base text-text-muted font-medium">
                    Data suku cadang telah dikunci bersamaan dengan konfirmasi penyelesaian pekerjaan.
                </div>
            @endif
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- LANGKAH 4: KONFIRMASI SELESAI PEKERJAAN FISIK                             -->
    <!-- ========================================================================= -->
    <section id="step-4" class="bg-surface rounded-2xl border-2 {{ $step2Done && !$step4Done ? 'border-primary shadow-sm' : 'border-primary-border' }} overflow-hidden">
        <button type="button"
                @click="openStep4 = !openStep4"
                class="w-full min-h-[64px] px-5 py-4 flex items-center justify-between gap-3 text-left cursor-pointer hover:bg-primary-subtle/40 transition-colors">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 rounded-xl {{ $step4Done ? 'bg-[#15803D] text-white' : 'bg-primary text-white' }} flex items-center justify-center font-extrabold text-base shrink-0">
                    @if($step4Done) &#10003; @else 4 @endif
                </span>
                <div>
                    <h2 class="text-lg font-extrabold text-text-main leading-tight">Selesaikan Pekerjaan Fisik</h2>
                    <span class="text-sm text-text-muted">Kunci lembar kerja &amp; lanjut ke pelaporan bayar</span>
                </div>
            </div>
            @if($step4Done)
                <span class="px-3 py-1 rounded-lg bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC] text-sm font-bold shrink-0">Selesai</span>
            @endif
        </button>

        <div x-show="openStep4" class="px-5 pb-5 pt-4 border-t border-primary-border/70 space-y-4">
            <!-- Ringkasan Tagihan Servis -->
            <div class="p-4 rounded-xl bg-primary-subtle border border-primary-border space-y-2 text-sm sm:text-base">
                <div class="flex justify-between text-text-muted">
                    <span>Biaya Servis Dasar ({{ $order->ac_count }} Unit)</span>
                    <span class="font-mono font-semibold text-text-main">Rp {{ number_format($order->base_amount, 0, ',', '.') }}</span>
                </div>
                @if($order->additional_amount > 0)
                    <div class="flex justify-between text-text-main font-semibold">
                        <span>Tambahan Suku Cadang</span>
                        <span class="font-mono">Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="pt-2.5 border-t border-primary-border flex justify-between items-center font-extrabold text-base sm:text-lg text-text-main">
                    <span>Total Tagihan Servis</span>
                    <span class="text-xl font-mono text-primary">Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Ceklis Kelengkapan -->
            @if(!$isWorkCompleted)
                <div class="p-4 rounded-xl bg-bg-main border border-primary-border space-y-2 text-sm sm:text-base">
                    <span class="block font-extrabold text-text-main">Ceklis Kelengkapan Sebelum Selesai:</span>
                    <div class="flex items-center gap-2.5 {{ $order->status === 'dikerjakan' ? 'text-[#15803D] font-bold' : 'text-[#92400E] font-semibold' }}">
                        <span>{{ $order->status === 'dikerjakan' ? '✓' : '!' }}</span>
                        <span>Status kedatangan di Langkah 1 sudah dimulai ({{ $order->status === 'dikerjakan' ? 'Terpenuhi' : 'Belum dimulai' }})</span>
                    </div>
                    <div class="flex items-center gap-2.5 {{ $order->photos->isNotEmpty() ? 'text-[#15803D] font-bold' : 'text-[#C2410C] font-semibold' }}">
                        <span>{{ $order->photos->isNotEmpty() ? '✓' : '!' }}</span>
                        <span>Foto bukti servis di Langkah 2 ({{ $order->photos->isNotEmpty() ? $order->photos->count() . ' foto terunggah' : 'Belum ada foto — wajib minimal 1 foto' }})</span>
                    </div>
                </div>

                @if($order->photos->isEmpty() || $order->status !== 'dikerjakan')
                    <div class="p-4 rounded-xl bg-[#FFEDD5] border-2 border-[#FDBA74] text-[#9A3412] text-sm sm:text-base font-semibold leading-relaxed">
                        @if($order->status !== 'dikerjakan')
                            Tombol selesai masih terkunci: silakan konfirmasi kedatangan di <strong>Langkah 1</strong> terlebih dahulu.
                        @else
                            Tombol selesai masih terkunci: silakan unggah minimal <strong>1 foto bukti servis</strong> di Langkah 2 sebelum menyelesaikan pengerjaan.
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('technician.order.complete', $order->id) }}">
                    @csrf
                    <button type="submit"
                            @if($order->photos->isEmpty() || $order->status !== 'dikerjakan') disabled @endif
                            onclick="return confirm('Apakah Anda yakin seluruh pengerjaan servis AC di lokasi masjid telah selesai dikerjakan?');"
                            class="w-full min-h-[54px] py-3.5 px-6 bg-primary hover:bg-primary-hover disabled:opacity-40 disabled:cursor-not-allowed text-white font-extrabold text-base sm:text-lg rounded-xl shadow-xs transition-colors flex items-center justify-center cursor-pointer">
                        Konfirmasi Pekerjaan Selesai &amp; Kunci Data
                    </button>
                </form>
            @else
                <div class="p-4 rounded-2xl bg-[#DCFCE7] border-2 border-[#86EFAC] space-y-3">
                    <p class="text-base font-bold text-[#14532D]">
                        &#10003; Pekerjaan servis di lokasi selesai dan data lembar kerja telah dikunci.
                    </p>
                    <a href="{{ route('technician.order.payment', $order->id) }}"
                       class="w-full min-h-[54px] py-3.5 px-6 bg-primary hover:bg-primary-hover text-white font-extrabold text-base sm:text-lg rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2">
                        <span>Lanjut ke Pelaporan Pembayaran</span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            @endif
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- MODAL EDIT FOTO BUKTI SERVIS                                              -->
    <!-- ========================================================================= -->
    <div x-show="showEditPhotoModal" x-cloak role="dialog" aria-modal="true" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showEditPhotoModal = false" class="bg-surface rounded-2xl border-2 border-primary-border max-w-md w-full p-5 sm:p-6 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between gap-3 pb-3 border-b border-primary-border">
                <h3 class="text-xl font-extrabold text-text-main">Perbarui Foto Bukti</h3>
                <button type="button" @click="showEditPhotoModal = false" aria-label="Tutup modal edit foto" class="tap-target inline-flex items-center justify-center rounded-xl bg-bg-main hover:bg-primary-subtle text-text-main text-2xl font-bold cursor-pointer shrink-0">&times;</button>
            </div>

            <form :action="'/teknisi/order/foto/' + editPhotoId" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-text-main">Kategori Foto</label>
                    <select name="photo_type" x-model="editPhotoType" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main font-semibold outline-none focus:border-primary bg-surface">
                        <option value="sebelum">Sebelum Servis</option>
                        <option value="sesudah">Sesudah Servis</option>
                        <option value="sparepart">Sparepart / Komponen</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-text-main">Keterangan Foto</label>
                    <input type="text" name="caption" x-model="editPhotoCaption" placeholder="Contoh: AC Shaf Depan Kanan" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-text-main">Ganti File Gambar (Opsional)</label>
                    <input type="file" name="photo" accept="image/*" class="w-full text-sm text-text-muted file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-primary-subtle file:text-primary hover:file:bg-primary-border/60">
                    <span class="text-sm text-text-muted block">Pilih file baru hanya jika ingin mengganti foto yang ada.</span>
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-primary-border">
                    <button type="button" @click="showEditPhotoModal = false" class="min-h-[48px] px-5 py-2.5 rounded-xl border border-primary-border bg-bg-main text-text-main text-base font-bold hover:bg-primary-subtle cursor-pointer">Batal</button>
                    <button type="submit" class="min-h-[48px] px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-base font-bold cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL LIGHTBOX PRATINJAU FOTO                                             -->
    <!-- ========================================================================= -->
    <div x-show="activeLightboxUrl" x-cloak role="dialog" aria-modal="true" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4" @click.self="activeLightboxUrl = ''">
        <div class="bg-surface rounded-2xl border border-primary-border max-w-xl w-full overflow-hidden shadow-2xl">
            <div class="px-4 py-3 bg-bg-main border-b border-primary-border flex items-center justify-between gap-3">
                <span class="text-base font-bold text-text-main truncate" x-text="lightboxCaption || 'Foto Bukti Servis'"></span>
                <button type="button" @click="activeLightboxUrl = ''" aria-label="Tutup foto" class="tap-target inline-flex items-center justify-center rounded-xl bg-surface border border-primary-border text-text-main text-2xl font-bold cursor-pointer shrink-0">&times;</button>
            </div>
            <div class="p-3 bg-black flex items-center justify-center">
                <img :src="activeLightboxUrl" alt="Foto Penuh" class="max-h-[75vh] object-contain w-auto rounded-lg">
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL EDIT SUKU CADANG / SPAREPART                                        -->
    <!-- ========================================================================= -->
    <div x-show="showEditSparepartModal" x-cloak role="dialog" aria-modal="true" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showEditSparepartModal = false" class="bg-surface rounded-2xl border-2 border-primary-border max-w-md w-full p-5 sm:p-6 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between gap-3 pb-3 border-b border-primary-border">
                <h3 class="text-xl font-extrabold text-text-main">Perbarui Suku Cadang</h3>
                <button type="button" @click="showEditSparepartModal = false" aria-label="Tutup modal edit suku cadang" class="tap-target inline-flex items-center justify-center rounded-xl bg-bg-main hover:bg-primary-subtle text-text-main text-2xl font-bold cursor-pointer shrink-0">&times;</button>
            </div>

            <form :action="'/teknisi/order/sparepart/' + editSparepartId" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-text-main">Nama Suku Cadang / Tindakan <span class="text-[#B91C1C]">*</span></label>
                    <input type="text" name="item_name" x-model="editSparepartName" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-base font-bold text-text-main">Jumlah <span class="text-[#B91C1C]">*</span></label>
                        <input type="number" name="quantity" x-model="editSparepartQty" min="1" max="999" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main font-mono font-bold outline-none focus:border-primary bg-surface">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-base font-bold text-text-main">Harga (Rp) <span class="text-[#B91C1C]">*</span></label>
                        <input type="number" name="price" x-model="editSparepartPrice" step="1000" min="0" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main font-mono font-bold outline-none focus:border-primary bg-surface">
                    </div>
                </div>

                <div class="p-3.5 bg-primary-subtle rounded-xl border border-primary-border flex justify-between items-center text-primary">
                    <span class="text-base font-bold">Subtotal Item</span>
                    <span class="font-extrabold font-mono text-lg" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format((parseInt(editSparepartQty) || 0) * (parseFloat(editSparepartPrice) || 0))"></span>
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-primary-border">
                    <button type="button" @click="showEditSparepartModal = false" class="min-h-[48px] px-5 py-2.5 rounded-xl border border-primary-border bg-bg-main text-text-main text-base font-bold hover:bg-primary-subtle cursor-pointer">Batal</button>
                    <button type="submit" class="min-h-[48px] px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-base font-bold cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL TAMBAH UNIT AC OLEH TEKNISI                                         -->
    <!-- ========================================================================= -->
    <div x-show="showAddAcModal" x-cloak role="dialog" aria-modal="true" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showAddAcModal = false" class="bg-surface rounded-2xl border-2 border-primary-border max-w-md w-full p-5 sm:p-6 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between gap-3 pb-3 border-b border-primary-border">
                <h3 class="text-xl font-extrabold text-text-main">Catat Unit AC Baru</h3>
                <button type="button" @click="showAddAcModal = false" aria-label="Tutup modal tambah AC" class="tap-target inline-flex items-center justify-center rounded-xl bg-bg-main hover:bg-primary-subtle text-text-main text-2xl font-bold cursor-pointer shrink-0">&times;</button>
            </div>

            <form action="{{ route('technician.order.ac.store', $order->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-text-main">Nama Ruangan / Penempatan <span class="text-[#B91C1C]">*</span></label>
                    <input type="text" name="room_name" required placeholder="Contoh: Shaf Utama Depan, Ruang DKM" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-base font-bold text-text-main">Kapasitas / PK <span class="text-[#B91C1C]">*</span></label>
                        <select name="ac_type" required class="w-full min-h-[50px] px-3.5 py-3 rounded-xl border-2 border-primary-border text-base text-text-main font-semibold outline-none focus:border-primary bg-surface">
                            <option value="0.5 PK">0.5 PK (1/2 PK)</option>
                            <option value="0.75 PK">0.75 PK (3/4 PK)</option>
                            <option value="1 PK" selected>1 PK</option>
                            <option value="1.5 PK">1.5 PK</option>
                            <option value="2 PK">2 PK</option>
                            <option value="2.5 PK">2.5 PK</option>
                            <option value="3 PK">3 PK</option>
                            <option value="5 PK">5 PK (Standing / Cassette)</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-base font-bold text-text-main">Jumlah Unit <span class="text-[#B91C1C]">*</span></label>
                        <input type="number" name="quantity" min="1" max="999" value="1" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main font-mono font-bold outline-none focus:border-primary bg-surface">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-text-main">Merek AC (Opsional)</label>
                    <input type="text" name="brand" placeholder="Contoh: Daikin, Panasonic, Sharp" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface">
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-primary-border">
                    <button type="button" @click="showAddAcModal = false" class="min-h-[48px] px-5 py-2.5 rounded-xl border border-primary-border bg-bg-main text-text-main text-base font-bold hover:bg-primary-subtle cursor-pointer">Batal</button>
                    <button type="submit" class="min-h-[48px] px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-base font-bold cursor-pointer">Simpan Unit AC</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL EDIT UNIT AC OLEH TEKNISI                                           -->
    <!-- ========================================================================= -->
    <div x-show="showEditAcModal" x-cloak role="dialog" aria-modal="true" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div @click.away="showEditAcModal = false" class="bg-surface rounded-2xl border-2 border-primary-border max-w-md w-full p-5 sm:p-6 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between gap-3 pb-3 border-b border-primary-border">
                <h3 class="text-xl font-extrabold text-text-main">Perbarui Data Unit AC</h3>
                <button type="button" @click="showEditAcModal = false" aria-label="Tutup modal edit AC" class="tap-target inline-flex items-center justify-center rounded-xl bg-bg-main hover:bg-primary-subtle text-text-main text-2xl font-bold cursor-pointer shrink-0">&times;</button>
            </div>

            <form :action="'/teknisi/order/{{ $order->id }}/ac/' + editAcId" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-text-main">Nama Ruangan / Penempatan <span class="text-[#B91C1C]">*</span></label>
                    <input type="text" name="room_name" x-model="editAcRoom" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-base font-bold text-text-main">Kapasitas / PK <span class="text-[#B91C1C]">*</span></label>
                        <select name="ac_type" x-model="editAcType" required class="w-full min-h-[50px] px-3.5 py-3 rounded-xl border-2 border-primary-border text-base text-text-main font-semibold outline-none focus:border-primary bg-surface">
                            <option value="0.5 PK">0.5 PK (1/2 PK)</option>
                            <option value="0.75 PK">0.75 PK (3/4 PK)</option>
                            <option value="1 PK">1 PK</option>
                            <option value="1.5 PK">1.5 PK</option>
                            <option value="2 PK">2 PK</option>
                            <option value="2.5 PK">2.5 PK</option>
                            <option value="3 PK">3 PK</option>
                            <option value="5 PK">5 PK (Standing / Cassette)</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-base font-bold text-text-main">Jumlah Unit <span class="text-[#B91C1C]">*</span></label>
                        <input type="number" name="quantity" x-model="editAcQty" min="1" max="999" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main font-mono font-bold outline-none focus:border-primary bg-surface">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-text-main">Merek AC (Opsional)</label>
                    <input type="text" name="brand" x-model="editAcBrand" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface">
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-primary-border">
                    <button type="button" @click="showEditAcModal = false" class="min-h-[48px] px-5 py-2.5 rounded-xl border border-primary-border bg-bg-main text-text-main text-base font-bold hover:bg-primary-subtle cursor-pointer">Batal</button>
                    <button type="submit" class="min-h-[48px] px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-base font-bold cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Script Kompresi & Pratinjau Foto di Browser (Client-side HTML5 Canvas — KONTRAK KRITIS) -->
<script>
function technicianWork() {
    return {
        openStep1: {{ !$step1Done ? 'true' : 'false' }},
        openStep2: {{ $step1Done && !$isWorkCompleted ? 'true' : 'false' }},
        openStep3: {{ $step1Done && $step2Done && !$isWorkCompleted ? 'true' : 'false' }},
        openStep4: {{ $isWorkCompleted || ($step1Done && $step2Done) ? 'true' : 'false' }},

        focusStep(num) {
            if (num === 1) this.openStep1 = true;
            if (num === 2) this.openStep2 = true;
            if (num === 3) this.openStep3 = true;
            if (num === 4) this.openStep4 = true;
            const el = document.getElementById('step-' + num);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        showAddAcModal: false,
        showEditAcModal: false,
        editAcId: null,
        editAcRoom: '',
        editAcType: '1 PK',
        editAcBrand: '',
        editAcQty: 1,

        openEditAc(id, room, type, brand, qty) {
            this.editAcId = id;
            this.editAcRoom = room;
            this.editAcType = type;
            this.editAcBrand = brand;
            this.editAcQty = qty;
            this.showEditAcModal = true;
        },

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
