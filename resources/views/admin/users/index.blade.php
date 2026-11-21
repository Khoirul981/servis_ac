@extends('layouts.app', ['title' => 'Master Petugas - Servis AC Masjid'])

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Manajemen Akun Petugas</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Kelola akun Admin Pengelola dan Teknisi Lapangan.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- DAFTAR AKUN PETUGAS (2 Kolom di Desktop) -->
        <div class="lg:col-span-2 space-y-3">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Daftar Petugas Aktif ({{ $users->count() }})</h2>
            
            <div class="space-y-2">
                @foreach($users as $user)
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div>
                            <div class="flex items-center gap-2">
                                <strong class="text-sm font-semibold text-gray-900">{{ $user->name }}</strong>
                                @if($user->id === auth()->id())
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700">Anda</span>
                                @endif
                            </div>
                            <span class="text-gray-500 font-mono block mt-0.5">{{ $user->email }} &bull; {{ $user->phone ?? '-' }}</span>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-auto flex-wrap">
                            <span class="px-2.5 py-1 rounded-md font-semibold text-[11px] {{ $user->isAdmin() ? 'bg-[#EBF2EE] text-[#1E3E34] border border-[#1E3E34]/20' : 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]' }}">
                                {{ $user->isAdmin() ? 'Admin Pengelola' : 'Teknisi Lapangan' }}
                            </span>

                            <button type="button" 
                                    onclick="openEditUserModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->phone }}', '{{ $user->role }}')"
                                    class="px-2.5 py-1 rounded-md border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium transition-colors cursor-pointer text-xs">
                                Edit
                            </button>

                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun petugas {{ addslashes($user->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded-md border border-red-200 text-red-600 hover:bg-red-50 font-medium transition-colors cursor-pointer text-xs">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- FORM TAMBAH PETUGAS BARU (1 Kolom di Desktop) -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 shadow-sm space-y-4 h-fit">
            <h2 class="text-base font-semibold text-gray-900 pb-2 border-b border-gray-100">
                Tambah Petugas Baru
            </h2>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3">
                @csrf

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">Nama Lengkap: <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Rahmat Hidayat" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">Alamat Email: <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required placeholder="email@servisac.com" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">Nomor WhatsApp: <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" required placeholder="08xxxxxxxxxx" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white font-mono">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">Peran Hak Akses: <span class="text-red-500">*</span></label>
                    <select name="role" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                        <option value="technician">Teknisi Lapangan</option>
                        <option value="admin">Admin Pengelola</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-gray-800">Kata Sandi: <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>

                <button type="submit" class="w-full min-h-[44px] py-2.5 px-4 bg-[#1E3E34] hover:bg-[#142B24] text-white font-semibold text-xs rounded-lg transition-colors cursor-pointer mt-2">
                    + Simpan Akun Petugas
                </button>
            </form>
        </div>

    </div>

</div>

<!-- MODAL EDIT PETUGAS -->
<div id="modal-edit-user" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl border border-gray-200 max-w-md w-full p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">Perbarui Akun Petugas</h3>
            <button type="button" onclick="closeEditUserModal()" class="text-gray-400 hover:text-gray-600 text-lg font-bold cursor-pointer">&times;</button>
        </div>

        <form id="form-edit-user" method="POST" action="" class="space-y-3.5">
            @csrf
            @method('PUT')

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-gray-800">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" id="edit-user-name" name="name" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-gray-800">Alamat Email <span class="text-red-500">*</span></label>
                <input type="email" id="edit-user-email" name="email" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-gray-800">Nomor WhatsApp <span class="text-red-500">*</span></label>
                <input type="tel" id="edit-user-phone" name="phone" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white font-mono">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-gray-800">Peran Hak Akses <span class="text-red-500">*</span></label>
                <select id="edit-user-role" name="role" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                    <option value="technician">Teknisi Lapangan</option>
                    <option value="admin">Admin Pengelola</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-gray-800">Ubah Kata Sandi</label>
                <input type="password" name="password" placeholder="Kosongkan jika tidak ingin diubah" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                <span class="text-[11px] text-gray-500 block">Isi hanya jika ingin mereset password akun ini.</span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 cursor-pointer">
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
</script>
@endsection
