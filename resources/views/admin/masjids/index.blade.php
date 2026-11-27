@extends('layouts.app', ['title' => 'Master Data Masjid - Servis AC Masjid'])

@section('content')
<div class="space-y-6">

    <!-- Ringkasan Error Validasi -->
    @if($errors->any())
        <div role="alert" class="p-4 sm:p-5 rounded-2xl bg-[#FEE2E2] border-2 border-[#FCA5A5] text-[#7F1D1D] space-y-1.5">
            <strong class="block text-base font-extrabold">Gagal menyimpan data masjid:</strong>
            <ul class="list-disc list-inside text-sm sm:text-base space-y-0.5 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header & Aksi Atas -->
    <section class="bg-surface rounded-2xl border border-primary-border p-5 sm:p-6 shadow-sm space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-text-main tracking-tight">
                    Master Data Masjid &amp; Musholla
                </h1>
                <p class="text-sm sm:text-base text-text-muted">
                    Daftar seluruh rumah ibadah beserta inventaris unit AC yang terdaftar dalam sistem.
                </p>
            </div>

            <button type="button"
                    onclick="openCreateMasjidModal()"
                    class="w-full lg:w-auto min-h-[50px] px-5 py-3 bg-primary hover:bg-primary-hover text-white rounded-xl text-base font-bold cursor-pointer transition-colors shadow-xs flex items-center justify-center gap-2 shrink-0">
                <span>+ Tambah Masjid</span>
            </button>
        </div>

        <!-- Kotak Pencarian Besar -->
        <form method="GET" action="{{ route('admin.masjids.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2 border-t border-primary-border/70">
            <div class="flex-1">
                <label for="masjid_search_query" class="sr-only">Cari nama, alamat, atau pengurus masjid</label>
                <input type="text"
                       id="masjid_search_query"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Cari nama masjid, alamat, atau nama pengurus..."
                       class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                        class="flex-1 sm:flex-none min-h-[50px] px-6 py-3 bg-primary hover:bg-primary-hover text-white rounded-xl text-base font-bold cursor-pointer transition-colors">
                    Cari Masjid
                </button>
                @if($search !== '')
                    <a href="{{ route('admin.masjids.index') }}"
                       class="min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border bg-bg-main hover:bg-primary-subtle text-text-main text-base font-bold inline-flex items-center justify-center transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </section>

    <!-- ========================================================================= -->
    <!-- TAMPILAN MOBILE (< lg): KARTU MASJID RAMAH ORANG TUA                      -->
    <!-- ========================================================================= -->
    <div class="lg:hidden space-y-4">
        @forelse($masjids as $masjid)
            <article class="bg-surface rounded-2xl border-2 border-primary-border p-5 shadow-sm space-y-4">
                <div class="flex items-start justify-between gap-3 pb-3 border-b border-primary-border/70">
                    <div class="min-w-0">
                        <h2 class="text-lg font-extrabold text-text-main leading-snug">{{ $masjid->name }}</h2>
                        <p class="text-sm text-text-muted mt-1 leading-relaxed">{{ $masjid->address }}</p>
                    </div>
                    <span class="shrink-0 px-3 py-1.5 rounded-lg bg-primary-subtle text-primary font-bold border border-primary-border text-sm whitespace-nowrap">
                        {{ $masjid->total_ac_units ?? $masjid->ac_units_count ?? 0 }} Unit
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm bg-bg-main p-3.5 rounded-xl border border-primary-border/60">
                    <div>
                        <span class="block text-text-muted">Pengurus (DKM / Marbot):</span>
                        <strong class="block text-base font-bold text-text-main mt-0.5">{{ $masjid->pic_name }}</strong>
                        <span class="block font-mono text-primary font-semibold">{{ $masjid->pic_phone }}</span>
                    </div>
                    <div>
                        <span class="block text-text-muted">Servis Terakhir:</span>
                        <strong class="block text-base font-bold text-text-main mt-0.5">
                            {{ $masjid->last_service_date ? $masjid->last_service_date->translatedFormat('d M Y') : 'Belum pernah' }}
                        </strong>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2.5">
                    <a href="{{ route('admin.masjids.show', $masjid->id) }}"
                       class="flex-1 min-h-[48px] px-4 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-base transition-colors flex items-center justify-center text-center">
                        Kelola Unit AC
                    </a>
                    <div class="flex gap-2.5">
                        <button type="button"
                                onclick="openEditMasjidModal({{ $masjid->id }}, '{{ addslashes($masjid->name) }}', '{{ addslashes($masjid->address) }}', '{{ addslashes($masjid->pic_name) }}', '{{ $masjid->pic_phone }}')"
                                class="flex-1 sm:flex-none min-h-[48px] px-5 py-2.5 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-base transition-colors cursor-pointer">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('admin.masjids.destroy', $masjid->id) }}" class="flex-1 sm:flex-none" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data masjid {{ addslashes($masjid->name) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full min-h-[48px] px-5 py-2.5 rounded-xl border-2 border-[#FCA5A5] text-[#B91C1C] bg-surface hover:bg-[#FEE2E2] font-bold text-base transition-colors cursor-pointer">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="bg-surface rounded-2xl border-2 border-dashed border-primary-border p-10 text-center space-y-1">
                <p class="text-base font-bold text-text-main">Tidak ada data masjid ditemukan.</p>
                <p class="text-sm text-text-muted">Coba kata kunci lain atau tambahkan data masjid baru.</p>
            </div>
        @endforelse
    </div>

    <!-- ========================================================================= -->
    <!-- TAMPILAN DESKTOP (lg+): TABEL MASTER MASJID                               -->
    <!-- ========================================================================= -->
    <div class="hidden lg:block bg-surface rounded-2xl border border-primary-border overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-subtle/70 border-b border-primary-border uppercase text-primary font-bold text-sm tracking-wide">
                    <tr>
                        <th class="py-4 px-4">Nama Masjid &amp; Alamat</th>
                        <th class="py-4 px-4 whitespace-nowrap">Pengurus (DKM / Marbot)</th>
                        <th class="py-4 px-4 text-center whitespace-nowrap">Unit AC</th>
                        <th class="py-4 px-4 text-center whitespace-nowrap">Servis Terakhir</th>
                        <th class="py-4 px-4 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-border/60 text-sm">
                    @forelse($masjids as $masjid)
                        <tr class="hover:bg-primary-subtle/30 transition-colors">
                            <td class="py-4 px-4 max-w-md">
                                <strong class="text-base font-bold text-text-main block">{{ $masjid->name }}</strong>
                                <span class="text-text-muted text-sm block mt-0.5">{{ $masjid->address }}</span>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <span class="font-bold text-base text-text-main block">{{ $masjid->pic_name }}</span>
                                <span class="text-primary font-mono font-semibold text-sm">{{ $masjid->pic_phone }}</span>
                            </td>
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center justify-center px-3.5 py-1.5 rounded-lg bg-primary-subtle text-primary font-bold border border-primary-border whitespace-nowrap text-sm">
                                    {{ $masjid->total_ac_units ?? $masjid->ac_units_count ?? 0 }} Unit
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center text-text-main font-medium whitespace-nowrap">
                                {{ $masjid->last_service_date ? $masjid->last_service_date->translatedFormat('d M Y') : 'Belum pernah' }}
                            </td>
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.masjids.show', $masjid->id) }}"
                                       class="min-h-[44px] px-3.5 py-2 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-sm transition-colors inline-flex items-center whitespace-nowrap">
                                        Kelola Unit AC
                                    </a>
                                    <button type="button"
                                            onclick="openEditMasjidModal({{ $masjid->id }}, '{{ addslashes($masjid->name) }}', '{{ addslashes($masjid->address) }}', '{{ addslashes($masjid->pic_name) }}', '{{ $masjid->pic_phone }}')"
                                            class="min-h-[44px] px-3.5 py-2 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-sm transition-colors cursor-pointer whitespace-nowrap">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.masjids.destroy', $masjid->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data masjid {{ addslashes($masjid->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="min-h-[44px] px-3.5 py-2 rounded-xl border border-[#FCA5A5] text-[#B91C1C] hover:bg-[#FEE2E2] font-bold text-sm transition-colors cursor-pointer whitespace-nowrap">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-text-muted text-base font-medium">
                                Tidak ada data masjid ditemukan.
                            </td>
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

<!-- MODAL TAMBAH MASJID BARU (PERTAHANKAN ID #modal-create-masjid) -->
<div id="modal-create-masjid" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if (event.target === this) closeCreateMasjidModal();">
    <div class="bg-surface rounded-2xl border-2 border-primary-border max-w-lg w-full p-5 sm:p-6 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between gap-3 pb-3 border-b border-primary-border">
            <h3 class="text-xl font-extrabold text-text-main">Tambah Masjid / Musholla Baru</h3>
            <button type="button" onclick="closeCreateMasjidModal()" aria-label="Tutup modal tambah masjid" class="tap-target inline-flex items-center justify-center rounded-xl bg-bg-main hover:bg-primary-subtle text-text-main text-2xl font-bold cursor-pointer shrink-0">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.masjids.store') }}" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label class="block text-base font-bold text-text-main">Nama Masjid / Musholla <span class="text-[#B91C1C]">*</span></label>
                <input type="text" name="name" required placeholder="Contoh: Masjid Jamie Al-Barkah" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface">
            </div>

            <div class="space-y-1.5">
                <label class="block text-base font-bold text-text-main">Alamat Lengkap <span class="text-[#B91C1C]">*</span></label>
                <textarea name="address" rows="3" required placeholder="Jalan, RT/RW, Kelurahan, Kecamatan..." class="w-full px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-text-main">Nama Kontak DKM / Marbot <span class="text-[#B91C1C]">*</span></label>
                    <input type="text" name="pic_name" required placeholder="Nama pengurus" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-text-main">No. WhatsApp Pengurus <span class="text-[#B91C1C]">*</span></label>
                    <input type="tel" name="pic_phone" required placeholder="08xxxxxxxxxx" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface font-mono">
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-primary-border">
                <button type="button" onclick="closeCreateMasjidModal()" class="min-h-[48px] px-5 py-2.5 rounded-xl border border-primary-border bg-bg-main text-text-main text-base font-bold hover:bg-primary-subtle cursor-pointer transition-colors">
                    Batal
                </button>
                <button type="submit" class="min-h-[48px] px-6 py-2.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-base font-bold cursor-pointer transition-colors shadow-xs">
                    Simpan Data Masjid
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT MASJID (PERTAHANKAN SEMUA DOM ID VANILLA JS) -->
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

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCreateMasjidModal();
            closeEditMasjidModal();
        }
    });
</script>
@endsection
