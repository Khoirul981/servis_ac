@extends('layouts.app', ['title' => 'Master Data Masjid - Servis AC Masjid'])

@section('content')
<div class="space-y-6">

    <!-- Header & Aksi Atas -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Master Data Masjid & Musholla</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Daftar seluruh rumah ibadah yang telah terdaftar dalam sistem perawatan AC.</p>
        </div>

        <div class="w-full sm:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <!-- Pencarian -->
            <form method="GET" action="{{ route('admin.masjids.index') }}" class="flex items-center gap-2">
                <input type="text" 
                       name="search" 
                       value="{{ $search }}"
                       placeholder="Cari nama atau alamat masjid..." 
                       class="px-4 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] w-full sm:w-60 bg-white">
                <button type="submit" class="px-4 py-2.5 bg-gray-900 hover:bg-black text-white rounded-lg text-xs font-medium cursor-pointer transition-colors">
                    Cari
                </button>
            </form>

            <!-- Tombol Tambah Masjid Baru -->
            <button type="button" 
                    onclick="openCreateMasjidModal()" 
                    class="min-h-[44px] px-4 py-2.5 bg-[#1E3E34] hover:bg-[#142B24] text-white rounded-lg text-xs font-semibold cursor-pointer transition-colors text-center">
                + Tambah Masjid Baru
            </button>
        </div>
    </div>

    <!-- Tabel Daftar Masjid -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 border-b border-gray-200 uppercase text-gray-700 font-semibold text-[11px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Nama Masjid & Alamat</th>
                        <th class="py-3.5 px-4 whitespace-nowrap">Pengurus (DKM / Marbot)</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Unit AC</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Servis Terakhir</th>
                        <th class="py-3.5 px-4 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($masjids as $masjid)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <strong class="text-sm font-semibold text-gray-900 block">{{ $masjid->name }}</strong>
                                <span class="text-gray-500 text-xs">{{ $masjid->address }}</span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-medium text-gray-800 block">{{ $masjid->pic_name }}</span>
                                <span class="text-gray-500 font-mono">{{ $masjid->pic_phone }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-md bg-[#EBF2EE] text-[#1E3E34] font-semibold border border-[#1E3E34]/20 whitespace-nowrap text-xs">
                                    {{ $masjid->ac_units_count }} Unit
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center text-gray-600 whitespace-nowrap">
                                {{ $masjid->last_service_date ? $masjid->last_service_date->translatedFormat('d M Y') : 'Belum pernah' }}
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.masjids.show', $masjid->id) }}" 
                                       class="px-2.5 py-1.5 rounded-md border border-gray-300 hover:bg-gray-100 text-gray-800 font-medium transition-colors text-decoration-none whitespace-nowrap">
                                        Kelola Unit AC
                                    </a>
                                    <button type="button" 
                                            onclick="openEditMasjidModal({{ $masjid->id }}, '{{ addslashes($masjid->name) }}', '{{ addslashes($masjid->address) }}', '{{ addslashes($masjid->pic_name) }}', '{{ $masjid->pic_phone }}')"
                                            class="px-2.5 py-1.5 rounded-md border border-gray-300 hover:bg-gray-100 text-gray-800 font-medium transition-colors cursor-pointer whitespace-nowrap">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.masjids.destroy', $masjid->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data masjid {{ addslashes($masjid->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-md border border-red-200 text-red-600 hover:bg-red-50 font-medium transition-colors cursor-pointer whitespace-nowrap">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-gray-400">Tidak ada data masjid ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $masjids->links() }}
    </div>

</div>

<!-- MODAL TAMBAH MASJID BARU -->
<div id="modal-create-masjid" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-gray-200 max-w-lg w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">Tambah Masjid / Musholla Baru</h3>
            <button type="button" onclick="closeCreateMasjidModal()" class="text-gray-400 hover:text-gray-600 text-lg font-bold cursor-pointer">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.masjids.store') }}" class="space-y-3.5">
            @csrf

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-gray-800">Nama Masjid / Musholla <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Masjid Jamie Al-Barkah" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-gray-800">Alamat Lengkap <span class="text-red-500">*</span></label>
                <textarea name="address" rows="3" required placeholder="Jalan, RT/RW, Kelurahan, Kecamatan..." class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white resize-none"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">Nama Kontak DKM / Marbot <span class="text-red-500">*</span></label>
                    <input type="text" name="pic_name" required placeholder="Nama pengurus" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">No. WhatsApp Pengurus <span class="text-red-500">*</span></label>
                    <input type="tel" name="pic_phone" required placeholder="08xxxxxxxxxx" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white font-mono">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeCreateMasjidModal()" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1E3E34] hover:bg-[#142B24] text-white text-xs font-semibold cursor-pointer transition-colors">
                    Simpan Data Masjid
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT MASJID -->
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

<script>
    function openCreateMasjidModal() {
        document.getElementById('modal-create-masjid').classList.remove('hidden');
    }

    function closeCreateMasjidModal() {
        document.getElementById('modal-create-masjid').classList.add('hidden');
    }

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
</script>
@endsection
