@extends('layouts.app', ['title' => 'Masuk Petugas - Servis AC Masjid'])

@section('content')
<div class="max-w-md mx-auto pt-4 sm:pt-10">
    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 shadow-sm">
        
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Masuk ke Sistem</h1>
            <p class="text-sm text-gray-600 mt-1">Khusus Admin Pengelola dan Teknisi Lapangan</p>
        </div>

        <!-- Form Login -->
        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <!-- Input Login (Email atau HP) -->
            <div class="space-y-1.5">
                <label for="login" class="block text-sm font-medium text-gray-900">
                    Alamat Email atau Nomor WhatsApp
                </label>
                <input type="text" 
                       name="login" 
                       id="login" 
                       value="{{ old('login') }}" 
                       required 
                       autofocus
                       placeholder="Contoh: admin@servisac.com atau 081234567890" 
                       class="w-full min-h-[46px] px-4 py-3 bg-white border {{ $errors->has('login') ? 'border-red-400 bg-red-50/50' : 'border-gray-300' }} rounded-lg text-gray-900 text-base placeholder-gray-400 focus:outline-none focus:border-[#1E3E34] focus:ring-1 focus:ring-[#1E3E34]">
                @error('login')
                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Input Password -->
            <div class="space-y-1.5" x-data="{ show: false }">
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm font-medium text-gray-900">
                        Kata Sandi
                    </label>
                    <button type="button" 
                            @click="show = !show" 
                            tabindex="-1"
                            class="text-xs font-medium text-gray-500 hover:text-gray-800 cursor-pointer">
                        <span x-text="show ? 'Sembunyikan' : 'Tampilkan'"></span>
                    </button>
                </div>
                <input :type="show ? 'text' : 'password'" 
                       name="password" 
                       id="password" 
                       required 
                       placeholder="Masukkan kata sandi akun" 
                       class="w-full min-h-[46px] px-4 py-3 bg-white border {{ $errors->has('password') ? 'border-red-400 bg-red-50/50' : 'border-gray-300' }} rounded-lg text-gray-900 text-base placeholder-gray-400 focus:outline-none focus:border-[#1E3E34] focus:ring-1 focus:ring-[#1E3E34]">
                @error('password')
                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ingat Saya -->
            <div class="flex items-center justify-between py-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#1E3E34] focus:ring-[#1E3E34] border-gray-300">
                    <span class="text-sm font-normal text-gray-700">Ingat saya di perangkat ini</span>
                </label>
            </div>

            <!-- Tombol Submit (Minimal 48px, Hijau Khidmat #1E3E34) -->
            <button type="submit" 
                    class="w-full min-h-[48px] py-3 px-6 bg-[#1E3E34] hover:bg-[#142B24] text-white font-medium text-base rounded-lg transition-colors focus:ring-2 focus:ring-[#1E3E34] focus:ring-offset-2 flex items-center justify-center cursor-pointer">
                Masuk Sekarang
            </button>
        </form>

        <!-- Akun Demo Cepat (Quick Fill) untuk Pengujian -->
        <div class="mt-6 pt-5 border-t border-gray-100" x-data>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2.5 text-center">Akun Uji Coba Cepat (Klik untuk Isi):</p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" 
                        @click="document.getElementById('login').value = 'admin@servisac.com'; document.getElementById('password').value = 'password123';"
                        class="p-2.5 rounded-lg border border-gray-200 bg-gray-50 hover:bg-[#EBF2EE] hover:border-[#1E3E34]/30 text-left transition-colors cursor-pointer">
                    <span class="block text-xs font-semibold text-gray-900">Admin</span>
                    <span class="block text-[11px] text-gray-500 truncate">admin@servisac.com</span>
                </button>
                <button type="button" 
                        @click="document.getElementById('login').value = 'budi@servisac.com'; document.getElementById('password').value = 'password123';"
                        class="p-2.5 rounded-lg border border-gray-200 bg-gray-50 hover:bg-[#EBF2EE] hover:border-[#1E3E34]/30 text-left transition-colors cursor-pointer">
                    <span class="block text-xs font-semibold text-gray-900">Teknisi Budi</span>
                    <span class="block text-[11px] text-gray-500 truncate">budi@servisac.com</span>
                </button>
            </div>
        </div>

        <!-- Tautan Kembali ke Form Pemesanan Publik -->
        <div class="mt-6 text-center">
            <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                &larr; Kembali ke Formulir Pemesanan
            </a>
        </div>

    </div>
</div>
@endsection
