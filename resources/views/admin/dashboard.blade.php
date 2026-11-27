@extends('layouts.app', ['title' => 'Meja Operasional Admin - Servis AC Masjid'])

@section('content')
<div class="space-y-6"
     x-data="{
        showAssignModal: false,
        showPayModal: false,
        showPhotosModal: false,
        selectedOrder: null,
        activePhotos: [],
        activeOrderNumber: '',
        assignDate: '',
        assignTime: 'Pagi (08:30 - 11:30 WIB)',
        assignTechId: '',
        originalOrderTime: '',
        isFlexibleOrderTime: false,
        officialSessions: [
            'Pagi (08:30 - 11:30 WIB)',
            'Siang Ba\'da Dzuhur (13:00 - 15:00 WIB)',
            'Sore Ba\'da Ashar (16:00 - 17:30 WIB)'
        ],
        techSchedules: [],
        loadingSchedules: false,

        async openAssign(order) {
            this.selectedOrder = order;
            this.assignDate = order.service_date ? order.service_date.split('T')[0] : '{{ now()->toDateString() }}';
            this.originalOrderTime = order.service_time || '';
            const isOfficial = this.officialSessions.includes(this.originalOrderTime);
            this.isFlexibleOrderTime = !isOfficial && this.originalOrderTime !== '';
            this.assignTime = isOfficial ? this.originalOrderTime : 'Pagi (08:30 - 11:30 WIB)';
            this.assignTechId = order.technician_id ? String(order.technician_id) : '';
            this.showAssignModal = true;
            await this.checkTechSchedules();
        },

        async checkTechSchedules() {
            if (!this.assignDate) return;
            this.loadingSchedules = true;
            try {
                const excludeId = this.selectedOrder ? this.selectedOrder.id : 0;
                const res = await fetch(`/admin/api/technician-schedule?date=${this.assignDate}&exclude_order_id=${excludeId}`);
                if (res.ok) {
                    const data = await res.json();
                    this.techSchedules = data.technicians || [];
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingSchedules = false;
            }
        },

        get selectedTechConflict() {
            if (!this.assignTechId || !this.assignTime) return null;
            const tech = this.techSchedules.find(t => t.id == this.assignTechId);
            if (!tech || !tech.tasks) return null;
            return tech.tasks.find(task => task.service_time === this.assignTime);
        },

        get selectedTechOtherTasks() {
            if (!this.assignTechId || !this.assignTime) return [];
            const tech = this.techSchedules.find(t => t.id == this.assignTechId);
            if (!tech || !tech.tasks) return [];
            return tech.tasks.filter(task => task.service_time !== this.assignTime);
        },

        getTechOptionLabel(tech) {
            const schedule = this.techSchedules.find(t => t.id == tech.id);
            if (!schedule) return tech.name + ' (' + tech.phone + ')';
            if (schedule.task_count === 0) {
                return tech.name + ' - Tersedia (0 Tugas)';
            }
            const hasSlotConflict = schedule.tasks.some(task => task.service_time === this.assignTime);
            if (hasSlotConflict) {
                return tech.name + ' - [BENTROK di Sesi Ini] (' + schedule.task_count + ' tugas)';
            }
            return tech.name + ' - ' + schedule.task_count + ' tugas (Sesi ini kosong)';
        },

        openPay(order) {
            if (!order.payment_method) {
                order.payment_method = 'transfer';
            }
            this.selectedOrder = order;
            this.showPayModal = true;
        },

        openPhotos(photos, orderNumber) {
            this.activePhotos = photos;
            this.activeOrderNumber = orderNumber;
            this.showPhotosModal = true;
        }
     }"
     @keydown.escape.window="showAssignModal = false; showPayModal = false; showPhotosModal = false">

    <!-- Header Meja Kerja Admin -->
    <section class="bg-surface rounded-2xl border border-primary-border p-5 sm:p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-text-main tracking-tight">
                Meja Kerja Operasional
            </h1>
            <p class="text-sm sm:text-base text-text-muted">
                Penjadwalan teknisi, pemantauan servis di masjid, dan verifikasi bukti pembayaran.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-sm">
            <span class="px-3.5 py-2 bg-primary-subtle text-primary font-bold rounded-xl border border-primary-border">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
            <span class="text-text-muted font-medium">
                Petugas: <strong class="text-text-main">{{ Auth::user()->name }}</strong>
            </span>
        </div>
    </section>

    <!-- 4 Tab Status Ramah Jempol (Grid 2x2 di HP, 4 Kolom di Desktop) -->
    <nav aria-label="Filter Status Pesanan" class="bg-surface border border-primary-border rounded-2xl p-2 shadow-sm">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
            <!-- Tab 1: Pesanan Baru -->
            <a href="{{ route('admin.dashboard', ['tab' => 'pesanan_baru']) }}"
               class="min-h-[56px] px-3.5 py-3 rounded-xl flex items-center justify-between gap-2 transition-colors {{ $activeTab === 'pesanan_baru' ? 'bg-primary text-white font-bold shadow-sm' : 'bg-bg-main text-text-main hover:bg-primary-subtle font-semibold border border-primary-border/60' }}">
                <span class="text-sm sm:text-base leading-snug">Pesanan Baru</span>
                <span class="px-2.5 py-1 rounded-full text-sm font-extrabold shrink-0 {{ $activeTab === 'pesanan_baru' ? 'bg-white text-primary' : 'bg-[#FEF3C7] text-[#92400E] border border-[#FCD34D]' }}">
                    {{ $counts['pesanan_baru'] }}
                </span>
            </a>

            <!-- Tab 2: Sedang Berjalan -->
            <a href="{{ route('admin.dashboard', ['tab' => 'berjalan']) }}"
               class="min-h-[56px] px-3.5 py-3 rounded-xl flex items-center justify-between gap-2 transition-colors {{ $activeTab === 'berjalan' ? 'bg-primary text-white font-bold shadow-sm' : 'bg-bg-main text-text-main hover:bg-primary-subtle font-semibold border border-primary-border/60' }}">
                <span class="text-sm sm:text-base leading-snug">Sedang Berjalan</span>
                <span class="px-2.5 py-1 rounded-full text-sm font-extrabold shrink-0 {{ $activeTab === 'berjalan' ? 'bg-white text-primary' : 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]' }}">
                    {{ $counts['berjalan'] }}
                </span>
            </a>

            <!-- Tab 3: Verifikasi Bukti Foto -->
            <a href="{{ route('admin.dashboard', ['tab' => 'verifikasi_bukti']) }}"
               class="min-h-[56px] px-3.5 py-3 rounded-xl flex items-center justify-between gap-2 transition-colors {{ in_array($activeTab, ['verifikasi_bukti', 'menunggu_bayar']) ? 'bg-primary text-white font-bold shadow-sm' : 'bg-bg-main text-text-main hover:bg-primary-subtle font-semibold border border-primary-border/60' }}">
                <span class="text-sm sm:text-base leading-snug">Verifikasi Bukti Foto</span>
                <span class="px-2.5 py-1 rounded-full text-sm font-extrabold shrink-0 {{ in_array($activeTab, ['verifikasi_bukti', 'menunggu_bayar']) ? 'bg-white text-primary' : 'bg-[#FFEDD5] text-[#C2410C] border border-[#FDBA74]' }}">
                    {{ $counts['verifikasi_bukti'] ?? ($counts['menunggu_bayar'] ?? 0) }}
                </span>
            </a>

            <!-- Tab 4: Selesai & Lunas -->
            <a href="{{ route('admin.dashboard', ['tab' => 'selesai']) }}"
               class="min-h-[56px] px-3.5 py-3 rounded-xl flex items-center justify-between gap-2 transition-colors {{ $activeTab === 'selesai' ? 'bg-primary text-white font-bold shadow-sm' : 'bg-bg-main text-text-main hover:bg-primary-subtle font-semibold border border-primary-border/60' }}">
                <span class="text-sm sm:text-base leading-snug">Selesai &amp; Lunas</span>
                <span class="px-2.5 py-1 rounded-full text-sm font-extrabold shrink-0 {{ $activeTab === 'selesai' ? 'bg-white text-primary' : 'bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC]' }}">
                    {{ $counts['selesai'] }}
                </span>
            </a>
        </div>
    </nav>

    <!-- ========================================================================= -->
    <!-- TAMPILAN MOBILE (< lg): KARTU PESANAN RAMAH ORANG TUA                     -->
    <!-- ========================================================================= -->
    <div class="lg:hidden space-y-4">
        @forelse($orders as $order)
            <article class="bg-surface rounded-2xl border-2 border-primary-border p-5 shadow-sm space-y-4">
                <!-- Baris Atas: Nomor Order & Status -->
                <div class="flex flex-wrap items-start justify-between gap-2 pb-3 border-b border-primary-border/70">
                    <div>
                        <span class="block font-mono text-base font-extrabold text-primary">{{ $order->order_number }}</span>
                        <span class="block text-sm text-text-muted mt-0.5">{{ $order->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                    </div>
                    <span class="inline-block px-3 py-1.5 rounded-lg text-sm font-bold
                        {{ $order->status === 'pesanan_baru' ? 'bg-[#FEF3C7] text-[#92400E] border border-[#FCD34D]' : '' }}
                        {{ in_array($order->status, ['dijadwalkan', 'dikerjakan']) ? 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]' : '' }}
                        {{ $order->status === 'pekerjaan_selesai' ? 'bg-[#FEF3C7] text-[#92400E] border border-[#FCD34D]' : '' }}
                        {{ $order->status === 'menunggu_verifikasi' ? 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]' : '' }}
                        {{ $order->status === 'menunggu_bayar' ? 'bg-[#FFEDD5] text-[#C2410C] border border-[#FDBA74]' : '' }}
                        {{ $order->status === 'selesai' ? 'bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC]' : '' }}
                        {{ $order->status === 'batal' ? 'bg-[#FEE2E2] text-[#B91C1C] border border-[#FCA5A5]' : '' }}">
                        @if($order->status === 'pekerjaan_selesai')
                            Nunggu Bukti Teknisi
                        @elseif($order->status === 'menunggu_verifikasi')
                            Bukti Siap Diverifikasi
                        @else
                            {{ $order->statusLabel() }}
                        @endif
                    </span>
                </div>

                <!-- Informasi Masjid -->
                <div class="space-y-1">
                    <h2 class="text-lg font-extrabold text-text-main leading-snug">{{ $order->masjid->name }}</h2>
                    <p class="text-sm text-text-muted leading-relaxed">{{ $order->masjid->address }}</p>
                    <p class="text-sm font-semibold text-text-main pt-0.5">
                        {{ $order->masjid->pic_name }}
                        <span class="font-mono text-primary ml-1">({{ $order->masjid->pic_phone }})</span>
                    </p>
                </div>

                <!-- Layanan, Jadwal & Teknisi -->
                <div class="p-3.5 rounded-xl bg-bg-main border border-primary-border/70 space-y-2 text-sm">
                    <div>
                        <span class="text-text-muted font-medium">Layanan ({{ $order->ac_count }} Unit AC):</span>
                        <strong class="block text-text-main font-bold mt-0.5">{{ $order->dkm_complaint }}</strong>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-primary-border/60">
                        <div>
                            <span class="block text-text-muted">Jadwal Kunjungan:</span>
                            <strong class="text-text-main font-semibold">
                                {{ $order->service_date ? $order->service_date->translatedFormat('d M Y') : '-' }}
                                &bull; {{ $order->service_time ?? '-' }}
                            </strong>
                        </div>
                        <div>
                            <span class="block text-text-muted">Teknisi Bertugas:</span>
                            @if($order->technician)
                                <strong class="text-text-main font-bold">{{ $order->technician->name }}</strong>
                                <span class="block font-mono text-text-muted">{{ $order->technician->phone }}</span>
                            @else
                                <strong class="text-[#92400E] font-bold">Belum ditugaskan</strong>
                            @endif
                        </div>
                    </div>

                    @if($order->additional_amount > 0)
                        <div class="pt-2 border-t border-primary-border/60 text-[#92400E] font-semibold">
                            + Sparepart: {{ $order->additional_notes }}
                        </div>
                    @endif
                </div>

                <!-- Lampiran Foto / Bukti Transfer -->
                @if($order->photos->isNotEmpty() || $order->payment_proof)
                    <div class="flex flex-wrap gap-2">
                        @if($order->photos->isNotEmpty())
                            <button type="button"
                                    @click="openPhotos({{ Js::from($order->photos) }}, '{{ $order->order_number }}')"
                                    class="min-h-[44px] px-3.5 py-2 rounded-xl bg-bg-main hover:bg-primary-subtle border border-primary-border text-text-main font-bold text-sm cursor-pointer transition-colors">
                                Lihat {{ $order->photos->count() }} Foto Bukti
                            </button>
                        @endif
                        @if($order->payment_proof)
                            <button type="button"
                                    @click="openPay({{ Js::from($order) }})"
                                    class="min-h-[44px] px-3.5 py-2 rounded-xl bg-[#E0F2FE] hover:bg-[#BAE6FD]/60 border border-[#BAE6FD] text-[#0369A1] font-bold text-sm cursor-pointer transition-colors">
                                Bukti Transfer Tersedia
                            </button>
                        @endif
                    </div>
                @endif

                <!-- Total Biaya -->
                <div class="p-3.5 rounded-xl bg-primary-subtle border border-primary-border flex items-center justify-between gap-2">
                    <div>
                        <span class="block text-sm font-semibold text-text-muted">Total Biaya</span>
                        @if($order->additional_amount > 0)
                            <span class="block text-sm text-text-muted">Dasar: Rp {{ number_format($order->base_amount, 0, ',', '.') }}</span>
                        @endif
                    </div>
                    <span class="text-xl font-extrabold font-mono text-primary">
                        Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}
                    </span>
                </div>

                <!-- Deretan Tombol Aksi Ramah Jempol -->
                <div class="pt-1 flex flex-col sm:flex-row gap-2.5">
                    @if($order->status === 'pesanan_baru')
                        <button type="button"
                                @click="openAssign({{ Js::from($order) }})"
                                class="flex-1 min-h-[48px] px-4 py-3 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-base transition-colors cursor-pointer shadow-xs">
                            Tugaskan Teknisi
                        </button>
                        <form method="POST" action="{{ route('admin.order.cancel', $order->id) }}" onsubmit="return confirm('Batalkan pesanan ini?');" class="sm:w-auto">
                            @csrf
                            <button type="submit"
                                    class="w-full min-h-[48px] px-4 py-3 rounded-xl border-2 border-[#FCA5A5] bg-surface hover:bg-[#FEE2E2] text-[#B91C1C] font-bold text-base transition-colors cursor-pointer">
                                Batalkan Pesanan
                            </button>
                        </form>
                    @elseif(in_array($order->status, ['dijadwalkan', 'dikerjakan']))
                        <button type="button"
                                @click="openAssign({{ Js::from($order) }})"
                                class="w-full min-h-[48px] px-4 py-3 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-base transition-colors cursor-pointer">
                            Ubah Jadwal / Teknisi
                        </button>
                    @elseif(in_array($order->status, ['menunggu_verifikasi', 'menunggu_bayar']))
                        <button type="button"
                                @click="openPay({{ Js::from($order) }})"
                                class="w-full min-h-[48px] px-4 py-3 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-base transition-colors cursor-pointer shadow-xs">
                            Verifikasi Bukti
                        </button>
                    @elseif($order->status === 'pekerjaan_selesai')
                        <div class="w-full min-h-[48px] px-4 py-3 rounded-xl bg-bg-main border border-primary-border text-center text-base font-semibold text-text-muted flex items-center justify-center">
                            Menunggu Teknisi
                        </div>
                    @elseif($order->status === 'selesai')
                        <a href="{{ route('admin.order.receipt', $order->id) }}"
                           class="w-full min-h-[48px] px-4 py-3 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-base transition-colors flex items-center justify-center">
                            Lihat Kuitansi Resmi
                        </a>
                    @endif
                </div>
            </article>
        @empty
            <div class="bg-surface rounded-2xl border-2 border-dashed border-primary-border p-10 text-center space-y-1">
                <p class="text-base font-bold text-text-main">Tidak ada data pesanan pada tab ini.</p>
                <p class="text-sm text-text-muted">Pesanan yang sesuai kategori ini akan tampil otomatis di sini.</p>
            </div>
        @endforelse
    </div>

    <!-- ========================================================================= -->
    <!-- TAMPILAN DESKTOP (lg+): TABEL OPERASIONAL PROFESIONAL                     -->
    <!-- ========================================================================= -->
    <div class="hidden lg:block bg-surface rounded-2xl border border-primary-border overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-subtle/70 border-b border-primary-border text-primary font-bold uppercase text-sm tracking-wide">
                    <tr>
                        <th class="py-4 px-4">No. Order &amp; Waktu</th>
                        <th class="py-4 px-4">Masjid &amp; Kontak</th>
                        <th class="py-4 px-4">Layanan &amp; Lampiran</th>
                        <th class="py-4 px-4">Teknisi</th>
                        <th class="py-4 px-4 text-right">Total Biaya</th>
                        <th class="py-4 px-4 text-center">Status</th>
                        <th class="py-4 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-border/60 text-sm">
                    @forelse($orders as $order)
                        <tr class="hover:bg-primary-subtle/30 transition-colors align-top">
                            <!-- No. Order -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="font-mono font-extrabold text-primary text-base block">{{ $order->order_number }}</span>
                                <span class="text-text-muted text-sm">{{ $order->created_at->translatedFormat('d M, H:i') }} WIB</span>
                            </td>

                            <!-- Masjid & Kontak -->
                            <td class="py-4 px-4 max-w-[240px]">
                                <strong class="text-base font-bold text-text-main block">{{ $order->masjid->name }}</strong>
                                <span class="text-text-muted text-sm block truncate">{{ $order->masjid->address }}</span>
                                <span class="text-text-main text-sm block mt-1">
                                    {{ $order->masjid->pic_name }}
                                    <span class="font-mono text-text-muted">({{ $order->masjid->pic_phone }})</span>
                                </span>
                            </td>

                            <!-- Layanan & Lampiran -->
                            <td class="py-4 px-4 max-w-[260px] space-y-1.5">
                                <span class="font-bold text-text-main block">{{ $order->dkm_complaint }}</span>
                                <span class="text-text-muted block">
                                    {{ $order->ac_count }} Unit AC &bull; {{ $order->service_date ? $order->service_date->translatedFormat('d M') : '-' }} ({{ $order->service_time ?? '-' }})
                                </span>
                                @if($order->additional_amount > 0)
                                    <span class="block text-[#92400E] font-semibold">
                                        + Sparepart: {{ $order->additional_notes }}
                                    </span>
                                @endif
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    @if($order->photos->isNotEmpty())
                                        <button type="button"
                                                @click="openPhotos({{ Js::from($order->photos) }}, '{{ $order->order_number }}')"
                                                class="min-h-[36px] px-3 py-1 rounded-lg bg-bg-main hover:bg-primary-subtle border border-primary-border text-text-main font-semibold text-sm cursor-pointer transition-colors">
                                            {{ $order->photos->count() }} Foto Bukti
                                        </button>
                                    @endif
                                    @if($order->payment_proof)
                                        <button type="button"
                                                @click="openPay({{ Js::from($order) }})"
                                                class="min-h-[36px] px-3 py-1 rounded-lg bg-[#E0F2FE] hover:bg-[#BAE6FD]/60 border border-[#BAE6FD] text-[#0369A1] font-bold text-sm cursor-pointer transition-colors">
                                            Bukti Transfer Tersedia
                                        </button>
                                    @endif
                                </div>
                            </td>

                            <!-- Teknisi -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($order->technician)
                                    <span class="font-bold text-text-main block text-base">{{ $order->technician->name }}</span>
                                    <span class="text-text-muted font-mono text-sm">{{ $order->technician->phone }}</span>
                                @else
                                    <span class="text-[#92400E] font-bold">Belum ditugaskan</span>
                                @endif
                            </td>

                            <!-- Total Biaya -->
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <span class="font-extrabold font-mono text-base text-primary block">
                                    Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}
                                </span>
                                @if($order->additional_amount > 0)
                                    <span class="text-sm text-text-muted font-mono">
                                        Dasar: {{ number_format($order->base_amount, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>

                            <!-- Status Badge -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                <span class="inline-block px-3 py-1.5 rounded-lg text-sm font-bold
                                    {{ $order->status === 'pesanan_baru' ? 'bg-[#FEF3C7] text-[#92400E] border border-[#FCD34D]' : '' }}
                                    {{ in_array($order->status, ['dijadwalkan', 'dikerjakan']) ? 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]' : '' }}
                                    {{ $order->status === 'pekerjaan_selesai' ? 'bg-[#FEF3C7] text-[#92400E] border border-[#FCD34D]' : '' }}
                                    {{ $order->status === 'menunggu_verifikasi' ? 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]' : '' }}
                                    {{ $order->status === 'menunggu_bayar' ? 'bg-[#FFEDD5] text-[#C2410C] border border-[#FDBA74]' : '' }}
                                    {{ $order->status === 'selesai' ? 'bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC]' : '' }}
                                    {{ $order->status === 'batal' ? 'bg-[#FEE2E2] text-[#B91C1C] border border-[#FCA5A5]' : '' }}">
                                    @if($order->status === 'pekerjaan_selesai')
                                        Nunggu Bukti Teknisi
                                    @elseif($order->status === 'menunggu_verifikasi')
                                        Bukti Siap Diverifikasi
                                    @else
                                        {{ $order->statusLabel() }}
                                    @endif
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    @if($order->status === 'pesanan_baru')
                                        <button type="button"
                                                @click="openAssign({{ Js::from($order) }})"
                                                class="min-h-[44px] px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-sm transition-colors cursor-pointer">
                                            Tugaskan
                                        </button>
                                        <form method="POST" action="{{ route('admin.order.cancel', $order->id) }}" onsubmit="return confirm('Batalkan pesanan ini?');" class="inline">
                                            @csrf
                                            <button type="submit" class="min-h-[44px] px-3 py-2 rounded-xl border border-[#FCA5A5] text-[#B91C1C] hover:bg-[#FEE2E2] text-sm font-bold cursor-pointer transition-colors">
                                                Batal
                                            </button>
                                        </form>
                                    @elseif(in_array($order->status, ['dijadwalkan', 'dikerjakan']))
                                        <button type="button"
                                                @click="openAssign({{ Js::from($order) }})"
                                                class="min-h-[44px] px-4 py-2 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-sm transition-colors cursor-pointer">
                                            Ubah Jadwal
                                        </button>
                                    @elseif(in_array($order->status, ['menunggu_verifikasi', 'menunggu_bayar']))
                                        <button type="button"
                                                @click="openPay({{ Js::from($order) }})"
                                                class="min-h-[44px] px-4 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-sm transition-colors cursor-pointer">
                                            Verifikasi Bukti
                                        </button>
                                    @elseif($order->status === 'pekerjaan_selesai')
                                        <span class="text-sm font-semibold text-text-muted italic">
                                            Menunggu Teknisi
                                        </span>
                                    @elseif($order->status === 'selesai')
                                        <a href="{{ route('admin.order.receipt', $order->id) }}"
                                           class="min-h-[44px] px-4 py-2 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-sm transition-colors inline-flex items-center">
                                            Kuitansi
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-text-muted text-base font-medium">
                                Tidak ada data pesanan pada tab ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginasi -->
    <div>
        {{ $orders->links() }}
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: PENUGASAN TEKNISI & JADWAL SERVIS                                -->
    <!-- ========================================================================= -->
    <div x-show="showAssignModal"
         x-cloak
         role="dialog"
         aria-modal="true"
         aria-label="Penugasan Teknisi dan Jadwal Servis"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.away="showAssignModal = false"
             class="bg-surface rounded-2xl border-2 border-primary-border max-w-lg w-full p-5 sm:p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between gap-3 pb-3 border-b border-primary-border">
                <div>
                    <h3 class="text-xl font-extrabold text-text-main">Penugasan Teknisi &amp; Jadwal</h3>
                    <p class="text-sm font-mono text-primary font-semibold" x-text="selectedOrder ? selectedOrder.order_number : ''"></p>
                </div>
                <button type="button"
                        @click="showAssignModal = false"
                        aria-label="Tutup modal penugasan"
                        class="tap-target inline-flex items-center justify-center rounded-xl bg-bg-main hover:bg-primary-subtle text-text-main text-2xl font-bold cursor-pointer shrink-0">
                    &times;
                </button>
            </div>

            <template x-if="selectedOrder">
                <form :action="'/admin/order/' + selectedOrder.id + '/assign'" method="POST" class="space-y-4">
                    @csrf

                    <!-- Ringkasan Masjid -->
                    <div class="p-4 bg-primary-subtle rounded-xl border border-primary-border space-y-1">
                        <span class="text-sm font-semibold text-text-muted block">Masjid / Musholla</span>
                        <strong class="text-text-main text-base font-extrabold block" x-text="selectedOrder.masjid.name"></strong>
                        <span class="text-text-muted text-sm block" x-text="selectedOrder.ac_count + ' Unit AC • ' + selectedOrder.dkm_complaint"></span>
                    </div>

                    <!-- Banner Info Jika Pelanggan Memilih Waktu Fleksibel / Non-Resmi -->
                    <div x-show="isFlexibleOrderTime" x-cloak class="p-4 rounded-xl bg-[#FEF3C7] border-2 border-[#FCD34D] text-[#92400E] space-y-1">
                        <strong class="block text-base font-bold">Informasi Sesi Waktu</strong>
                        <p class="text-sm leading-relaxed font-medium">
                            Pelanggan memilih waktu fleksibel. Silakan pilih salah satu dari 3 sesi resmi di bawah untuk menjadwalkan teknisi.
                        </p>
                        <p class="text-sm font-semibold">
                            Pilihan awal pelanggan: <span x-text="originalOrderTime"></span>
                        </p>
                    </div>

                    <!-- Tanggal Servis -->
                    <div class="space-y-1.5">
                        <label for="assign_service_date" class="block text-base font-bold text-text-main">Tanggal Kedatangan</label>
                        <input type="date"
                               id="assign_service_date"
                               name="service_date"
                               required
                               x-model="assignDate"
                               @change="checkTechSchedules()"
                               class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main font-semibold outline-none focus:border-primary bg-surface">
                    </div>

                    <!-- Jam Servis (EXACT STRING MATCH 3 SESI RESMI) -->
                    <div class="space-y-1.5">
                        <label for="assign_service_time" class="block text-base font-bold text-text-main">Sesi Jam Kedatangan</label>
                        <select id="assign_service_time"
                                name="service_time"
                                required
                                x-model="assignTime"
                                class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main font-semibold outline-none focus:border-primary bg-surface">
                            <option value="Pagi (08:30 - 11:30 WIB)">Pagi (08:30 - 11:30 WIB)</option>
                            <option value="Siang Ba'da Dzuhur (13:00 - 15:00 WIB)">Siang Ba'da Dzuhur (13:00 - 15:00 WIB)</option>
                            <option value="Sore Ba'da Ashar (16:00 - 17:30 WIB)">Sore Ba'da Ashar (16:00 - 17:30 WIB)</option>
                        </select>
                    </div>

                    <!-- Pilih Teknisi -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between gap-2">
                            <label for="assign_technician_id" class="block text-base font-bold text-text-main">Pilih Teknisi Lapangan</label>
                            <span x-show="loadingSchedules" class="text-sm text-primary font-semibold italic">Memeriksa jadwal...</span>
                        </div>
                        <select id="assign_technician_id"
                                name="technician_id"
                                required
                                x-model="assignTechId"
                                class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 text-base text-text-main font-semibold outline-none bg-surface transition-colors"
                                :class="selectedTechConflict ? 'border-[#B91C1C] focus:border-[#B91C1C]' : 'border-primary-border focus:border-primary'">
                            <option value="">-- Pilih Teknisi --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" x-text="getTechOptionLabel({{ Js::from($tech) }})">
                                    {{ $tech->name }} ({{ $tech->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Ketersediaan / Bentrok Jadwal -->
                    <div class="space-y-2.5">
                        <div x-show="selectedTechConflict" x-cloak class="p-4 bg-[#FEE2E2] border-2 border-[#FCA5A5] rounded-xl text-[#7F1D1D] space-y-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <strong class="text-base font-extrabold">Peringatan: Jadwal Bentrok!</strong>
                                <span class="px-2.5 py-0.5 rounded-md text-sm font-bold bg-[#B91C1C] text-white">BENTROK</span>
                            </div>
                            <p class="text-sm leading-relaxed">
                                Teknisi ini sudah terjadwal di <strong x-text="selectedTechConflict ? selectedTechConflict.masjid_name : ''"></strong> pada sesi <strong x-text="assignTime"></strong>.
                            </p>
                            <p class="text-sm font-semibold">
                                * Tombol simpan dinonaktifkan. Pilih sesi lain atau teknisi lain yang tersedia.
                            </p>
                        </div>

                        <div x-show="!selectedTechConflict && selectedTechOtherTasks.length > 0" x-cloak class="p-4 bg-[#E0F2FE] border-2 border-[#BAE6FD] rounded-xl text-[#0C4A6E] space-y-1.5">
                            <strong class="text-base font-bold block">Info Jadwal Teknisi di Tanggal Ini:</strong>
                            <ul class="list-disc list-inside text-sm space-y-1">
                                <template x-for="task in selectedTechOtherTasks" :key="task.order_id">
                                    <li><span x-text="task.service_time + ' di ' + task.masjid_name"></span></li>
                                </template>
                            </ul>
                            <p class="text-sm font-semibold text-[#0369A1]">
                                Sesi <strong x-text="assignTime"></strong> masih kosong dan aman untuk ditugaskan.
                            </p>
                        </div>

                        <div x-show="assignTechId && !selectedTechConflict && selectedTechOtherTasks.length === 0" x-cloak class="p-3.5 bg-primary-subtle border-2 border-primary-border rounded-xl text-sm text-primary flex items-center justify-between gap-2 font-semibold">
                            <span>Teknisi tersedia penuh pada tanggal ini (0 tugas).</span>
                            <span class="font-bold px-2.5 py-1 rounded-lg bg-primary text-white shrink-0">TERSEDIA</span>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-primary-border">
                        <button type="button"
                                @click="showAssignModal = false"
                                class="min-h-[48px] px-5 py-2.5 text-base font-bold text-text-main bg-bg-main hover:bg-primary-subtle border border-primary-border rounded-xl cursor-pointer transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="selectedTechConflict"
                                class="min-h-[48px] px-6 py-2.5 text-base font-bold text-white rounded-xl transition-colors shadow-xs"
                                :class="selectedTechConflict ? 'opacity-50 cursor-not-allowed bg-gray-400' : 'bg-primary hover:bg-primary-hover cursor-pointer'">
                            Simpan Penugasan
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: VERIFIKASI PELUNASAN PEMBAYARAN                                  -->
    <!-- ========================================================================= -->
    <div x-show="showPayModal"
         x-cloak
         role="dialog"
         aria-modal="true"
         aria-label="Verifikasi Pelunasan Pembayaran"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.away="showPayModal = false"
             class="bg-surface rounded-2xl border-2 border-primary-border max-w-xl w-full p-5 sm:p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between gap-3 pb-3 border-b border-primary-border">
                <div>
                    <h3 class="text-xl font-extrabold text-text-main">Verifikasi Pelunasan Pembayaran</h3>
                    <p class="text-sm font-mono font-semibold text-primary" x-text="selectedOrder ? 'No. Pesanan: ' + selectedOrder.order_number : ''"></p>
                </div>
                <button type="button"
                        @click="showPayModal = false"
                        aria-label="Tutup modal verifikasi pembayaran"
                        class="tap-target inline-flex items-center justify-center rounded-xl bg-bg-main hover:bg-primary-subtle text-text-main text-2xl font-bold cursor-pointer shrink-0">
                    &times;
                </button>
            </div>

            <template x-if="selectedOrder">
                <form :action="'/admin/order/' + selectedOrder.id + '/pay'" method="POST" class="space-y-5">
                    @csrf

                    <!-- Preview Besar Foto Bukti Transfer / QRIS dari Teknisi -->
                    <template x-if="selectedOrder.payment_proof">
                        <div class="p-4 bg-[#E0F2FE]/60 rounded-2xl border-2 border-[#BAE6FD] space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-sm font-extrabold text-[#0369A1] uppercase tracking-wide">
                                    Foto Bukti Pembayaran Teknisi
                                </span>
                                <span class="px-2.5 py-1 rounded-lg text-sm font-bold bg-surface text-[#0369A1] border border-[#BAE6FD] uppercase"
                                      x-text="'Metode: ' + (selectedOrder.payment_method || 'transfer')"></span>
                            </div>

                            <div class="rounded-xl overflow-hidden border-2 border-[#BAE6FD] bg-gray-900 flex items-center justify-center">
                                <img :src="'/storage/' + selectedOrder.payment_proof"
                                     alt="Bukti Pembayaran"
                                     class="max-h-72 w-auto object-contain cursor-pointer hover:opacity-95 transition-opacity"
                                     @click="window.open('/storage/' + selectedOrder.payment_proof, '_blank')">
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-sm text-[#0369A1]">
                                <span>Ketuk gambar untuk memperbesar resolusi penuh.</span>
                                <a :href="'/storage/' + selectedOrder.payment_proof"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="font-bold underline hover:text-[#0C4A6E]">
                                    Buka Foto di Tab Baru &rarr;
                                </a>
                            </div>
                        </div>
                    </template>

                    <template x-if="!selectedOrder.payment_proof">
                        <div class="p-4 bg-bg-main rounded-xl border border-primary-border text-sm text-text-muted font-medium">
                            Teknisi tidak menyertakan foto bukti transfer (pembayaran tunai / manual).
                        </div>
                    </template>

                    <!-- Rincian Tagihan -->
                    <div class="p-4 bg-primary-subtle rounded-xl border border-primary-border text-sm sm:text-base space-y-2">
                        <strong class="text-text-main text-base font-extrabold block" x-text="selectedOrder.masjid.name"></strong>
                        <div class="flex justify-between text-text-muted">
                            <span>Biaya Servis Dasar</span>
                            <span class="font-mono font-semibold text-text-main" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedOrder.base_amount)"></span>
                        </div>
                        <template x-if="selectedOrder.additional_amount > 0">
                            <div class="flex justify-between text-text-main font-semibold">
                                <span>Biaya Suku Cadang</span>
                                <span class="font-mono" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedOrder.additional_amount)"></span>
                            </div>
                        </template>
                        <div class="pt-2.5 border-t border-primary-border flex items-center justify-between font-extrabold text-base sm:text-lg text-text-main">
                            <span>Total Pembayaran</span>
                            <span class="text-xl font-mono text-primary" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(parseFloat(selectedOrder.base_amount) + parseFloat(selectedOrder.additional_amount))"></span>
                        </div>
                    </div>

                    <!-- Pilih Metode Pembayaran (Kartu Radio Besar) -->
                    <div class="space-y-2">
                        <span class="block text-base font-bold text-text-main">Konfirmasi Metode Pembayaran:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <label class="min-h-[60px] border-2 rounded-xl p-3.5 cursor-pointer transition-colors flex sm:flex-col items-center sm:justify-center gap-3 sm:gap-1.5 text-base font-bold"
                                   :class="selectedOrder.payment_method === 'transfer' ? 'border-primary bg-primary-subtle text-primary ring-1 ring-primary' : 'border-primary-border bg-surface text-text-main hover:bg-primary-subtle/40'">
                                <input type="radio" name="payment_method" value="transfer" x-model="selectedOrder.payment_method" class="w-5 h-5 accent-primary cursor-pointer">
                                <span>Transfer Bank</span>
                            </label>
                            <label class="min-h-[60px] border-2 rounded-xl p-3.5 cursor-pointer transition-colors flex sm:flex-col items-center sm:justify-center gap-3 sm:gap-1.5 text-base font-bold"
                                   :class="selectedOrder.payment_method === 'qris' ? 'border-primary bg-primary-subtle text-primary ring-1 ring-primary' : 'border-primary-border bg-surface text-text-main hover:bg-primary-subtle/40'">
                                <input type="radio" name="payment_method" value="qris" x-model="selectedOrder.payment_method" class="w-5 h-5 accent-primary cursor-pointer">
                                <span>QRIS</span>
                            </label>
                            <label class="min-h-[60px] border-2 rounded-xl p-3.5 cursor-pointer transition-colors flex sm:flex-col items-center sm:justify-center gap-3 sm:gap-1.5 text-base font-bold"
                                   :class="selectedOrder.payment_method === 'cash' ? 'border-primary bg-primary-subtle text-primary ring-1 ring-primary' : 'border-primary-border bg-surface text-text-main hover:bg-primary-subtle/40'">
                                <input type="radio" name="payment_method" value="cash" x-model="selectedOrder.payment_method" class="w-5 h-5 accent-primary cursor-pointer">
                                <span>Tunai</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-primary-border">
                        <button type="button"
                                @click="showPayModal = false"
                                class="min-h-[48px] px-5 py-2.5 text-base font-bold text-text-main bg-bg-main hover:bg-primary-subtle border border-primary-border rounded-xl cursor-pointer transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="min-h-[50px] px-6 py-3 text-base font-extrabold text-white bg-primary hover:bg-primary-hover rounded-xl cursor-pointer transition-colors shadow-xs">
                            Verifikasi Lunas &amp; Buat Kuitansi
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: GALERI FOTO BUKTI SERVIS LAPANGAN                                -->
    <!-- ========================================================================= -->
    <div x-show="showPhotosModal"
         x-cloak
         role="dialog"
         aria-modal="true"
         aria-label="Foto Bukti Servis Lapangan"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div @click.away="showPhotosModal = false"
             class="bg-surface rounded-2xl border-2 border-primary-border max-w-2xl w-full p-5 sm:p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between gap-3 pb-3 border-b border-primary-border">
                <div>
                    <h3 class="text-xl font-extrabold text-text-main">Foto Bukti Servis Lapangan</h3>
                    <p class="text-sm font-mono font-semibold text-primary" x-text="'No. Pesanan: ' + activeOrderNumber"></p>
                </div>
                <button type="button"
                        @click="showPhotosModal = false"
                        aria-label="Tutup modal foto bukti"
                        class="tap-target inline-flex items-center justify-center rounded-xl bg-bg-main hover:bg-primary-subtle text-text-main text-2xl font-bold cursor-pointer shrink-0">
                    &times;
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <template x-for="photo in activePhotos" :key="photo.id">
                    <div class="rounded-2xl border-2 border-primary-border overflow-hidden bg-surface shadow-xs flex flex-col justify-between">
                        <div class="relative bg-bg-main">
                            <img :src="photo.url || ('/storage/' + photo.photo_url)"
                                 alt="Bukti Servis"
                                 @click="window.open(photo.url || ('/storage/' + photo.photo_url), '_blank')"
                                 class="w-full h-44 object-cover cursor-pointer hover:opacity-95 transition-opacity">
                            <span class="absolute top-2.5 left-2.5 px-2.5 py-1 rounded-lg text-sm font-bold uppercase shadow-xs border"
                                  :class="photo.photo_type === 'sebelum' ? 'bg-[#FEF3C7] text-[#92400E] border-[#FCD34D]' : (photo.photo_type === 'sesudah' ? 'bg-[#DCFCE7] text-[#15803D] border-[#86EFAC]' : 'bg-[#E0F2FE] text-[#0369A1] border-[#BAE6FD]')"
                                  x-text="photo.photo_type"></span>
                        </div>
                        <div class="p-3.5 text-sm space-y-3">
                            <p class="text-text-main font-semibold leading-snug" x-text="photo.caption || 'Tanpa keterangan'"></p>
                            <div class="flex items-center justify-between gap-2 pt-2 border-t border-primary-border/60">
                                <a :href="photo.url || ('/storage/' + photo.photo_url)"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="min-h-[44px] inline-flex items-center text-sm font-bold text-primary hover:underline">
                                    Lihat Penuh &rarr;
                                </a>
                                <form :action="'/admin/order/foto/' + photo.id" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto bukti servis ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="min-h-[44px] px-3.5 py-2 rounded-xl border border-[#FCA5A5] text-[#B91C1C] hover:bg-[#FEE2E2] font-bold text-sm cursor-pointer transition-colors">
                                        Hapus Foto
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex justify-end pt-3 border-t border-primary-border">
                <button type="button"
                        @click="showPhotosModal = false"
                        class="min-h-[48px] px-6 py-2.5 text-base font-bold text-text-main bg-bg-main hover:bg-primary-subtle border border-primary-border rounded-xl cursor-pointer transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
