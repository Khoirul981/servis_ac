@extends('layouts.app', ['title' => 'Master Petugas - Servis AC Masjid'])

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Manajemen Akun Petugas</h1>
            <p class="text-xs sm:text-sm text-slate-500">Kelola akun Admin Pengelola dan Teknisi Lapangan.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- DAFTAR AKUN PETUGAS (2 Kolom) -->
        <div class="md:col-span-2 space-y-3">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Daftar Petugas Aktif ({{ $users->count() }}):</h2>
            
            <div class="space-y-2.5">
                @foreach($users as $user)
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $user->isAdmin() ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }} flex items-center justify-center font-bold text-base">
                                {{ $user->isAdmin() ? '🛡️' : '🔧' }}
                            </div>
                            <div>
                                <strong class="text-sm font-black text-slate-900 block">{{ $user->name }}</strong>
                                <span class="text-slate-500 font-mono">{{ $user->email }} &bull; {{ $user->phone ?? '-' }}</span>
                            </div>
                        </div>

                        <span class="px-3 py-1 rounded-full font-bold text-xs {{ $user->isAdmin() ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $user->isAdmin() ? 'Admin Pengelola' : 'Teknisi Lapangan' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- FORM TAMBAH PETUGAS BARU (1 Kolom) -->
        <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                <span>➕</span>
                <span>Tambah Petugas Baru</span>
            </h2>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Nama Lengkap:</label>
                    <input type="text" name="name" required placeholder="Nama Petugas" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Alamat Email:</label>
                    <input type="email" name="email" required placeholder="email@servisac.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Nomor WhatsApp:</label>
                    <input type="tel" name="phone" required placeholder="08xxxxxxxxxx" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Peran Akses:</label>
                    <select name="role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                        <option value="technician">Teknisi Lapangan</option>
                        <option value="admin">Admin Pengelola</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">Kata Sandi:</label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <button type="submit" class="w-full min-h-[46px] py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs cursor-pointer">
                    Simpan Akun Petugas
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
