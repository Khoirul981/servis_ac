@extends('layouts.app', ['title' => 'Masuk Petugas - Servis AC Masjid'])

@section('content')
<div class="max-w-lg mx-auto py-3 sm:py-8 space-y-5">

    <!-- Kartu Login Utama -->
    <div class="bg-surface rounded-2xl border-2 border-primary-border shadow-sm overflow-hidden">

        <!-- Header Identitas Hijau Khidmat -->
        <div class="bg-gradient-to-br from-primary to-primary-light px-6 py-6 text-white text-center space-y-2">
            <div class="mx-auto w-14 h-14 rounded-2xl bg-white/15 border border-accent-gold/60 flex items-center justify-center shadow-xs">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Masuk ke Sistem</h1>
            <p class="text-sm sm:text-base text-white/90">
                Khusus Admin Pengelola dan Teknisi Lapangan
            </p>
        </div>

        <!-- Form Login -->
        <div class="p-6 sm:p-8 space-y-6">
            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf

                <!-- Input Login (Email atau Nomor WhatsApp) -->
                <div class="space-y-2">
                    <label for="login" class="block text-base font-bold text-text-main">
                        Email atau Nomor WhatsApp
                    </label>
                    <input type="text"
                           name="login"
                           id="login"
                           value="{{ old('login') }}"
                           required
                           autofocus
                           autocomplete="username"
                           placeholder="Contoh: admin@servisac.com atau 081234567890"
                           class="w-full min-h-[52px] px-4 py-3 bg-surface border-2 {{ $errors->has('login') ? 'border-[#B91C1C] bg-[#FEE2E2]/30' : 'border-primary-border' }} rounded-xl text-text-main text-base placeholder-gray-400 focus:outline-none focus:border-primary">
                    @error('login')
                        <p class="text-sm font-semibold text-[#B91C1C]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Input Kata Sandi -->
                <div class="space-y-2" x-data="{ show: false }">
                    <div class="flex items-center justify-between gap-2">
                        <label for="password" class="block text-base font-bold text-text-main">
                            Kata Sandi
                        </label>
                        <button type="button"
                                @click="show = !show"
                                tabindex="-1"
                                class="min-h-[44px] px-3 rounded-lg text-sm font-bold text-primary hover:bg-primary-subtle transition-colors cursor-pointer inline-flex items-center gap-1.5">
                            <span x-text="show ? 'Sembunyikan' : 'Tampilkan'"></span>
                        </button>
                    </div>
                    <input :type="show ? 'text' : 'password'"
                           name="password"
                           id="password"
                           required
                           autocomplete="current-password"
                           placeholder="Masukkan kata sandi akun Anda"
                           class="w-full min-h-[52px] px-4 py-3 bg-surface border-2 {{ $errors->has('password') ? 'border-[#B91C1C] bg-[#FEE2E2]/30' : 'border-primary-border' }} rounded-xl text-text-main text-base placeholder-gray-400 focus:outline-none focus:border-primary">
                    @error('password')
                        <p class="text-sm font-semibold text-[#B91C1C]">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ingat Saya -->
                <div>
                    <label class="min-h-[44px] inline-flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox"
                               name="remember"
                               {{ old('remember') ? 'checked' : '' }}
                               class="w-5 h-5 rounded accent-primary border-primary-border cursor-pointer">
                        <span class="text-base font-medium text-text-main">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Tombol Submit Utama -->
                <button type="submit"
                        class="w-full min-h-[54px] py-3.5 px-6 bg-primary hover:bg-primary-hover text-white font-extrabold text-base sm:text-lg rounded-xl transition-colors shadow-sm flex items-center justify-center cursor-pointer">
                    Masuk ke Sistem
                </button>
            </form>

            <!-- Tautan Kembali ke Form Pemesanan Publik -->
            <div class="pt-4 border-t border-primary-border/70 text-center">
                <a href="{{ route('home') }}"
                   class="min-h-[44px] inline-flex items-center justify-center gap-1.5 text-base font-bold text-primary hover:text-primary-hover transition-colors">
                    <span aria-hidden="true">&larr;</span>
                    <span>Kembali ke Formulir Pemesanan</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Kotak Terpisah Alat Bantu Uji Coba Cepat (Quick Fill) -->
    <div class="bg-bg-main rounded-2xl border border-primary-border p-4 sm:p-5 space-y-3" x-data>
        <div class="text-center space-y-0.5">
            <span class="inline-block px-2.5 py-0.5 rounded-full bg-primary-subtle border border-primary-border text-primary text-sm font-bold">
                Alat Bantu Uji Coba (Demo)
            </span>
            <p class="text-sm text-text-muted">
                Sentuh salah satu akun di bawah untuk mengisi formulir login secara otomatis.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button type="button"
                    @click="document.getElementById('login').value = 'admin@servisac.com'; document.getElementById('password').value = 'password123';"
                    class="min-h-[56px] p-3.5 rounded-xl border-2 border-primary-border bg-surface hover:bg-primary-subtle hover:border-primary text-left transition-colors cursor-pointer">
                <span class="block text-base font-bold text-text-main">Admin Pengelola</span>
                <span class="block text-sm font-mono text-text-muted truncate mt-0.5">admin@servisac.com</span>
            </button>
            <button type="button"
                    @click="document.getElementById('login').value = 'budi@servisac.com'; document.getElementById('password').value = 'password123';"
                    class="min-h-[56px] p-3.5 rounded-xl border-2 border-primary-border bg-surface hover:bg-primary-subtle hover:border-primary text-left transition-colors cursor-pointer">
                <span class="block text-base font-bold text-text-main">Teknisi Budi</span>
                <span class="block text-sm font-mono text-text-muted truncate mt-0.5">budi@servisac.com</span>
            </button>
        </div>
    </div>

</div>
@endsection
