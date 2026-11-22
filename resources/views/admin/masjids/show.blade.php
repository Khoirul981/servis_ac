@extends('layouts.app', ['title' => 'Detail Masjid - ' . $masjid->name])

@section('content')
<div class="space-y-6">

    <!-- Tombol Kembali -->
    <a href="{{ route('admin.masjids.index') }}" class="text-xs font-medium text-gray-600 hover:text-gray-900 inline-flex items-center gap-1">
        &larr; Kembali ke Daftar Masjid
    </a>

    <!-- Header Profil Masjid -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Profil Masjid & Musholla</span>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $masjid->name }}</h1>
                <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mt-1">{{ $masjid->address }}</p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" 
                        onclick="openEditMasjidModal({{ $masjid->id }}, '{{ addslashes($masjid->name) }}', '{{ addslashes($masjid->address) }}', '{{ addslashes($masjid->pic_name) }}', '{{ $masjid->pic_phone }}')"
                        class="px-3.5 py-2 rounded-lg border border-gray-300 text-gray-800 text-xs font-semibold hover:bg-gray-50 cursor-pointer transition-colors">
                    Edit Profil Masjid
                </button>
                <form method="POST" action="{{ route('admin.masjids.destroy', $masjid->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data masjid {{ addslashes($masjid->name) }} beserta seluruh unit AC-nya?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3.5 py-2 rounded-lg border border-red-200 text-red-600 text-xs font-semibold hover:bg-red-50 cursor-pointer transition-colors">
                        Hapus Masjid
                    </button>
                </form>
            </div>
        </div>

        <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center gap-6 text-xs text-gray-700">
            <span>Pengurus (DKM): <strong>{{ $masjid->pic_name }}</strong></span>
            <span>WhatsApp: <strong class="font-mono">{{ $masjid->pic_phone }}</strong></span>
            <span>Servis Terakhir: <strong>{{ $masjid->last_service_date ? $masjid->last_service_date->translatedFormat('d M Y') : 'Belum pernah' }}</strong></span>
        </div>
    </div>

    <!-- DAFTAR UNIT AC & FORM TAMBAH -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">
                Daftar Unit AC Terpasang (Total: {{ $masjid->acUnits->sum('quantity') ?: $masjid->acUnits->count() }} Unit)
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @forelse($masjid->acUnits as $unit)
                <div class="p-4 rounded-lg border border-gray-200 bg-gray-50 space-y-2 text-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <span class="block font-semibold text-sm text-gray-900">{{ $unit->room_name }}</span>
                            <span class="px-2 py-0.5 rounded bg-white border border-gray-300 font-semibold text-[11px] text-[#1E3E34]">
                                {{ $unit->ac_type }}
                            </span>
                        </div>
                        <div class="text-gray-600 pt-1 flex items-center justify-between">
                            <span>Merk: <strong>{{ $unit->brand ?? '-' }}</strong></span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-[#EBF2EE] text-[#1E3E34] font-semibold border border-[#1E3E34]/20 text-[11px]">
                                {{ $unit->quantity ?? 1 }} Unit
                            </span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gray-200 flex items-center justify-end gap-1.5">
                        <button type="button" 
                                onclick="openEditUnitModal({{ $unit->id }}, '{{ addslashes($unit->room_name) }}', '{{ addslashes($unit->ac_type) }}', '{{ addslashes($unit->brand ?? '') }}', {{ $unit->quantity ?? 1 }})"
                                class="px-2.5 py-1 rounded border border-gray-300 hover:bg-white text-gray-700 font-medium transition-colors cursor-pointer text-[11px]">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('admin.masjids.ac.destroy', $unit->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus unit AC di {{ addslashes($unit->room_name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2.5 py-1 rounded border border-red-200 text-red-600 hover:bg-red-50 font-medium transition-colors cursor-pointer text-[11px]">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-3 p-6 text-center text-gray-400 text-xs">
                    Belum ada data unit AC terdaftar untuk masjid ini. Silakan tambahkan pada form di bawah.
                </div>
            @endforelse
        </div>

        <!-- Form Tambah Unit AC Baru -->
        <div class="pt-4 border-t border-gray-100">
            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Tambah Unit AC Baru:</h3>
            <form method="POST" action="{{ route('admin.masjids.ac.store', $masjid->id) }}" class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                @csrf
                <div class="sm:col-span-4">
                    <input type="text" name="room_name" required placeholder="Nama Ruang (mis: Shaf Depan)" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>
                <div class="sm:col-span-3">
                    <input type="text" name="ac_type" required placeholder="Tipe / PK (mis: 1.5 PK)" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>
                <div class="sm:col-span-3">
                    <input type="text" name="brand" placeholder="Merk (mis: Daikin, Sharp)" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>
                <div class="sm:col-span-2">
                    <input type="number" name="quantity" min="1" max="999" value="1" required placeholder="Jumlah" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white" title="Jumlah Unit AC">
                </div>
                <div class="sm:col-span-12">
                    <button type="submit" class="min-h-[44px] px-5 py-2.5 bg-[#1E3E34] hover:bg-[#142B24] text-white rounded-lg text-xs font-semibold cursor-pointer transition-colors w-full sm:w-auto">
                        + Simpan Unit AC
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- RIWAYAT SERVIS DI MASJID INI -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-3">
        <h2 class="text-base font-semibold text-gray-900">
            Riwayat Servis AC di Masjid Ini
        </h2>

        <div class="space-y-2">
            @forelse($masjid->orders as $order)
                <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-mono font-medium text-gray-900 block">{{ $order->order_number }}</span>
                        <span class="text-gray-500">{{ $order->ac_count }} Unit AC &bull; {{ $order->dkm_complaint }}</span>
                    </div>
                    <div class="text-right">
                        <span class="px-2.5 py-1 rounded-md font-semibold text-[11px] {{ $order->status === 'selesai' ? 'bg-[#DCFCE7] text-[#15803D]' : 'bg-gray-200 text-gray-800' }}">
                            {{ $order->statusLabel() }}
                        </span>
                        <span class="block text-[11px] text-gray-400 mt-1">{{ $order->created_at->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-400">Belum ada riwayat servis untuk masjid ini.</p>
            @endforelse
        </div>
    </div>

</div>

<!-- MODAL EDIT PROFIL MASJID -->
<div id="modal-edit-masjid" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-gray-200 max-w-lg w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">Perbarui Data Masjid</h3>
            <button type="button" onclick="closeEditMasjidModal()" class="text-gray-400 hover:text-gray-600 text-lg font-bold cursor-pointer">&times;</button>
        </div>

        <form id="form-edit-masjid" method="POST" action="" class="space-y-3.5">
            @csrf
            @method('PUT')

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-gray-800">Nama Masjid / Musholla <span class="text-red-500">*</span></label>
                <input type="text" id="edit-masjid-name" name="name" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-gray-800">Alamat Lengkap <span class="text-red-500">*</span></label>
                <textarea id="edit-masjid-address" name="address" rows="3" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white resize-none"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">Nama Kontak DKM / Marbot <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-masjid-pic-name" name="pic_name" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">No. WhatsApp Pengurus <span class="text-red-500">*</span></label>
                    <input type="tel" id="edit-masjid-pic-phone" name="pic_phone" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white font-mono">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeEditMasjidModal()" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1E3E34] hover:bg-[#142B24] text-white text-xs font-semibold cursor-pointer transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT UNIT AC -->
<div id="modal-edit-unit" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-gray-200 max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">Perbarui Data Unit AC</h3>
            <button type="button" onclick="closeEditUnitModal()" class="text-gray-400 hover:text-gray-600 text-lg font-bold cursor-pointer">&times;</button>
        </div>

        <form id="form-edit-unit" method="POST" action="" class="space-y-3.5">
            @csrf
            @method('PUT')

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-gray-800">Nama Ruangan / Posisi AC <span class="text-red-500">*</span></label>
                <input type="text" id="edit-unit-room" name="room_name" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-gray-800">Tipe / Kapasitas PK <span class="text-red-500">*</span></label>
                <input type="text" id="edit-unit-type" name="ac_type" required placeholder="Contoh: 1.5 PK, 2 PK" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">Merk AC</label>
                    <input type="text" id="edit-unit-brand" name="brand" placeholder="Contoh: Daikin, Panasonic, Sharp" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">Jumlah Unit <span class="text-red-500">*</span></label>
                    <input type="number" id="edit-unit-quantity" name="quantity" min="1" max="999" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeEditUnitModal()" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1E3E34] hover:bg-[#142B24] text-white text-xs font-semibold cursor-pointer transition-colors">
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
</script>
@endsection
