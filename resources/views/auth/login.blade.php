@extends('layouts.app', ['title' => 'Masuk Petugas - Servis AC Masjid'])

@section('content')
<div class="max-w-md mx-auto pt-4 sm:pt-10">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
        
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-emerald-100 text-emerald-800 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
                🔑
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Masuk ke Sistem</h1>
            <p class="text-sm text-slate-600 mt-1">Khusus Admin Pengelola & Teknisi Lapangan</p>
        </div>

        <!-- Form Login -->
        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <!-- Input Login (Email atau HP) -->
            <div>
                <label for="login" class="block text-sm font-bold text-slate-900 mb-1.5">
                    Alamat Email atau Nomor WhatsApp
                </label>
                <input type="text" 
                       name="login" 
                       id="login" 
                       value="{{ old('login') }}" 
                       required 
                       autofocus
                       placeholder="contoh: admin@servisac.com / 0812xxxx" 
                       class="w-full px-4 py-3 rounded-xl border {{ $errors->has('login') ? 'border-rose-400 bg-rose-50' : 'border-slate-300' }} text-slate-900 text-base focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all placeholder:text-slate-400">
                @error('login')
                    <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Input Password -->
            <div x-data="{ show: false }">
                <label for="password" class="block text-sm font-bold text-slate-900 mb-1.5">
                    Kata Sandi
                </label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" 
                           name="password" 
                           id="password" 
                           required 
                           placeholder="Masukkan kata sandi" 
                           class="w-full px-4 py-3 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 bg-rose-50' : 'border-slate-300' }} text-slate-900 text-base focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all placeholder:text-slate-400 pr-12">
                    <button type="button" 
                            @click="show = !show" 
                            tabindex="-1"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                        <span x-text="show ? '🙈' : '👁️'" class="text-lg"></span>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ingat Saya -->
            <div class="flex items-center justify-between py-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <span class="text-sm font-medium text-slate-700">Ingat saya di HP ini</span>
                </label>
            </div>

            <!-- Tombol Submit (Minimal 48px, Ramah Jempol) -->
            <button type="submit" 
                    class="w-full min-h-[48px] py-3 px-6 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-base rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                <span>🔐</span>
                <span>Masuk Sekarang</span>
            </button>
        </form>

        <!-- Akun Demo Cepat (Quick Fill) untuk Pengujian -->
        <div class="mt-6 pt-5 border-t border-slate-100" x-data>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 text-center">Akun Uji Coba Cepat (Klik untuk Isi):</p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" 
                        @click="document.getElementById('login').value = 'admin@servisac.com'; document.getElementById('password').value = 'password123';"
                        class="p-2.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-200 text-left transition-colors cursor-pointer">
                    <span class="block text-xs font-bold text-slate-900">👤 Admin</span>
                    <span class="block text-[11px] text-slate-500">admin@servisac.com</span>
                </button>
                <button type="button" 
                        @click="document.getElementById('login').value = 'budi@servisac.com'; document.getElementById('password').value = 'password123';"
                        class="p-2.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-emerald-50 hover:border-emerald-200 text-left transition-colors cursor-pointer">
                    <span class="block text-xs font-bold text-slate-900">🔧 Teknisi Budi</span>
                    <span class="block text-[11px] text-slate-500">budi@servisac.com</span>
                </button>
            </div>
        </div>

        <!-- Tautan Kembali ke Form Pemesanan Publik -->
        <div class="mt-6 text-center">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-700 transition-colors inline-flex items-center gap-1">
                <span>&larr;</span>
                <span>Kembali ke Halaman Pemesanan Servis</span>
            </a>
        </div>

    </div>
</div>
@endsection
