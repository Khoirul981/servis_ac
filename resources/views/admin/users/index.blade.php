@extends('layouts.app', ['title' => 'Master Petugas - Servis AC Masjid'])

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <section class="bg-surface rounded-2xl border border-primary-border p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-text-main tracking-tight">
                Manajemen Akun Petugas
            </h1>
            <p class="text-sm sm:text-base text-text-muted">
                Kelola akun Admin Pengelola dan Teknisi Lapangan yang memiliki akses ke sistem.
            </p>
        </div>
        <span class="self-start sm:self-center px-3.5 py-2 rounded-xl bg-primary-subtle border border-primary-border text-primary text-sm font-bold">
            Total: {{ $users->count() }} Petugas Aktif
        </span>
    </section>

    <!-- Ringkasan Error Validasi -->
    @if($errors->any())
        <div role="alert" class="p-4 sm:p-5 rounded-2xl bg-[#FEE2E2] border-2 border-[#FCA5A5] text-[#7F1D1D] space-y-1.5">
            <strong class="block text-base font-extrabold">Gagal menyimpan data petugas:</strong>
            <ul class="list-disc list-inside text-sm sm:text-base space-y-0.5 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Layout 2 Kolom Seimbang di Desktop (Daftar Petugas + Form Tambah) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- ===================================================================== -->
        <!-- DAFTAR PETUGAS (KARTU DI HP, TABEL DI DESKTOP)                        -->
        <!-- ===================================================================== -->
        <div class="lg:col-span-8 space-y-4">
            <h2 class="text-lg font-extrabold text-text-main px-1">
                Daftar Akun Petugas ({{ $users->count() }})
            </h2>

            <!-- Tampilan Mobile (< lg): Kartu Petugas -->
            <div class="lg:hidden space-y-4">
                @foreach($users as $user)
                    <article class="bg-surface rounded-2xl border-2 border-primary-border p-5 shadow-sm space-y-4">
                        <div class="flex flex-wrap items-start justify-between gap-2 pb-3 border-b border-primary-border/70">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-lg font-extrabold text-text-main">{{ $user->name }}</h3>
                                    @if($user->id === auth()->id())
                                        <span class="px-2.5 py-0.5 rounded-md bg-bg-main border border-primary-border text-text-main text-sm font-bold">
                                            Akun Anda
                                        </span>
                                    @endif
                                </div>
                                <span class="block text-sm font-mono text-text-muted mt-1 break-all">{{ $user->email }}</span>
                                <span class="block text-sm font-mono font-semibold text-primary mt-0.5">{{ $user->phone ?? '-' }}</span>
                            </div>

                            <!-- Badge Peran Tegas -->
                            @if($user->isAdmin())
                                <span class="px-3 py-1.5 rounded-lg text-sm font-bold bg-primary text-white border border-accent-gold/70 shrink-0">
                                    Admin Pengelola
                                </span>
                            @else
                                <span class="px-3 py-1.5 rounded-lg text-sm font-bold bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD] shrink-0">
                                    Teknisi Lapangan
                                </span>
                            @endif
                        </div>

                        <!-- Aksi Terpisah Lega -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="button"
                                    onclick="openEditUserModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->phone }}', '{{ $user->role }}')"
                                    class="flex-1 min-h-[48px] px-4 py-2.5 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-base transition-colors cursor-pointer">
                                Edit / Reset Sandi
                            </button>

                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="sm:w-auto" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun petugas {{ addslashes($user->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-full min-h-[48px] px-5 py-2.5 rounded-xl border-2 border-[#FCA5A5] bg-surface text-[#B91C1C] hover:bg-[#FEE2E2] font-bold text-base transition-colors cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Tampilan Desktop (lg+): Tabel Daftar Petugas -->
            <div class="hidden lg:block bg-surface rounded-2xl border border-primary-border overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-primary-subtle/70 border-b border-primary-border text-primary font-bold uppercase text-sm tracking-wide">
                            <tr>
                                <th class="py-4 px-4">Nama &amp; Kontak</th>
                                <th class="py-4 px-4">Peran</th>
                                <th class="py-4 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary-border/60 text-sm">
                            @foreach($users as $user)
                                <tr class="hover:bg-primary-subtle/30 transition-colors">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-2">
                                            <strong class="text-base font-bold text-text-main">{{ $user->name }}</strong>
                                            @if($user->id === auth()->id())
                                                <span class="px-2.5 py-0.5 rounded-md text-sm font-bold bg-bg-main border border-primary-border text-text-main">Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-sm font-mono text-text-muted mt-0.5">
                                            {{ $user->email }} &bull; <span class="text-primary font-semibold">{{ $user->phone ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if($user->isAdmin())
                                            <span class="inline-block px-3 py-1.5 rounded-lg text-sm font-bold bg-primary text-white border border-accent-gold/70">
                                                Admin Pengelola
                                            </span>
                                        @else
                                            <span class="inline-block px-3 py-1.5 rounded-lg text-sm font-bold bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]">
                                                Teknisi Lapangan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-3">
                                            <button type="button"
                                                    onclick="openEditUserModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->phone }}', '{{ $user->role }}')"
                                                    class="min-h-[44px] px-4 py-2 rounded-xl border-2 border-primary bg-surface hover:bg-primary-subtle text-primary font-bold text-sm transition-colors cursor-pointer">
                                                Edit / Reset Sandi
                                            </button>

                                            @if($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun petugas {{ addslashes($user->name) }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl border border-[#FCA5A5] text-[#B91C1C] hover:bg-[#FEE2E2] font-bold text-sm transition-colors cursor-pointer">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- FORM TAMBAH PETUGAS BARU                                              -->
        <!-- ===================================================================== -->
        <aside class="lg:col-span-4 lg:sticky lg:top-24 bg-surface rounded-2xl border-2 border-primary-border p-5 sm:p-6 shadow-sm space-y-4">
            <div class="pb-3 border-b border-primary-border/70">
                <h2 class="text-xl font-extrabold text-text-main">
                    Tambah Petugas Baru
                </h2>
                <p class="text-sm text-text-muted mt-0.5">
                    Daftarkan akun Admin atau Teknisi baru.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label for="create_user_name" class="block text-base font-bold text-text-main">Nama Lengkap <span class="text-[#B91C1C]">*</span></label>
                    <input type="text" id="create_user_name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Rahmat Hidayat" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface">
                </div>

                <div class="space-y-1.5">
                    <label for="create_user_email" class="block text-base font-bold text-text-main">Alamat Email <span class="text-[#B91C1C]">*</span></label>
                    <input type="email" id="create_user_email" name="email" value="{{ old('email') }}" required placeholder="email@servisac.com" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface">
                </div>

                <div class="space-y-1.5">
                    <label for="create_user_phone" class="block text-base font-bold text-text-main">Nomor WhatsApp <span class="text-[#B91C1C]">*</span></label>
                    <input type="tel" id="create_user_phone" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface font-mono">
                </div>

                <div class="space-y-1.5">
                    <label for="create_user_role" class="block text-base font-bold text-text-main">Peran Hak Akses <span class="text-[#B91C1C]">*</span></label>
                    <select id="create_user_role" name="role" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main font-semibold outline-none focus:border-primary bg-surface">
                        <option value="technician" {{ old('role') === 'technician' ? 'selected' : '' }}>Teknisi Lapangan</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin Pengelola</option>
                    </select>
                </div>

                <div class="space-y-1.5">
                    <label for="create_user_password" class="block text-base font-bold text-text-main">Kata Sandi <span class="text-[#B91C1C]">*</span></label>
                    <input type="password" id="create_user_password" name="password" required placeholder="Minimal 6 karakter" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface">
                </div>

                <button type="submit" class="w-full min-h-[52px] py-3 px-5 bg-primary hover:bg-primary-hover text-white font-bold text-base rounded-xl transition-colors cursor-pointer shadow-xs">
                    + Simpan Akun Petugas
                </button>
            </form>
        </aside>

    </div>

</div>

<!-- MODAL EDIT PETUGAS (PERTAHANKAN SEMUA DOM ID VANILLA JS) -->
<div id="modal-edit-user" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if (event.target === this) closeEditUserModal();">
    <div class="bg-surface rounded-2xl border-2 border-primary-border max-w-md w-full p-5 sm:p-6 space-y-5 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between gap-3 pb-3 border-b border-primary-border">
            <h3 class="text-xl font-extrabold text-text-main">Perbarui Akun Petugas</h3>
            <button type="button" onclick="closeEditUserModal()" aria-label="Tutup modal edit petugas" class="tap-target inline-flex items-center justify-center rounded-xl bg-bg-main hover:bg-primary-subtle text-text-main text-2xl font-bold cursor-pointer shrink-0">&times;</button>
        </div>

        <form id="form-edit-user" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label for="edit-user-name" class="block text-base font-bold text-text-main">Nama Lengkap <span class="text-[#B91C1C]">*</span></label>
                <input type="text" id="edit-user-name" name="name" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface">
            </div>

            <div class="space-y-1.5">
                <label for="edit-user-email" class="block text-base font-bold text-text-main">Alamat Email <span class="text-[#B91C1C]">*</span></label>
                <input type="email" id="edit-user-email" name="email" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface">
            </div>

            <div class="space-y-1.5">
                <label for="edit-user-phone" class="block text-base font-bold text-text-main">Nomor WhatsApp <span class="text-[#B91C1C]">*</span></label>
                <input type="tel" id="edit-user-phone" name="phone" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main outline-none focus:border-primary bg-surface font-mono">
            </div>

            <div class="space-y-1.5">
                <label for="edit-user-role" class="block text-base font-bold text-text-main">Peran Hak Akses <span class="text-[#B91C1C]">*</span></label>
                <select id="edit-user-role" name="role" required class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main font-semibold outline-none focus:border-primary bg-surface">
                    <option value="technician">Teknisi Lapangan</option>
                    <option value="admin">Admin Pengelola</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label for="edit-user-password" class="block text-base font-bold text-text-main">Reset Kata Sandi</label>
                <input type="password" id="edit-user-password" name="password" placeholder="Kosongkan jika tidak ingin diubah" class="w-full min-h-[50px] px-4 py-3 rounded-xl border-2 border-primary-border text-base text-text-main placeholder-gray-400 outline-none focus:border-primary bg-surface">
                <span class="text-sm text-text-muted block">Isi hanya jika ingin mengganti kata sandi akun ini.</span>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-primary-border">
                <button type="button" onclick="closeEditUserModal()" class="min-h-[48px] px-5 py-2.5 rounded-xl border border-primary-border bg-bg-main text-text-main text-base font-bold hover:bg-primary-subtle cursor-pointer transition-colors">
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
    function openEditUserModal(id, name, email, phone, role) {
        document.getElementById('form-edit-user').action = '/admin/users/' + id;
        document.getElementById('edit-user-name').value = name;
        document.getElementById('edit-user-email').value = email;
        document.getElementById('edit-user-phone').value = phone;
        document.getElementById('edit-user-role').value = role;
        document.getElementById('modal-edit-user').classList.remove('hidden');
    }

    function closeEditUserModal() {
        document.getElementById('modal-edit-user').classList.add('hidden');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeEditUserModal();
        }
    });
</script>
@endsection
