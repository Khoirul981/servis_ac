@extends('layouts.app', ['title' => 'Formulir Pemesanan Servis AC Masjid & Musholla'])

@section('content')
@php
    $orderFormErrors = collect($errors->getMessages())->except('track_order_number');
    $step1ErrorKeys = ['is_new_masjid', 'masjid_id', 'new_masjid_name', 'address', 'pic_name', 'pic_phone'];
    $step2ErrorKeys = ['service_items', 'ac_count', 'dkm_complaint'];
    $step3ErrorKeys = ['service_date', 'service_time', 'notes'];

    $initialStep = 1;
    if ($orderFormErrors->isNotEmpty()) {
        $errorKeys = $orderFormErrors->keys()->all();
        $hasStep1Err = collect($errorKeys)->contains(fn ($k) => in_array($k, $step1ErrorKeys, true));
        $hasStep2Err = collect($errorKeys)->contains(fn ($k) => in_array($k, $step2ErrorKeys, true) || str_starts_with($k, 'service_items.'));
        $hasStep3Err = collect($errorKeys)->contains(fn ($k) => in_array($k, $step3ErrorKeys, true));

        if ($hasStep1Err) {
            $initialStep = 1;
        } elseif ($hasStep2Err) {
            $initialStep = 2;
        } elseif ($hasStep3Err) {
            $initialStep = 3;
        }
    }

    $oldServiceItems = old('service_items');
    if (is_array($oldServiceItems) && count($oldServiceItems) > 0) {
        $initialServiceRows = array_values(array_map(function ($item) {
            return [
                'room_name' => (string) ($item['room_name'] ?? ''),
                'ac_type' => (string) ($item['ac_type'] ?? '1 PK'),
                'service_category' => (string) ($item['service_category'] ?? 'Cuci Rutin'),
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                'ac_unit_id' => ! empty($item['ac_unit_id']) ? (int) $item['ac_unit_id'] : null,
            ];
        }, $oldServiceItems));
    } else {
        $initialServiceRows = [
            [
                'room_name' => '',
                'ac_type' => '1 PK',
                'service_category' => 'Cuci Rutin',
                'quantity' => 1,
                'ac_unit_id' => null,
            ],
        ];
    }

    $defaultServiceDate = old('service_date', now()->addDay()->toDateString());
    $defaultServiceTime = old('service_time', 'Pagi (08:30 - 11:30 WIB)');
    $defaultIsNewMasjid = (string) old('is_new_masjid', '0');

    $serviceTimeOptions = [
        [
            'value' => 'Pagi (08:30 - 11:30 WIB)',
            'title' => 'Pagi',
            'time' => '08:30 - 11:30 WIB',
            'desc' => 'Sebelum waktu Dzuhur',
        ],
        [
            'value' => "Siang Ba'da Dzuhur (13:00 - 15:00 WIB)",
            'title' => "Siang Ba'da Dzuhur",
            'time' => '13:00 - 15:00 WIB',
            'desc' => 'Setelah sholat Dzuhur',
        ],
        [
            'value' => "Sore Ba'da Ashar (16:00 - 17:30 WIB)",
            'title' => "Sore Ba'da Ashar",
            'time' => '16:00 - 17:30 WIB',
            'desc' => 'Setelah sholat Ashar',
        ],
        [
            'value' => 'Kapan Saja / Fleksibel',
            'title' => 'Kapan Saja / Fleksibel',
            'time' => 'Menyesuaikan jadwal teknisi',
            'desc' => 'Admin akan koordinasi via WhatsApp',
        ],
    ];
@endphp

<div x-data="orderWizard()"
     x-init="init()"
     :class="(currentStep === 2 || currentStep === 3) ? 'pb-28 lg:pb-6' : 'pb-6'"
     class="space-y-5">

    <!-- Hero Ringkas Berwibawa -->
    <section class="rounded-2xl bg-gradient-to-br from-primary to-primary-light text-white p-5 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="space-y-1.5">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-accent-gold/60 text-sm font-semibold text-white">
                    <span class="w-2 h-2 rounded-full bg-accent-gold shrink-0"></span>
                    Layanan Khusus Rumah Ibadah
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-snug">
                    Pemesanan Servis AC Masjid
                </h1>
                <p class="text-sm sm:text-base text-white/90 leading-relaxed max-w-2xl">
                    Formulir perawatan pendingin ruang ibadah untuk Pengurus DKM dan Marbot. Pelunasan dilakukan setelah servis beres.
                </p>
            </div>
        </div>
    </section>

    <!-- Panel Lipat Lacak Pesanan -->
    <section class="bg-surface rounded-2xl border-2 border-primary-border shadow-sm overflow-hidden"
             x-data="{ openTrack: {{ $errors->has('track_order_number') || request('order_number') ? 'true' : 'false' }} }">
        <button type="button"
                @click="openTrack = !openTrack"
                :aria-expanded="openTrack.toString()"
                class="w-full min-h-[60px] px-4 sm:px-5 py-3.5 flex items-center justify-between gap-3 text-left hover:bg-primary-subtle/50 transition-colors cursor-pointer">
            <div class="flex items-center gap-3.5 min-w-0">
                <span class="w-11 h-11 rounded-xl bg-primary-subtle text-primary border border-primary-border flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </span>
                <span class="min-w-0">
                    <span class="block text-base font-bold text-text-main leading-tight">Sudah Pernah Memesan?</span>
                    <span class="block text-sm text-text-muted mt-0.5">Lacak status pengerjaan &amp; bukti pembayaran</span>
                </span>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-primary-subtle text-primary text-sm font-bold shrink-0">
                <span x-text="openTrack ? 'Tutup' : 'Lacak Status'"></span>
                <svg class="w-4 h-4 transition-transform duration-150" :class="openTrack ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </span>
        </button>

        <div x-show="openTrack" x-cloak class="px-4 sm:px-5 pb-5 pt-4 border-t border-primary-border bg-bg-main/60 space-y-3">
            <form method="GET" action="{{ route('order.track') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label for="track_order_number_input" class="sr-only">Nomor Registrasi Pesanan</label>
                    <input type="text"
                           id="track_order_number_input"
                           name="order_number"
                           value="{{ old('track_order_number', request('order_number')) }}"
                           required
                           placeholder="Contoh: SO-{{ now()->format('Ymd') }}-001"
                           class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 {{ $errors->has('track_order_number') ? 'border-[#B91C1C] bg-[#FEE2E2]/30' : 'border-primary-border bg-surface' }} text-base text-text-main font-mono placeholder-gray-400 focus:outline-none focus:border-primary">
                </div>
                <button type="submit"
                        class="min-h-[50px] px-6 py-3 bg-primary hover:bg-primary-hover text-white rounded-xl text-base font-bold cursor-pointer transition-colors whitespace-nowrap shadow-sm">
                    Lihat Pesanan
                </button>
            </form>
            @error('track_order_number')
                <div class="p-3.5 rounded-xl bg-[#FEE2E2] border border-[#FCA5A5] text-[#7F1D1D] text-sm font-semibold">
                    {{ $message }}
                </div>
            @enderror
        </div>
    </section>

    <!-- Ringkasan Error Validasi dari Server (jika ada) -->
    @if($orderFormErrors->isNotEmpty())
        <div role="alert" class="p-4 sm:p-5 rounded-2xl bg-[#FEE2E2] border-2 border-[#FCA5A5] text-[#7F1D1D] space-y-2">
            <div class="flex items-center gap-2.5">
                <svg class="w-6 h-6 shrink-0 text-[#B91C1C]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <h2 class="text-base font-bold">Mohon periksa kembali isian formulir Anda:</h2>
            </div>
            <ul class="list-disc list-inside space-y-1 text-sm sm:text-base font-medium pl-1">
                @foreach($orderFormErrors->flatten() as $errMsg)
                    <li>{{ $errMsg }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Layout Utama: 2 Kolom di Desktop, 1 Kolom di HP -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Kolom Kiri: Indikator Langkah + Form 3 Tahap -->
        <div class="lg:col-span-8 space-y-5">

            <!-- Indikator 3 Langkah (Stepper) -->
            <nav aria-label="Tahap Pemesanan" class="bg-surface rounded-2xl border border-primary-border p-3 sm:p-4 shadow-sm">
                <ol class="grid grid-cols-3 gap-2 sm:gap-3">
                    <!-- Langkah 1 -->
                    <li>
                        <button type="button"
                                @click="goToStep(1)"
                                :aria-current="currentStep === 1 ? 'step' : false"
                                :class="currentStep === 1
                                    ? 'bg-primary text-white border-primary shadow-sm'
                                    : (currentStep > 1 ? 'bg-primary-subtle text-primary border-primary-border hover:bg-primary-subtle/80' : 'bg-bg-main text-text-muted border-gray-200 hover:bg-primary-subtle/40')"
                                class="w-full min-h-[68px] sm:min-h-[60px] p-2.5 sm:px-3.5 sm:py-3 rounded-xl border-2 flex flex-col sm:flex-row items-center justify-center sm:justify-start gap-1.5 sm:gap-2.5 transition-colors cursor-pointer text-center sm:text-left">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center font-extrabold text-base shrink-0"
                                  :class="currentStep === 1 ? 'bg-white text-primary' : (currentStep > 1 ? 'bg-primary text-white' : 'bg-white text-text-muted border border-gray-300')">
                                <span x-show="currentStep <= 1">1</span>
                                <svg x-show="currentStep > 1" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            <span class="text-sm font-bold leading-tight">
                                <span class="hidden sm:inline">Data Masjid &amp; Kontak</span>
                                <span class="sm:hidden">Masjid &amp; Kontak</span>
                            </span>
                        </button>
                    </li>

                    <!-- Langkah 2 -->
                    <li>
                        <button type="button"
                                @click="goToStep(2)"
                                :aria-current="currentStep === 2 ? 'step' : false"
                                :class="currentStep === 2
                                    ? 'bg-primary text-white border-primary shadow-sm'
                                    : (currentStep > 2 ? 'bg-primary-subtle text-primary border-primary-border hover:bg-primary-subtle/80' : 'bg-bg-main text-text-muted border-gray-200 hover:bg-primary-subtle/40')"
                                class="w-full min-h-[68px] sm:min-h-[60px] p-2.5 sm:px-3.5 sm:py-3 rounded-xl border-2 flex flex-col sm:flex-row items-center justify-center sm:justify-start gap-1.5 sm:gap-2.5 transition-colors cursor-pointer text-center sm:text-left">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center font-extrabold text-base shrink-0"
                                  :class="currentStep === 2 ? 'bg-white text-primary' : (currentStep > 2 ? 'bg-primary text-white' : 'bg-white text-text-muted border border-gray-300')">
                                <span x-show="currentStep <= 2">2</span>
                                <svg x-show="currentStep > 2" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            <span class="text-sm font-bold leading-tight">
                                <span class="hidden sm:inline">Unit &amp; Keluhan AC</span>
                                <span class="sm:hidden">Unit &amp; Keluhan</span>
                            </span>
                        </button>
                    </li>

                    <!-- Langkah 3 -->
                    <li>
                        <button type="button"
                                @click="goToStep(3)"
                                :aria-current="currentStep === 3 ? 'step' : false"
                                :class="currentStep === 3
                                    ? 'bg-primary text-white border-primary shadow-sm'
                                    : 'bg-bg-main text-text-muted border-gray-200 hover:bg-primary-subtle/40'"
                                class="w-full min-h-[68px] sm:min-h-[60px] p-2.5 sm:px-3.5 sm:py-3 rounded-xl border-2 flex flex-col sm:flex-row items-center justify-center sm:justify-start gap-1.5 sm:gap-2.5 transition-colors cursor-pointer text-center sm:text-left">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center font-extrabold text-base shrink-0"
                                  :class="currentStep === 3 ? 'bg-white text-primary' : 'bg-white text-text-muted border border-gray-300'">
                                3
                            </span>
                            <span class="text-sm font-bold leading-tight">
                                <span class="hidden sm:inline">Jadwal &amp; Konfirmasi</span>
                                <span class="sm:hidden">Jadwal &amp; Kirim</span>
                            </span>
                        </button>
                    </li>
                </ol>
            </nav>

            <!-- SATU FORM UTUH UNTUK SEMUA LANGKAH (x-show, BUKAN x-if) -->
            <form id="orderForm"
                  method="POST"
                  action="{{ route('order.store') }}"
                  novalidate
                  @submit="handleFormSubmit($event)"
                  class="space-y-5">
                @csrf

                <!-- Kolom Jebakan Honeypot Anti-Spam (WAJIB UTUH & TERSEMBUNYI) -->
                <div style="display:none;" aria-hidden="true">
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </div>

                <!-- Hidden Inputs Wajib Kontrak Backend -->
                <input type="hidden" name="is_new_masjid" :value="isNewMasjid">
                <input type="hidden" name="masjid_id" :value="isNewMasjid === '0' ? selectedMasjidId : ''">
                <input type="hidden" name="dkm_complaint" :value="complaintSummary">
                <input type="hidden" name="ac_count" :value="totalUnits">

                <!-- =============================================================== -->
                <!-- LANGKAH 1: DATA MASJID & KONTAK PENGURUS                        -->
                <!-- =============================================================== -->
                <div x-show="currentStep === 1" class="bg-surface rounded-2xl border border-primary-border p-5 sm:p-7 shadow-sm space-y-6">
                    <div class="pb-3 border-b border-primary-border/70">
                        <span class="text-sm font-bold text-primary uppercase tracking-wider">Langkah 1 dari 3</span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-text-main mt-0.5">
                            Data Masjid &amp; Kontak Pengurus
                        </h2>
                        <p class="text-sm text-text-muted mt-1">
                            Pilih masjid yang sudah pernah terdaftar atau daftarkan masjid baru Anda.
                        </p>
                    </div>

                    <!-- Mode Pilih Masjid Terdaftar vs Masjid Baru -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-1.5 bg-bg-main border border-primary-border rounded-2xl">
                        <button type="button"
                                @click="switchMasjidMode('0')"
                                :class="isNewMasjid === '0' ? 'bg-primary text-white shadow-sm font-bold' : 'bg-surface text-text-main hover:bg-primary-subtle font-semibold border border-primary-border/60'"
                                class="min-h-[52px] px-4 py-3 rounded-xl text-base transition-colors cursor-pointer flex items-center justify-center text-center">
                            Cari Masjid Terdaftar
                        </button>
                        <button type="button"
                                @click="switchMasjidMode('1')"
                                :class="isNewMasjid === '1' ? 'bg-primary text-white shadow-sm font-bold' : 'bg-surface text-text-main hover:bg-primary-subtle font-semibold border border-primary-border/60'"
                                class="min-h-[52px] px-4 py-3 rounded-xl text-base transition-colors cursor-pointer flex items-center justify-center text-center">
                            + Daftarkan Masjid Baru
                        </button>
                    </div>

                    <!-- Input Pencarian Masjid Terdaftar -->
                    <div x-show="isNewMasjid === '0'" class="space-y-3">
                        <div class="relative space-y-2">
                            <label for="masjid_search_input" class="block text-base font-bold text-text-main">
                                Cari Nama Masjid atau Musholla <span class="text-[#B91C1C]">*</span>
                            </label>
                            <div class="relative">
                                <input type="text"
                                       id="masjid_search_input"
                                       x-model="masjidQuery"
                                       @focus="showDropdown = true; searchMasjid()"
                                       @input="onMasjidSearchInput()"
                                       autocomplete="off"
                                       placeholder="Ketik nama masjid (contoh: Al-Muhajirin)"
                                       class="w-full min-h-[52px] pl-4 pr-12 py-3 bg-surface border-2 border-primary-border rounded-xl text-text-main text-base placeholder-gray-400 focus:outline-none focus:border-primary">
                                <button type="button"
                                        x-show="masjidQuery.length > 0"
                                        x-cloak
                                        @click="clearSelectedMasjid()"
                                        aria-label="Hapus pencarian masjid"
                                        class="absolute right-1.5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-lg text-text-muted hover:text-text-main hover:bg-bg-main flex items-center justify-center text-xl font-bold cursor-pointer">
                                    &times;
                                </button>
                            </div>
                            <p class="text-sm text-text-muted">
                                Sentuh nama masjid dari daftar yang muncul agar alamat dan kontak terisi otomatis.
                            </p>

                            <!-- Daftar Rekomendasi Autocomplete Lega & Ramah Jempol -->
                            <div x-show="showDropdown"
                                 x-cloak
                                 @click.away="showDropdown = false"
                                 class="absolute z-30 left-0 right-0 mt-1 bg-surface border-2 border-primary-border rounded-2xl shadow-lg max-h-72 overflow-y-auto divide-y divide-primary-border/60">
                                <template x-for="item in filteredMasjids" :key="item.id">
                                    <button type="button"
                                            @click="selectMasjid(item)"
                                            class="w-full min-h-[60px] text-left px-4 py-3.5 hover:bg-primary-subtle transition-colors cursor-pointer flex items-start justify-between gap-3">
                                        <span class="min-w-0">
                                            <span class="block text-base font-bold text-text-main leading-snug" x-text="item.name"></span>
                                            <span class="block text-sm text-text-muted mt-1 leading-snug" x-text="item.address"></span>
                                        </span>
                                        <span x-show="item.ac_units && item.ac_units.length > 0"
                                              class="shrink-0 px-2.5 py-1 rounded-lg bg-primary-subtle text-primary border border-primary-border text-sm font-bold"
                                              x-text="item.ac_units.reduce((acc, u) => acc + (parseInt(u.quantity) || 1), 0) + ' AC'"></span>
                                    </button>
                                </template>

                                <template x-if="filteredMasjids.length === 0">
                                    <div class="p-4 text-center space-y-3">
                                        <p class="text-sm text-text-muted">
                                            Masjid "<strong class="text-text-main" x-text="masjidQuery"></strong>" belum ditemukan di daftar.
                                        </p>
                                        <button type="button"
                                                @click="useQueryAsNewMasjid()"
                                                class="w-full min-h-[48px] px-4 py-2.5 rounded-xl bg-primary-subtle hover:bg-primary text-primary hover:text-white border-2 border-primary-border font-bold text-sm transition-colors cursor-pointer">
                                            + Daftarkan sebagai Masjid Baru
                                        </button>
                                    </div>
                                </template>
                            </div>

                            @error('masjid_id')
                                <p class="text-sm font-semibold text-[#B91C1C]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kartu Konfirmasi Hijau Lembut Setelah Masjid Dipilih -->
                        <div x-show="selectedMasjidId" x-cloak class="p-4 sm:p-5 rounded-2xl bg-primary-subtle border-2 border-primary-border space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <span class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </span>
                                    <div>
                                        <span class="block text-sm font-bold text-primary uppercase tracking-wide">Masjid Terpilih</span>
                                        <strong class="block text-lg font-extrabold text-text-main mt-0.5" x-text="selectedMasjidName"></strong>
                                    </div>
                                </div>
                                <span x-show="selectedMasjidUnits.length > 0"
                                      class="shrink-0 px-3 py-1 rounded-lg bg-primary text-white text-sm font-bold"
                                      x-text="registeredTotalUnits + ' Unit AC'"></span>
                            </div>

                            <template x-if="selectedMasjidUnits.length > 0">
                                <div class="space-y-2 pt-1">
                                    <div class="divide-y divide-primary-border/60 bg-surface rounded-xl border border-primary-border overflow-hidden">
                                        <template x-for="unit in selectedMasjidUnits" :key="unit.id">
                                            <div class="px-3.5 py-2.5 flex items-center justify-between gap-2 text-sm">
                                                <div>
                                                    <strong class="text-text-main block font-bold" x-text="unit.room_name"></strong>
                                                    <span class="text-text-muted" x-text="(unit.brand ? unit.brand + ' • ' : '') + unit.ac_type"></span>
                                                </div>
                                                <span class="px-2.5 py-1 rounded-lg bg-primary-subtle text-primary font-bold whitespace-nowrap"
                                                      x-text="(unit.quantity || 1) + ' Unit'"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <p class="text-sm text-primary font-medium">
                                        Data AC di atas bisa langsung Anda pakai otomatis di Langkah 2.
                                    </p>
                                </div>
                            </template>

                            <template x-if="selectedMasjidUnits.length === 0">
                                <p class="text-sm text-text-muted bg-surface/80 rounded-xl p-3 border border-primary-border/70">
                                    Belum ada catatan inventaris AC untuk masjid ini. Teknisi akan membantu mendata spesifikasi AC saat di lokasi.
                                </p>
                            </template>
                        </div>
                    </div>

                    <!-- Input Nama Masjid Baru -->
                    <div x-show="isNewMasjid === '1'" x-cloak class="space-y-2">
                        <label for="new_masjid_name" class="block text-base font-bold text-text-main">
                            Nama Masjid atau Musholla Baru <span class="text-[#B91C1C]">*</span>
                        </label>
                        <input type="text"
                               name="new_masjid_name"
                               id="new_masjid_name"
                               x-model="newMasjidName"
                               placeholder="Contoh: Masjid Jamie Al-Barkah"
                               class="w-full min-h-[52px] px-4 py-3 bg-surface border-2 {{ $errors->has('new_masjid_name') ? 'border-[#B91C1C]' : 'border-primary-border' }} rounded-xl text-text-main text-base placeholder-gray-400 focus:outline-none focus:border-primary">
                        @error('new_masjid_name')
                            <p class="text-sm font-semibold text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alamat Lengkap -->
                    <div class="space-y-2">
                        <label for="address" class="block text-base font-bold text-text-main">
                            Alamat Lengkap Masjid <span class="text-[#B91C1C]">*</span>
                        </label>
                        <textarea name="address"
                                  id="address"
                                  rows="3"
                                  x-model="address"
                                  placeholder="Jalan, nomor, RT/RW, kelurahan, kecamatan, kota (dan patokan)"
                                  class="w-full px-4 py-3 bg-surface border-2 {{ $errors->has('address') ? 'border-[#B91C1C]' : 'border-primary-border' }} rounded-xl text-text-main text-base placeholder-gray-400 focus:outline-none focus:border-primary"></textarea>
                        @error('address')
                            <p class="text-sm font-semibold text-[#B91C1C]">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kontak Pengurus -->
                    <div class="pt-4 border-t border-primary-border/70 space-y-4">
                        <h3 class="text-lg font-bold text-text-main">
                            Kontak Pengurus (DKM / Marbot)
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label for="pic_name" class="block text-base font-bold text-text-main">
                                    Nama Pengurus / Pemesan <span class="text-[#B91C1C]">*</span>
                                </label>
                                <input type="text"
                                       name="pic_name"
                                       id="pic_name"
                                       x-model="picName"
                                       placeholder="Contoh: Bapak H. Bambang"
                                       class="w-full min-h-[52px] px-4 py-3 bg-surface border-2 {{ $errors->has('pic_name') ? 'border-[#B91C1C]' : 'border-primary-border' }} rounded-xl text-text-main text-base placeholder-gray-400 focus:outline-none focus:border-primary">
                                @error('pic_name')
                                    <p class="text-sm font-semibold text-[#B91C1C]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="pic_phone" class="block text-base font-bold text-text-main">
                                    Nomor WhatsApp Aktif <span class="text-[#B91C1C]">*</span>
                                </label>
                                <input type="tel"
                                       name="pic_phone"
                                       id="pic_phone"
                                       x-model="picPhone"
                                       inputmode="tel"
                                       placeholder="Contoh: 081234567890"
                                       class="w-full min-h-[52px] px-4 py-3 bg-surface border-2 {{ $errors->has('pic_phone') ? 'border-[#B91C1C]' : 'border-primary-border' }} rounded-xl text-text-main text-base font-mono placeholder-gray-400 focus:outline-none focus:border-primary">
                                <p class="text-sm text-text-muted">Awali dengan 08 (contoh: 0812xxxx).</p>
                                @error('pic_phone')
                                    <p class="text-sm font-semibold text-[#B91C1C]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Pesan Panduan Validasi Langkah 1 -->
                    <div x-show="step1Error" x-cloak role="alert" class="p-4 rounded-xl bg-[#FEF3C7] border-2 border-[#FCD34D] text-[#92400E] text-base font-semibold flex items-start gap-2.5">
                        <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <span x-text="step1Error"></span>
                    </div>

                    <!-- Tombol Lanjut ke Langkah 2 -->
                    <div class="pt-2">
                        <button type="button"
                                @click="goToStep(2)"
                                class="w-full min-h-[54px] px-6 py-3.5 bg-primary hover:bg-primary-hover text-white font-bold text-base sm:text-lg rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                            <span>Lanjut ke Langkah 2: Unit &amp; Keluhan AC</span>
                            <span aria-hidden="true">&rarr;</span>
                        </button>
                    </div>
                </div>

                <!-- =============================================================== -->
                <!-- LANGKAH 2: UNIT & KELUHAN AC (MULTI-ITEM)                       -->
                <!-- =============================================================== -->
                <div x-show="currentStep === 2" x-cloak class="space-y-5">
                    <div class="bg-surface rounded-2xl border border-primary-border p-5 sm:p-7 shadow-sm space-y-5">
                        <div class="pb-3 border-b border-primary-border/70">
                            <span class="text-sm font-bold text-primary uppercase tracking-wider">Langkah 2 dari 3</span>
                            <h2 class="text-xl sm:text-2xl font-extrabold text-text-main mt-0.5">
                                Unit &amp; Keluhan AC
                            </h2>
                            <p class="text-sm text-text-muted mt-1">
                                Anda dapat menambahkan lebih dari 1 jenis kapasitas PK atau keluhan dalam satu pesanan.
                            </p>
                        </div>

                        <!-- Shortcut Pakai Data AC Terdaftar -->
                        <div x-show="isNewMasjid === '0' && selectedMasjidUnits.length > 0"
                             x-cloak
                             class="p-4 sm:p-5 rounded-2xl bg-primary-subtle border-2 border-primary-border space-y-3">
                            <div class="space-y-1">
                                <p class="text-base font-bold text-primary">
                                    Data AC Masjid Ini Sudah Tercatat (<span x-text="registeredTotalUnits"></span> Unit)
                                </p>
                                <p class="text-sm text-text-muted">
                                    Tekan tombol di bawah untuk langsung mengisi seluruh daftar AC masjid secara otomatis, lalu Anda tinggal sesuaikan keluhan atau jumlahnya.
                                </p>
                            </div>
                            <button type="button"
                                    @click="importRegisteredUnits()"
                                    class="w-full sm:w-auto min-h-[48px] px-5 py-3 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-base transition-colors cursor-pointer inline-flex items-center justify-center gap-2 shadow-sm">
                                <span>Pakai Data AC Masjid Ini (Otomatis)</span>
                                <span aria-hidden="true">&rarr;</span>
                            </button>
                            <p x-show="importedNotice" x-cloak class="text-sm font-bold text-[#15803D]">
                                &#10003; Data unit AC terdaftar berhasil dimasukkan ke formulir di bawah.
                            </p>
                        </div>

                        <!-- Daftar Kartu Item AC -->
                        <div class="space-y-4">
                            <template x-for="(row, index) in serviceRows" :key="index">
                                <div class="bg-bg-main/70 rounded-2xl border-2 border-primary-border p-4 sm:p-5 space-y-4">
                                    <!-- Hidden Inputs Array Kontrak Backend -->
                                    <input type="hidden" :name="'service_items[' + index + '][ac_type]'" :value="row.ac_type">
                                    <input type="hidden" :name="'service_items[' + index + '][service_category]'" :value="row.service_category">
                                    <input type="hidden" :name="'service_items[' + index + '][room_name]'" :value="row.room_name">
                                    <input type="hidden" :name="'service_items[' + index + '][quantity]'" :value="row.quantity">
                                    <input type="hidden" :name="'service_items[' + index + '][ac_unit_id]'" :value="row.ac_unit_id || ''">

                                    <!-- Header Kartu Keluhan -->
                                    <div class="flex items-center justify-between gap-2 pb-3 border-b border-primary-border">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-8 h-8 rounded-lg bg-primary text-white font-extrabold text-sm flex items-center justify-center" x-text="index + 1"></span>
                                            <h3 class="text-lg font-extrabold text-text-main" x-text="'Keluhan #' + (index + 1)"></h3>
                                        </div>
                                        <button type="button"
                                                x-show="serviceRows.length > 1"
                                                @click="removeRow(index)"
                                                class="min-h-[44px] px-3.5 py-2 rounded-xl bg-[#FEE2E2] hover:bg-[#FECACA] text-[#B91C1C] font-bold text-sm cursor-pointer transition-colors">
                                            &times; Hapus
                                        </button>
                                    </div>

                                    <!-- Kategori Keluhan (Kartu Pilihan Ramah Jempol) -->
                                    <div class="space-y-2">
                                        <span class="block text-base font-bold text-text-main">
                                            Kategori / Jenis Keluhan <span class="text-[#B91C1C]">*</span>
                                        </span>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                            <button type="button"
                                                    @click="row.service_category = 'Cuci Rutin'"
                                                    :class="row.service_category === 'Cuci Rutin' ? 'border-primary bg-primary-subtle text-primary ring-1 ring-primary' : 'border-primary-border bg-surface text-text-main hover:bg-primary-subtle/40'"
                                                    class="min-h-[56px] p-3.5 rounded-xl border-2 text-left transition-colors cursor-pointer">
                                                <span class="block text-base font-bold leading-snug">Cuci Rutin</span>
                                                <span class="block text-sm text-text-muted mt-0.5">Pembersihan berkala</span>
                                            </button>

                                            <button type="button"
                                                    @click="row.service_category = 'Kurang Dingin / Bocor Air'"
                                                    :class="row.service_category === 'Kurang Dingin / Bocor Air' ? 'border-primary bg-primary-subtle text-primary ring-1 ring-primary' : 'border-primary-border bg-surface text-text-main hover:bg-primary-subtle/40'"
                                                    class="min-h-[56px] p-3.5 rounded-xl border-2 text-left transition-colors cursor-pointer">
                                                <span class="block text-base font-bold leading-snug">Kurang Dingin / Bocor Air</span>
                                                <span class="block text-sm text-text-muted mt-0.5">Cek freon &amp; talang</span>
                                            </button>

                                            <button type="button"
                                                    @click="row.service_category = 'Mati Total / Rusak'"
                                                    :class="row.service_category === 'Mati Total / Rusak' ? 'border-primary bg-primary-subtle text-primary ring-1 ring-primary' : 'border-primary-border bg-surface text-text-main hover:bg-primary-subtle/40'"
                                                    class="min-h-[56px] p-3.5 rounded-xl border-2 text-left transition-colors cursor-pointer">
                                                <span class="block text-base font-bold leading-snug">Mati Total / Rusak</span>
                                                <span class="block text-sm text-text-muted mt-0.5">Perbaikan kerusakan berat</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Kapasitas PK & Jumlah Unit -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- Kapasitas / PK AC -->
                                        <div class="space-y-2">
                                            <label :for="'ac_type_' + index" class="block text-base font-bold text-text-main">
                                                Kapasitas / PK AC <span class="text-[#B91C1C]">*</span>
                                            </label>
                                            <select :id="'ac_type_' + index"
                                                    x-model="row.ac_type"
                                                    class="w-full min-h-[52px] px-4 py-3 rounded-xl border-2 border-primary-border bg-surface text-text-main text-base font-semibold focus:outline-none focus:border-primary">
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

                                        <!-- Jumlah Unit Counter Ramah Jempol -->
                                        <div class="space-y-2">
                                            <label :for="'qty_' + index" class="block text-base font-bold text-text-main">
                                                Jumlah Unit AC <span class="text-[#B91C1C]">*</span>
                                            </label>
                                            <div class="flex items-center gap-2.5">
                                                <button type="button"
                                                        @click="decreaseRowQty(row)"
                                                        aria-label="Kurangi jumlah unit"
                                                        class="w-12 h-12 min-w-[48px] min-h-[48px] rounded-xl bg-surface border-2 border-primary-border text-text-main hover:bg-primary-subtle font-extrabold text-2xl flex items-center justify-center cursor-pointer transition-colors">
                                                    &minus;
                                                </button>
                                                <input type="number"
                                                       :id="'qty_' + index"
                                                       x-model.number="row.quantity"
                                                       @change="normalizeRowQty(row)"
                                                       min="1"
                                                       max="100"
                                                       inputmode="numeric"
                                                       class="w-20 h-12 text-center rounded-xl border-2 border-primary-border bg-surface font-extrabold font-mono text-lg text-text-main focus:outline-none focus:border-primary">
                                                <button type="button"
                                                        @click="increaseRowQty(row)"
                                                        aria-label="Tambah jumlah unit"
                                                        class="w-12 h-12 min-w-[48px] min-h-[48px] rounded-xl bg-primary hover:bg-primary-hover text-white font-extrabold text-2xl flex items-center justify-center cursor-pointer transition-colors shadow-sm">
                                                    +
                                                </button>
                                                <span class="text-base font-semibold text-text-muted ml-1">Unit</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Lokasi / Ruang AC -->
                                    <div class="space-y-2">
                                        <label :for="'room_' + index" class="block text-base font-bold text-text-main">
                                            Lokasi / Ruang AC <span class="text-sm font-normal text-text-muted">(opsional)</span>
                                        </label>
                                        <input type="text"
                                               :id="'room_' + index"
                                               x-model="row.room_name"
                                               placeholder="Contoh: Shaf Depan, Ruang Imam, Lantai 2"
                                               class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border bg-surface text-text-main text-base placeholder-gray-400 focus:outline-none focus:border-primary">
                                    </div>

                                    <!-- Subtotal Baris -->
                                    <div class="pt-3 border-t border-primary-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 bg-surface rounded-xl px-4 py-3">
                                        <span class="text-sm text-text-muted">
                                            Tarif: <strong class="text-text-main font-mono" x-text="'Rp ' + formatRupiah(getRowRate(row)) + ' / unit'"></strong>
                                        </span>
                                        <span class="text-base font-extrabold text-primary font-mono" x-text="'Subtotal: Rp ' + formatRupiah(getRowSubtotal(row))"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Tombol Tambah Unit / Keluhan Lain -->
                        <button type="button"
                                @click="addRow()"
                                class="w-full min-h-[52px] px-5 py-3.5 rounded-2xl border-2 border-dashed border-primary/50 bg-primary-subtle/70 hover:bg-primary-subtle text-primary font-bold text-base flex items-center justify-center gap-2 cursor-pointer transition-colors">
                            <span>+ Tambah Unit / Keluhan Lain</span>
                        </button>

                        <!-- Pesan Panduan Validasi Langkah 2 -->
                        <div x-show="step2Error" x-cloak role="alert" class="p-4 rounded-xl bg-[#FEF3C7] border-2 border-[#FCD34D] text-[#92400E] text-base font-semibold">
                            <span x-text="step2Error"></span>
                        </div>

                        <!-- Navigasi Langkah 2 -->
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                            <button type="button"
                                    @click="goToStep(1)"
                                    class="min-h-[50px] px-6 py-3 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-base cursor-pointer transition-colors">
                                &larr; Kembali ke Data Masjid
                            </button>
                            <button type="button"
                                    @click="goToStep(3)"
                                    class="hidden lg:inline-flex min-h-[52px] px-7 py-3 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-base cursor-pointer transition-colors items-center justify-center gap-2 shadow-sm">
                                <span>Lanjut ke Jadwal &amp; Konfirmasi</span>
                                <span aria-hidden="true">&rarr;</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- =============================================================== -->
                <!-- LANGKAH 3: JADWAL & KONFIRMASI PESANAN                          -->
                <!-- =============================================================== -->
                <div x-show="currentStep === 3" x-cloak class="space-y-5">
                    <div class="bg-surface rounded-2xl border border-primary-border p-5 sm:p-7 shadow-sm space-y-6">
                        <div class="pb-3 border-b border-primary-border/70">
                            <span class="text-sm font-bold text-primary uppercase tracking-wider">Langkah 3 dari 3</span>
                            <h2 class="text-xl sm:text-2xl font-extrabold text-text-main mt-0.5">
                                Jadwal Kedatangan &amp; Konfirmasi
                            </h2>
                            <p class="text-sm text-text-muted mt-1">
                                Pilih waktu kunjungan yang nyaman bagi pengurus masjid, lalu periksa ringkasan sebelum mengirim.
                            </p>
                        </div>

                        <!-- Tanggal Kedatangan -->
                        <div class="space-y-2">
                            <label for="service_date" class="block text-base font-bold text-text-main">
                                Rencana Hari Servis <span class="text-sm font-normal text-text-muted">(opsional)</span>
                            </label>
                            <input type="date"
                                   name="service_date"
                                   id="service_date"
                                   x-model="serviceDate"
                                   min="{{ now()->toDateString() }}"
                                   class="w-full min-h-[52px] px-4 py-3 bg-surface border-2 border-primary-border rounded-xl text-text-main text-base font-semibold focus:outline-none focus:border-primary">
                            @error('service_date')
                                <p class="text-sm font-semibold text-[#B91C1C]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Pilihan Waktu Kedatangan (Radio Cards Ramah Jempol) -->
                        <div class="space-y-2.5">
                            <span class="block text-base font-bold text-text-main">
                                Pilihan Sesi Waktu Kedatangan
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($serviceTimeOptions as $timeOption)
                                    <label class="min-h-[68px] p-4 rounded-xl border-2 cursor-pointer transition-colors flex items-start gap-3"
                                           :class="serviceTime === {{ Js::from($timeOption['value']) }} ? 'border-primary bg-primary-subtle ring-1 ring-primary' : 'border-primary-border bg-surface hover:bg-primary-subtle/40'">
                                        <input type="radio"
                                               name="service_time"
                                               value="{{ $timeOption['value'] }}"
                                               x-model="serviceTime"
                                               class="mt-1 w-5 h-5 accent-primary shrink-0 cursor-pointer">
                                        <span class="min-w-0">
                                            <span class="block text-base font-bold text-text-main leading-snug">{{ $timeOption['title'] }}</span>
                                            <span class="block text-sm font-semibold text-primary mt-0.5">{{ $timeOption['time'] }}</span>
                                            <span class="block text-sm text-text-muted">{{ $timeOption['desc'] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('service_time')
                                <p class="text-sm font-semibold text-[#B91C1C]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Catatan Tambahan -->
                        <div class="space-y-2">
                            <label for="notes" class="block text-base font-bold text-text-main">
                                Catatan Tambahan untuk Teknisi <span class="text-sm font-normal text-text-muted">(opsional)</span>
                            </label>
                            <input type="text"
                                   name="notes"
                                   id="notes"
                                   x-model="notes"
                                   placeholder="Contoh: Butuh tangga tinggi untuk ruang sholat utama"
                                   class="w-full min-h-[52px] px-4 py-3 bg-surface border-2 border-primary-border rounded-xl text-text-main text-base placeholder-gray-400 focus:outline-none focus:border-primary">
                            @error('notes')
                                <p class="text-sm font-semibold text-[#B91C1C]">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kotak Ringkasan Pesanan Sebelum Kirim -->
                        <div class="rounded-2xl bg-primary-subtle border-2 border-primary-border p-4 sm:p-5 space-y-4">
                            <div class="flex items-center justify-between gap-2 pb-3 border-b border-primary-border">
                                <h3 class="text-lg font-extrabold text-primary">
                                    Ringkasan Pesanan Anda
                                </h3>
                                <span class="px-3 py-1 rounded-lg bg-primary text-white text-sm font-bold"
                                      x-text="totalUnits + ' Unit AC'"></span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm sm:text-base">
                                <div class="bg-surface rounded-xl p-3.5 border border-primary-border/80 space-y-1">
                                    <span class="block text-sm font-semibold text-text-muted">Masjid &amp; Kontak</span>
                                    <strong class="block text-base font-bold text-text-main" x-text="displayMasjidName"></strong>
                                    <p class="text-sm text-text-muted leading-snug" x-text="address || '-'"></p>
                                    <p class="text-sm font-semibold text-text-main pt-1">
                                        <span x-text="picName || '-'"></span>
                                        <span class="font-mono text-text-muted block" x-text="picPhone || '-'"></span>
                                    </p>
                                </div>

                                <div class="bg-surface rounded-xl p-3.5 border border-primary-border/80 space-y-1">
                                    <span class="block text-sm font-semibold text-text-muted">Rencana Jadwal</span>
                                    <strong class="block text-base font-bold text-text-main" x-text="formattedServiceDate"></strong>
                                    <p class="text-sm font-semibold text-primary" x-text="serviceTime"></p>
                                    <p x-show="notes" x-cloak class="text-sm text-text-muted pt-1">
                                        Catatan: <span x-text="notes"></span>
                                    </p>
                                </div>
                            </div>

                            <!-- Daftar Rincian AC -->
                            <div class="bg-surface rounded-xl border border-primary-border/80 divide-y divide-primary-border/60">
                                <template x-for="(row, idx) in serviceRows" :key="'sum-' + idx">
                                    <div class="px-4 py-3 flex items-center justify-between gap-3 text-sm sm:text-base">
                                        <div class="min-w-0">
                                            <strong class="block text-text-main font-bold" x-text="row.quantity + 'x ' + row.ac_type + ' — ' + row.service_category"></strong>
                                            <span x-show="row.room_name" class="block text-sm text-text-muted" x-text="'Ruang: ' + row.room_name"></span>
                                        </div>
                                        <span class="font-bold font-mono text-primary whitespace-nowrap" x-text="'Rp ' + formatRupiah(getRowSubtotal(row))"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Total Perkiraan Biaya -->
                            <div class="pt-2 flex flex-col sm:flex-row sm:items-end justify-between gap-2">
                                <div>
                                    <span class="block text-sm font-bold text-text-muted uppercase tracking-wide">Perkiraan Biaya Awal</span>
                                    <span class="block text-2xl sm:text-3xl font-extrabold text-primary font-mono mt-0.5"
                                          x-text="'Rp ' + formatRupiah(totalEstimatedAmount)"></span>
                                </div>
                                <p class="text-sm text-text-muted sm:text-right max-w-xs">
                                    *Pelunasan setelah servis beres. Biaya akhir menyesuaikan bila ada tambah freon/suku cadang.
                                </p>
                            </div>
                        </div>

                        <!-- Navigasi & Tombol Kirim Utama -->
                        <div class="pt-1 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3">
                            <button type="button"
                                    @click="goToStep(2)"
                                    class="min-h-[50px] px-6 py-3 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-base cursor-pointer transition-colors">
                                &larr; Ubah Rincian AC
                            </button>

                            <button type="submit"
                                    :disabled="isSubmitting"
                                    class="min-h-[56px] sm:flex-1 px-7 py-3.5 bg-primary hover:bg-primary-hover disabled:opacity-60 text-white font-extrabold text-base sm:text-lg rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                                <span x-text="isSubmitting ? 'Sedang Mengirim Pesanan...' : 'Kirim Pesanan Servis'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Kolom Kanan Desktop: Kartu Ringkasan Estimasi Biaya Sticky -->
        <aside class="hidden lg:block lg:col-span-4 lg:sticky lg:top-24 space-y-4">
            <div class="bg-surface rounded-2xl border-2 border-primary-border p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-primary-border">
                    <h2 class="text-lg font-extrabold text-primary">Ringkasan Estimasi</h2>
                    <span class="px-2.5 py-1 rounded-lg bg-primary-subtle text-primary font-bold text-sm"
                          x-text="totalUnits + ' Unit'"></span>
                </div>

                <div class="space-y-3 text-sm">
                    <div>
                        <span class="block text-text-muted font-medium">Masjid / Musholla</span>
                        <strong class="block text-base text-text-main font-bold mt-0.5" x-text="displayMasjidName"></strong>
                    </div>

                    <div class="pt-2 border-t border-primary-border/60 space-y-2">
                        <span class="block text-text-muted font-medium">Rincian Layanan</span>
                        <template x-for="(row, idx) in serviceRows" :key="'aside-' + idx">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-text-main leading-snug">
                                    <strong x-text="row.quantity + 'x ' + row.ac_type"></strong>
                                    <span class="block text-text-muted" x-text="row.service_category + (row.room_name ? ' (' + row.room_name + ')' : '')"></span>
                                </span>
                                <span class="font-bold font-mono text-primary whitespace-nowrap" x-text="'Rp ' + formatRupiah(getRowSubtotal(row))"></span>
                            </div>
                        </template>
                    </div>

                    <div class="pt-2 border-t border-primary-border/60">
                        <span class="block text-text-muted font-medium">Jadwal Pilihan</span>
                        <strong class="block text-text-main font-semibold mt-0.5" x-text="formattedServiceDate"></strong>
                        <span class="block text-primary font-semibold" x-text="serviceTime"></span>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-primary-subtle border border-primary-border space-y-1">
                    <span class="block text-sm font-bold text-text-muted">Perkiraan Biaya Awal</span>
                    <div class="text-2xl font-extrabold text-primary font-mono"
                         x-text="'Rp ' + formatRupiah(totalEstimatedAmount)"></div>
                    <span class="block text-sm text-text-muted">*Bayar setelah pengerjaan selesai</span>
                </div>
            </div>

            <div class="bg-surface rounded-2xl border border-primary-border p-4 text-sm text-text-muted leading-relaxed">
                <strong class="text-text-main block mb-1">Butuh bantuan pengisian?</strong>
                Setelah pesanan dikirim, Anda akan mendapat tombol WhatsApp langsung ke Admin Pengelola untuk koordinasi jadwal teknisi.
            </div>
        </aside>
    </div>

    <!-- Sticky Bottom Summary Bar Khusus HP (Langkah 2 & 3) -->
    <div x-show="currentStep === 2 || currentStep === 3"
         x-cloak
         class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-surface border-t-2 border-primary-border shadow-[0_-6px_18px_rgba(19,34,27,0.12)] px-4 py-3 safe-bottom">
        <div class="max-w-5xl mx-auto flex items-center justify-between gap-3">
            <div class="min-w-0">
                <span class="block text-sm font-semibold text-text-muted leading-tight">
                    Estimasi (<span x-text="totalUnits + ' Unit'"></span>)
                </span>
                <span class="block text-xl font-extrabold text-primary font-mono truncate"
                      x-text="'Rp ' + formatRupiah(totalEstimatedAmount)"></span>
            </div>

            <button x-show="currentStep === 2"
                    type="button"
                    @click="goToStep(3)"
                    class="min-h-[50px] px-5 py-3 bg-primary hover:bg-primary-hover text-white font-bold text-base rounded-xl shadow-sm shrink-0 cursor-pointer transition-colors">
                Lanjut &rarr;
            </button>

            <button x-show="currentStep === 3"
                    type="submit"
                    form="orderForm"
                    :disabled="isSubmitting"
                    class="min-h-[50px] px-5 py-3 bg-primary hover:bg-primary-hover disabled:opacity-60 text-white font-bold text-base rounded-xl shadow-sm shrink-0 cursor-pointer transition-colors">
                <span x-text="isSubmitting ? 'Mengirim...' : 'Kirim Pesanan'"></span>
            </button>
        </div>
    </div>

</div>

<script>
function orderWizard() {
    return {
        currentStep: {{ $initialStep }},
        isSubmitting: false,
        step1Error: '',
        step2Error: '',
        importedNotice: false,

        isNewMasjid: {{ Js::from($defaultIsNewMasjid) }},
        selectedMasjidId: {{ Js::from((string) old('masjid_id', '')) }},
        selectedMasjidName: '',
        masjids: {{ Js::from($masjids) }},
        selectedMasjidUnits: [],
        masjidQuery: '',
        filteredMasjids: [],
        showDropdown: false,

        newMasjidName: {{ Js::from((string) old('new_masjid_name', '')) }},
        address: {{ Js::from((string) old('address', '')) }},
        picName: {{ Js::from((string) old('pic_name', '')) }},
        picPhone: {{ Js::from((string) old('pic_phone', '')) }},

        serviceRows: {{ Js::from($initialServiceRows) }},
        serviceDate: {{ Js::from((string) $defaultServiceDate) }},
        serviceTime: {{ Js::from((string) $defaultServiceTime) }},
        notes: {{ Js::from((string) old('notes', '')) }},

        ratesMatrix: {
            'Cuci Rutin': {
                '0.5 PK': 65000,
                '0.75 PK': 65000,
                '1 PK': 65000,
                '1.5 PK': 75000,
                '2 PK': 75000,
                '2.5 PK': 90000,
                '3 PK': 90000,
                '5 PK': 130000,
            },
            'Kurang Dingin / Bocor Air': {
                '0.5 PK': 80000,
                '0.75 PK': 80000,
                '1 PK': 80000,
                '1.5 PK': 90000,
                '2 PK': 90000,
                '2.5 PK': 110000,
                '3 PK': 110000,
                '5 PK': 160000,
            },
            'Mati Total / Rusak': {
                '0.5 PK': 100000,
                '0.75 PK': 100000,
                '1 PK': 100000,
                '1.5 PK': 120000,
                '2 PK': 120000,
                '2.5 PK': 140000,
                '3 PK': 140000,
                '5 PK': 200000,
            }
        },

        init() {
            this.filteredMasjids = this.masjids;

            if (this.selectedMasjidId) {
                const found = this.masjids.find(m => String(m.id) === String(this.selectedMasjidId));
                if (found) {
                    this.selectedMasjidName = found.name;
                    this.masjidQuery = found.name;
                    this.selectedMasjidUnits = found.ac_units || [];
                    if (!this.address) this.address = found.address;
                    if (!this.picName) this.picName = found.pic_name;
                    if (!this.picPhone) this.picPhone = found.pic_phone;
                }
            }
        },

        get registeredTotalUnits() {
            return this.selectedMasjidUnits.reduce((acc, u) => acc + (parseInt(u.quantity) || 1), 0);
        },

        get displayMasjidName() {
            if (this.isNewMasjid === '1') {
                return this.newMasjidName.trim() || 'Masjid Baru (belum diisi)';
            }
            return this.selectedMasjidName || 'Belum dipilih';
        },

        get formattedServiceDate() {
            if (!this.serviceDate) return 'Menyesuaikan';
            const parts = this.serviceDate.split('-');
            if (parts.length !== 3) return this.serviceDate;
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const monthIdx = parseInt(parts[1], 10) - 1;
            return `${parseInt(parts[2], 10)} ${months[monthIdx] || parts[1]} ${parts[0]}`;
        },

        getRowRate(row) {
            const cat = row.service_category || 'Cuci Rutin';
            const catRates = this.ratesMatrix[cat] || this.ratesMatrix['Cuci Rutin'];
            const pk = row.ac_type || '1 PK';
            return catRates[pk] || 75000;
        },

        getRowSubtotal(row) {
            return (parseInt(row.quantity) || 1) * this.getRowRate(row);
        },

        get totalUnits() {
            return this.serviceRows.reduce((sum, r) => sum + (parseInt(r.quantity) || 1), 0);
        },

        get totalEstimatedAmount() {
            return this.serviceRows.reduce((sum, r) => sum + this.getRowSubtotal(r), 0);
        },

        get complaintSummary() {
            return this.serviceRows.map(r => {
                const room = r.room_name ? ` [${r.room_name}]` : '';
                return `${r.quantity}x ${r.ac_type} (${r.service_category}${room})`;
            }).join(', ');
        },

        formatRupiah(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        },

        switchMasjidMode(mode) {
            this.isNewMasjid = mode;
            this.step1Error = '';
            this.importedNotice = false;
            if (mode === '1') {
                this.selectedMasjidId = '';
                this.selectedMasjidName = '';
                this.masjidQuery = '';
                this.selectedMasjidUnits = [];
                this.showDropdown = false;
            }
        },

        useQueryAsNewMasjid() {
            const typed = this.masjidQuery.trim();
            this.switchMasjidMode('1');
            if (typed) {
                this.newMasjidName = typed;
            }
        },

        clearSelectedMasjid() {
            this.selectedMasjidId = '';
            this.selectedMasjidName = '';
            this.masjidQuery = '';
            this.selectedMasjidUnits = [];
            this.filteredMasjids = this.masjids;
            this.showDropdown = true;
        },

        onMasjidSearchInput() {
            if (this.selectedMasjidId && this.masjidQuery.trim() !== this.selectedMasjidName) {
                this.selectedMasjidId = '';
                this.selectedMasjidName = '';
                this.selectedMasjidUnits = [];
            }
            this.searchMasjid();
            this.showDropdown = true;
        },

        searchMasjid() {
            if (!this.masjidQuery) {
                this.filteredMasjids = this.masjids;
            } else {
                const q = this.masjidQuery.toLowerCase();
                this.filteredMasjids = this.masjids.filter(m =>
                    m.name.toLowerCase().includes(q) || m.address.toLowerCase().includes(q)
                );
            }
        },

        selectMasjid(item) {
            this.selectedMasjidId = String(item.id);
            this.selectedMasjidName = item.name;
            this.masjidQuery = item.name;
            this.address = item.address;
            this.picName = item.pic_name;
            this.picPhone = item.pic_phone;
            this.selectedMasjidUnits = item.ac_units || [];
            this.showDropdown = false;
            this.step1Error = '';
            this.importedNotice = false;
        },

        addRow() {
            this.serviceRows.push({
                room_name: '',
                ac_type: '1 PK',
                service_category: 'Cuci Rutin',
                quantity: 1,
                ac_unit_id: null,
            });
        },

        removeRow(index) {
            if (this.serviceRows.length > 1) {
                this.serviceRows.splice(index, 1);
            }
        },

        increaseRowQty(row) {
            const current = parseInt(row.quantity) || 1;
            row.quantity = Math.min(100, current + 1);
        },

        decreaseRowQty(row) {
            const current = parseInt(row.quantity) || 1;
            row.quantity = Math.max(1, current - 1);
        },

        normalizeRowQty(row) {
            const val = parseInt(row.quantity) || 1;
            row.quantity = Math.min(100, Math.max(1, val));
        },

        importRegisteredUnits() {
            if (!this.selectedMasjidUnits || this.selectedMasjidUnits.length === 0) return;
            this.serviceRows = this.selectedMasjidUnits.map(unit => ({
                room_name: unit.room_name || '',
                ac_type: unit.ac_type || '1 PK',
                service_category: 'Cuci Rutin',
                quantity: parseInt(unit.quantity) || 1,
                ac_unit_id: unit.id,
            }));
            this.importedNotice = true;
            this.step2Error = '';
        },

        validateStep1() {
            this.step1Error = '';

            if (this.isNewMasjid === '0' && !this.selectedMasjidId) {
                this.step1Error = 'Mohon pilih salah satu masjid dari daftar pencarian, atau tekan "+ Daftarkan Masjid Baru".';
                return false;
            }
            if (this.isNewMasjid === '1' && !this.newMasjidName.trim()) {
                this.step1Error = 'Mohon isi Nama Masjid atau Musholla Baru terlebih dahulu.';
                return false;
            }
            if (!this.address.trim()) {
                this.step1Error = 'Mohon isi Alamat Lengkap Masjid agar teknisi mudah menemukan lokasi.';
                return false;
            }
            if (!this.picName.trim()) {
                this.step1Error = 'Mohon isi Nama Pengurus atau Pemesan (DKM / Marbot).';
                return false;
            }
            const cleanPhone = this.picPhone.replace(/[^0-9+]/g, '');
            if (!cleanPhone) {
                this.step1Error = 'Mohon isi Nomor WhatsApp aktif untuk konfirmasi jadwal.';
                return false;
            }
            if (!/^(08|628|\+628)[0-9]{8,12}$/.test(cleanPhone)) {
                this.step1Error = 'Format nomor WhatsApp belum sesuai. Mohon gunakan contoh format: 081234567890.';
                return false;
            }

            return true;
        },

        validateStep2() {
            this.step2Error = '';
            if (!this.serviceRows || this.serviceRows.length === 0) {
                this.step2Error = 'Mohon tambahkan minimal 1 item unit AC yang akan diservis.';
                return false;
            }
            for (const row of this.serviceRows) {
                this.normalizeRowQty(row);
            }
            return true;
        },

        goToStep(target) {
            if (target === this.currentStep) return;

            // Mundur ke langkah sebelumnya selalu diperbolehkan tanpa kehilangan isian
            if (target < this.currentStep) {
                this.currentStep = target;
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            // Maju ke Langkah 2 atau 3 memerlukan Langkah 1 lengkap
            if (target >= 2 && !this.validateStep1()) {
                this.currentStep = 1;
                return;
            }

            if (target >= 3 && !this.validateStep2()) {
                this.currentStep = 2;
                return;
            }

            this.currentStep = target;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        handleFormSubmit(event) {
            if (!this.validateStep1()) {
                event.preventDefault();
                this.currentStep = 1;
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }
            if (!this.validateStep2()) {
                event.preventDefault();
                this.currentStep = 2;
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }
            this.isSubmitting = true;
        }
    };
}
</script>
@endsection
