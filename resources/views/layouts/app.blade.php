<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Servis AC Masjid & Musholla' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased text-slate-800 flex flex-col justify-between selection:bg-emerald-500 selection:text-white">

    <!-- Header / Navigasi -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-decoration-none">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xl shadow-xs">
                    🕌
                </div>
                <div>
                    <span class="block text-base sm:text-lg font-bold text-slate-900 leading-tight">Servis AC Masjid</span>
                    <span class="block text-xs font-medium text-emerald-700">Manajemen Perawatan V2</span>
                </div>
            </a>

            <div class="flex items-center gap-3">
                @auth
                    @if(Auth::user()->isAdmin())
                        <div class="hidden md:flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-bold">
                            <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                                📋 Meja Operasional
                            </a>
                            <a href="{{ route('admin.masjids.index') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.masjids.*') ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                                🕌 Data Masjid
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                                👥 Petugas
                            </a>
                        </div>
                    @endif

                    <div class="text-right hidden sm:block">
                        <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {{ Auth::user()->isAdmin() ? 'Pengelola Admin' : 'Teknisi Lapangan' }}
                        </span>
                        <span class="block text-sm font-bold text-slate-900 leading-none">
                            {{ Auth::user()->name }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors border border-rose-200 cursor-pointer">
                            <span>Keluar</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-slate-700 hover:text-emerald-700 bg-slate-100 hover:bg-emerald-50 rounded-lg transition-colors border border-slate-200">
                        <span>Masuk Petugas</span>
                    </a>
                @endauth
            </div>
        </div>

        @auth
            @if(Auth::user()->isAdmin())
                <div class="md:hidden border-t border-slate-100 px-4 py-2 flex items-center justify-around text-xs font-bold bg-slate-50">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'text-emerald-700 font-extrabold' : 'text-slate-600' }}">
                        📋 Operasional
                    </a>
                    <a href="{{ route('admin.masjids.index') }}" class="{{ request()->routeIs('admin.masjids.*') ? 'text-emerald-700 font-extrabold' : 'text-slate-600' }}">
                        🕌 Masjid & AC
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'text-emerald-700 font-extrabold' : 'text-slate-600' }}">
                        👥 Petugas
                    </a>
                </div>
            @endif
        @endauth
    </header>

    <!-- Notifikasi Flash Message -->
    <div class="max-w-5xl mx-auto px-4 w-full pt-4">
        @if (session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3 text-emerald-900 shadow-xs">
                <span class="text-xl">✅</span>
                <div class="text-sm font-medium pt-0.5 leading-relaxed">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3 text-rose-900 shadow-xs">
                <span class="text-xl">⚠️</span>
                <div class="text-sm font-medium pt-0.5 leading-relaxed">{{ session('error') }}</div>
            </div>
        @endif
    </div>

    <!-- Konten Halaman Utama -->
    <main class="flex-1 max-w-5xl mx-auto px-4 py-4 sm:py-6 w-full">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-8">
        <div class="max-w-5xl mx-auto px-4 text-center text-xs text-slate-500">
            Sistem Servis AC Masjid & Musholla &bull; Versi Ramah Pengguna (V2) &bull; Terhubung WhatsApp & Google Maps
        </div>
    </footer>

</body>
</html>
