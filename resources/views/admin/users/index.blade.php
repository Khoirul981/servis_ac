@extends('layouts.app', ['title' => 'Master Petugas - Servis AC Masjid'])

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Manajemen Akun Petugas</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Kelola akun Admin Pengelola dan Teknisi Lapangan.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- DAFTAR AKUN PETUGAS (2 Kolom) -->
        <div class="md:col-span-2 space-y-3">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Daftar Petugas Aktif ({{ $users->count() }})</h2>
            
            <div class="space-y-2">
                @foreach($users as $user)
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex items-center justify-between text-xs">
                        <div>
                            <strong class="text-sm font-semibold text-gray-900 block">{{ $user->name }}</strong>
                            <span class="text-gray-500 font-mono">{{ $user->email }} &bull; {{ $user->phone ?? '-' }}</span>
                        </div>

                        <span class="px-3 py-1 rounded-md font-semibold text-xs {{ $user->isAdmin() ? 'bg-[#EBF2EE] text-[#1E3E34] border border-[#1E3E34]/20' : 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]' }}">
                            {{ $user->isAdmin() ? 'Admin Pengelola' : 'Teknisi Lapangan' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- FORM TAMBAH PETUGAS BARU (1 Kolom) -->
        <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 shadow-sm space-y-4">
            <h2 class="text-base font-semibold text-gray-900 pb-2 border-b border-gray-100">
                Tambah Petugas Baru
            </h2>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3">
                @csrf

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-gray-800">Nama Lengkap:</label>
                    <input type="text" name="name" required placeholder="Nama Petugas" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-gray-800">Alamat Email:</label>
                    <input type="email" name="email" required placeholder="email@servisac.com" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-gray-800">Nomor WhatsApp:</label>
                    <input type="tel" name="phone" required placeholder="08xxxxxxxxxx" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white font-mono">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-gray-800">Peran Akses:</label>
                    <select name="role" required class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                        <option value="technician">Teknisi Lapangan</option>
                        <option value="admin">Admin Pengelola</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-gray-800">Kata Sandi:</label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                </div>

                <button type="submit" class="w-full min-h-[44px] py-2.5 px-4 bg-[#1E3E34] hover:bg-[#142B24] text-white font-medium text-xs rounded-lg transition-colors cursor-pointer">
                    Simpan Akun Petugas
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
