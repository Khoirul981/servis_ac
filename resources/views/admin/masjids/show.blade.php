@extends('layouts.app', ['title' => 'Detail Masjid - ' . $masjid->name])

@section('content')
@php
    $cleanDkmPhone = preg_replace('/[^0-9]/', '', (string) $masjid->pic_phone);
    if (str_starts_with($cleanDkmPhone, '08')) {
        $cleanDkmPhone = '628' . substr($cleanDkmPhone, 2);
    }
    $waDkmUrl = 'https://wa.me/' . $cleanDkmPhone;
    $totalAcUnits = $masjid->acUnits->sum('quantity') ?: $masjid->acUnits->count();
@endphp

<div class="space-y-6">

    <!-- Tombol Kembali -->
    <div>
        <a href="{{ route('admin.masjids.index') }}"
           class="min-h-[44px] inline-flex items-center gap-1.5 text-base font-bold text-primary hover:text-primary-hover transition-colors">
            <span aria-hidden="true">&larr;</span>
            <span>Kembali ke Daftar Masjid</span>
        </a>
    </div>

    <!-- Ringkasan Error Validasi -->
    @if($errors->any())
        <div role="alert" class="p-4 sm:p-5 rounded-2xl bg-[#FEE2E2] border-2 border-[#FCA5A5] text-[#7F1D1D] space-y-1.5">
            <strong class="block text-base font-extrabold">Mohon periksa kembali data yang dimasukkan:</strong>
            <ul class="list-disc list-inside text-sm sm:text-base space-y-0.5 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- KARTU RINGKASAN PROFIL MASJID                                             -->
    <!-- ========================================================================= -->
    <section class="bg-surface rounded-2xl border-2 border-primary-border p-5 sm:p-7 shadow-sm space-y-5">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-lg bg-primary-subtle border border-primary-border text-sm font-bold text-primary">
                        Profil Masjid &amp; Musholla
                    </span>
                    <span class="px-3 py-1 rounded-lg bg-primary text-white text-sm font-bold">
                        {{ $totalAcUnits }} Unit AC Terpasang
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-text-main tracking-tight pt-1">
                    {{ $masjid->name }}
                </h1>
                <p class="text-base text-text-muted leading-relaxed">
                    {{ $masjid->address }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 shrink-0">
                <a href="{{ $waDkmUrl }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="min-h-[48px] px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-base font-bold transition-colors inline-flex items-center justify-center gap-2 shadow-xs">
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12.031 2C6.51 2 2.02 6.489 2.02 12.011c0 1.763.461 3.485 1.337 5.002L2 22l5.118-1.342a9.97 9.97 0 0 0 4.913 1.296h.004c5.52 0 10.011-4.489 10.011-10.011C22.046 6.489 17.555 2 12.031 2Zm5.829 14.234c-.245.691-1.425 1.321-1.976 1.404-.512.077-1.178.109-1.901-.121-.438-.139-1.002-.326-1.724-.639-3.035-1.31-5.016-4.377-5.168-4.579-.151-.202-1.233-1.641-1.233-3.131 0-1.49.779-2.222 1.055-2.525.276-.303.603-.379.804-.379.201 0 .402.002.578.011.186.009.435-.071.681.519.251.605.854 2.084.929 2.235.075.151.126.328.025.53-.101.202-.151.328-.301.505-.151.177-.317.395-.452.53-.151.151-.308.315-.132.618.176.303.782 1.29 1.679 2.089 1.153 1.028 2.126 1.347 2.428 1.498.302.151.478.126.654-.076.176-.202.754-.883.955-1.186.201-.303.402-.252.678-.151.276.101 1.759.83 2.061.981.302.151.503.227.578.353.076.126.076.731-.169 1.422Z" />
                    </svg>
                    <span>WhatsApp DKM</span>
                </a>
                <button type="button"
                        onclick="openEditMasjidModal({{ $masjid->id }}, '{{ addslashes($masjid->name) }}', '{{ addslashes($masjid->address) }}', '{{ addslashes($masjid->pic_name) }}', '{{ $masjid->pic_phone }}')"
                        class="min-h-[48px] px-4 py-2.5 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary text-base font-bold cursor-pointer transition-colors">
                    Edit Profil Masjid
                </button>
                <form method="POST" action="{{ route('admin.masjids.destroy', $masjid->id) }}" class="sm:inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data masjid {{ addslashes($masjid->name) }} beserta seluruh unit AC-nya?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full sm:w-auto min-h-[48px] px-4 py-2.5 rounded-xl border-2 border-[#FCA5A5] bg-surface hover:bg-[#FEE2E2] text-[#B91C1C] text-base font-bold cursor-pointer transition-colors">
                        Hapus Masjid
                    </button>
                </form>
            </div>
        </div>

        <div class="pt-4 border-t border-primary-border/70 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm sm:text-base">
            <div class="p-3.5 rounded-xl bg-bg-main border border-primary-border/60">
                <span class="block text-sm text-text-muted">Pengurus (DKM / Marbot)</span>
                <strong class="block text-base font-bold text-text-main mt-0.5">{{ $masjid->pic_name }}</strong>
            </div>
            <div class="p-3.5 rounded-xl bg-bg-main border border-primary-border/60">
                <span class="block text-sm text-text-muted">Nomor WhatsApp</span>
                <strong class="block text-base font-bold font-mono text-primary mt-0.5">{{ $masjid->pic_phone }}</strong>
            </div>
            <div class="p-3.5 rounded-xl bg-bg-main border border-primary-border/60">
                <span class="block text-sm text-text-muted">Servis Terakhir</span>
                <strong class="block text-base font-bold text-text-main mt-0.5">
                    {{ $masjid->last_service_date ? $masjid->last_service_date->translatedFormat('d F Y') : 'Belum pernah' }}
                </strong>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- DAFTAR UNIT AC & FORM TAMBAH UNIT                                         -->
    <!-- ========================================================================= -->
    <section class="bg-surface rounded-2xl border border-primary-border p-5 sm:p-6 shadow-sm space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-primary-border/70">
            <div>
                <h2 class="text-xl font-extrabold text-text-main">Inventaris Unit AC Terpasang</h2>
                <p class="text-sm text-text-muted">Data unit ini otomatis tampil saat DKM memesan servis baru.</p>
            </div>
            <span class="px-3.5 py-1.5 rounded-lg bg-primary-subtle border border-primary-border text-primary text-sm font-bold">
                Total: {{ $totalAcUnits }} Unit
            </span>
        </div>

        <!-- Grid Kartu Unit AC -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($masjid->acUnits as $unit)
                <div class="p-4 rounded-2xl border-2 border-primary-border bg-bg-main/70 space-y-3 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-extrabold text-base text-text-main leading-snug">{{ $unit->room_name }}</h3>
                            <span class="shrink-0 px-2.5 py-1 rounded-lg bg-surface border border-primary-border font-bold text-sm text-primary">
                                {{ $unit->ac_type }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-2 text-sm pt-1">
                            <span class="text-text-muted">Merek: <strong class="text-text-main">{{ $unit->brand ?? '-' }}</strong></span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-primary-subtle text-primary font-bold border border-primary-border">
                                {{ $unit->quantity ?? 1 }} Unit
                            </span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-primary-border/70 flex items-center gap-2">
                        <button type="button"
                                onclick="openEditUnitModal({{ $unit->id }}, '{{ addslashes($unit->room_name) }}', '{{ addslashes($unit->ac_type) }}', '{{ addslashes($unit->brand ?? '') }}', {{ $unit->quantity ?? 1 }})"
                                class="flex-1 min-h-[44px] px-3 py-2 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-sm transition-colors cursor-pointer">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('admin.masjids.ac.destroy', $unit->id) }}" class="flex-1" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit AC di {{ addslashes($unit->room_name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full min-h-[44px] px-3 py-2 rounded-xl border border-[#FCA5A5] bg-surface text-[#B91C1C] hover:bg-[#FEE2E2] font-bold text-sm transition-colors cursor-pointer">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 p-8 rounded-2xl border-2 border-dashed border-primary-border text-center text-text-muted text-base font-medium">
                    Belum ada data unit AC terdaftar untuk masjid ini. Silakan tambahkan pada formulir di bawah.
                </div>
            @endforelse
        </div>

        <!-- Form Tambah Unit AC Baru -->
        <div class="pt-5 border-t border-primary-border space-y-4">
            <h3 class="text-lg font-extrabold text-text-main">Tambah Unit AC Baru</h3>
            <form method="POST" action="{{ route('admin.masjids.ac.store', $masjid->id) }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
                @csrf
                <div class="lg:col-span-4 space-y-1.5">
                    <label for="new_ac_room" class="block text-sm font-bold text-text-main">Nama Ruang <span class="text-[#B91C1C]">*</span></label>
                    <input type="text" id="new_ac_room" name="room_name" required placeholder="Contoh: Shaf Depan" class="w-full min-h-[48px] px-4 py-2.5 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface">
                </div>
                <div class="lg:col-span-3 space-y-1.5">
                    <label for="new_ac_type" class="block text-sm font-bold text-text-main">Tipe / PK <span class="text-[#B91C1C]">*</span></label>
                    <input type="text" id="new_ac_type" name="ac_type" required placeholder="Contoh: 1.5 PK" class="w-full min-h-[48px] px-4 py-2.5 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface">
                </div>
                <div class="lg:col-span-3 space-y-1.5">
                    <label for="new_ac_brand" class="block text-sm font-bold text-text-main">Merek (Opsional)</label>
                    <input type="text" id="new_ac_brand" name="brand" placeholder="Contoh: Daikin, Sharp" class="w-full min-h-[48px] px-4 py-2.5 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface">
                </div>
                <div class="lg:col-span-2 space-y-1.5">
                    <label for="new_ac_qty" class="block text-sm font-bold text-text-main">Jumlah <span class="text-[#B91C1C]">*</span></label>
                    <input type="number" id="new_ac_qty" name="quantity" min="1" max="999" value="1" required class="w-full min-h-[48px] px-4 py-2.5 rounded-xl border-2 border-primary-border text-base text-text-main font-mono font-bold outline-none focus:border-primary bg-surface">
                </div>
                <div class="sm:col-span-2 lg:col-span-12 pt-1">
                    <button type="submit" class="w-full sm:w-auto min-h-[50px] px-6 py-3 bg-primary hover:bg-primary-hover text-white rounded-xl text-base font-bold cursor-pointer transition-colors shadow-xs">
                        + Simpan Unit AC
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- RIWAYAT SERVIS DI MASJID INI                                              -->
    <!-- ========================================================================= -->
    <section class="bg-surface rounded-2xl border border-primary-border p-5 sm:p-6 shadow-sm space-y-4">
        <h2 class="text-xl font-extrabold text-text-main">
            Riwayat Servis AC di Masjid Ini
        </h2>

        <div class="space-y-3">
            @forelse($masjid->orders as $order)
                <div class="p-4 rounded-xl border border-primary-border bg-bg-main/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="space-y-1">
                        <span class="font-mono font-extrabold text-base text-primary block">{{ $order->order_number }}</span>
                        <p class="text-sm sm:text-base font-semibold text-text-main">
                            {{ $order->ac_count }} Unit AC &bull; {{ $order->dkm_complaint }}
                        </p>
                        @if($order->technician)
                            <span class="text-sm text-text-muted block">Teknisi: <strong class="text-text-main">{{ $order->technician->name }}</strong></span>
                        @endif
                    </div>
                    <div class="sm:text-right flex sm:flex-col items-center sm:items-end justify-between gap-2">
                        <span class="px-3 py-1 rounded-lg font-bold text-sm {{ $order->status === 'selesai' ? 'bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC]' : ($order->status === 'batal' ? 'bg-[#FEE2E2] text-[#B91C1C] border border-[#FCA5A5]' : 'bg-primary-subtle text-primary border border-primary-border') }}">
                            {{ $order->statusLabel() }}
                        </span>
                        <span class="text-sm font-medium text-text-muted">{{ $order->created_at->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
            @empty
                <p class="text-base text-text-muted">Belum ada riwayat servis untuk masjid ini.</p>
            @endforelse
        </div>
    </section>

</div>

<!-- MODAL EDIT PROFIL MASJID (PERTAHANKAN SEMUA DOM ID VANILLA JS) -->
<div id="modal-edit-masjid" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if (event.target === this) closeEditMasjidModal();">
    <div class="bg-surface rounded-2xl border-2 border-primary-border max-w-lg w-full p-5 sm:p-6 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between gap-3 pb-3 border-b border-primary-border">
            <h3 class="text-xl font-extrabold text-text-main">Perbarui Data Masjid</h3>
            <button type="button" onclick="closeEditMasjidModal()" aria-label="Tutup modal edit masjid" class="tap-target inline-flex items-center justify-center rounded-xl bg-bg-main hover:bg-primary-subtle text-text-main text-2xl font-bold cursor-pointer shrink-0">&times;</button>
        </div>

        <form id="form-edit-masjid" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label for="edit-masjid-name" class="block text-base font-bold text-text-main">Nama Masjid / Musholla <span class="text-[#B91C1C]">*</span></label>
                <input type="text" id="edit-masjid-name" name="name" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface">
            </div>

            <div class="space-y-1.5">
                <label for="edit-masjid-address" class="block text-base font-bold text-text-main">Alamat Lengkap <span class="text-[#B91C1C]">*</span></label>
                <textarea id="edit-masjid-address" name="address" rows="3" required class="w-full px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="edit-masjid-pic-name" class="block text-base font-bold text-text-main">Nama Kontak DKM / Marbot <span class="text-[#B91C1C]">*</span></label>
                    <input type="text" id="edit-masjid-pic-name" name="pic_name" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface">
                </div>

                <div class="space-y-1.5">
                    <label for="edit-masjid-pic-phone" class="block text-base font-bold text-text-main">No. WhatsApp Pengurus <span class="text-[#B91C1C]">*</span></label>
                    <input type="tel" id="edit-masjid-pic-phone" name="pic_phone" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface font-mono">
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-primary-border">
                <button type="button" onclick="closeEditMasjidModal()" class="min-h-[48px] px-5 py-2.5 rounded-xl border border-primary-border bg-bg-main text-text-main text-base font-bold hover:bg-primary-subtle cursor-pointer transition-colors">
                    Batal
                </button>
                <button type="submit" class="min-h-[48px] px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-base font-bold cursor-pointer transition-colors shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT UNIT AC (PERTAHANKAN SEMUA DOM ID VANILLA JS) -->
<div id="modal-edit-unit" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if (event.target === this) closeEditUnitModal();">
    <div class="bg-surface rounded-2xl border-2 border-primary-border max-w-md w-full p-5 sm:p-6 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between gap-3 pb-3 border-b border-primary-border">
            <h3 class="text-xl font-extrabold text-text-main">Perbarui Data Unit AC</h3>
            <button type="button" onclick="closeEditUnitModal()" aria-label="Tutup modal edit unit AC" class="tap-target inline-flex items-center justify-center rounded-xl bg-bg-main hover:bg-primary-subtle text-text-main text-2xl font-bold cursor-pointer shrink-0">&times;</button>
        </div>

        <form id="form-edit-unit" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label for="edit-unit-room" class="block text-base font-bold text-text-main">Nama Ruangan / Posisi AC <span class="text-[#B91C1C]">*</span></label>
                <input type="text" id="edit-unit-room" name="room_name" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface">
            </div>

            <div class="space-y-1.5">
                <label for="edit-unit-type" class="block text-base font-bold text-text-main">Tipe / Kapasitas PK <span class="text-[#B91C1C]">*</span></label>
                <input type="text" id="edit-unit-type" name="ac_type" required placeholder="Contoh: 1.5 PK, 2 PK" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="edit-unit-brand" class="block text-base font-bold text-text-main">Merek AC</label>
                    <input type="text" id="edit-unit-brand" name="brand" placeholder="Contoh: Daikin, Sharp" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface">
                </div>

                <div class="space-y-1.5">
                    <label for="edit-unit-quantity" class="block text-base font-bold text-text-main">Jumlah Unit <span class="text-[#B91C1C]">*</span></label>
                    <input type="number" id="edit-unit-quantity" name="quantity" min="1" max="999" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main font-mono font-bold outline-none focus:border-primary bg-surface">
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-primary-border">
                <button type="button" onclick="closeEditUnitModal()" class="min-h-[48px] px-5 py-2.5 rounded-xl border border-primary-border bg-bg-main text-text-main text-base font-bold hover:bg-primary-subtle cursor-pointer transition-colors">
                    Batal
                </button>
                <button type="submit" class="min-h-[48px] px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-base font-bold cursor-pointer transition-colors shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditMasjidModal(id, name, address, picName, picPhone) {
        document.getElementById('form-edit-masjid').action = '/admin/masjids/' + id;
        document.getElementById('edit-masjid-name').value = name;
        document.getElementById('edit-masjid-address').value = address;
        document.getElementById('edit-masjid-pic-name').value = picName;
        document.getElementById('edit-masjid-pic-phone').value = picPhone;
        document.getElementById('modal-edit-masjid').classList.remove('hidden');
    }

    function closeEditMasjidModal() {
        document.getElementById('modal-edit-masjid').classList.add('hidden');
    }

    function openEditUnitModal(id, roomName, acType, brand, quantity) {
        document.getElementById('form-edit-unit').action = '/admin/ac/' + id;
        document.getElementById('edit-unit-room').value = roomName;
        document.getElementById('edit-unit-type').value = acType;
        document.getElementById('edit-unit-brand').value = brand;
        document.getElementById('edit-unit-quantity').value = quantity || 1;
        document.getElementById('modal-edit-unit').classList.remove('hidden');
    }

    function closeEditUnitModal() {
        document.getElementById('modal-edit-unit').classList.add('hidden');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeEditMasjidModal();
            closeEditUnitModal();
        }
    });
</script>
@endsection
