<!DOCTYPE html>
<html lang="id" class="h-full bg-bg-main">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Servis AC Masjid & Musholla' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $isAdminUser = auth()->check() && Auth::user()->isAdmin();
@endphp
<body class="min-h-full flex flex-col font-sans antialiased text-text-main bg-bg-main selection:bg-primary selection:text-white {{ $isAdminUser ? 'pb-24 md:pb-0' : '' }}">

    <!-- Header -->
    <header class="no-print bg-surface border-b border-primary-border sticky top-0 z-40 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-3 min-w-0">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-gradient-to-br from-primary to-primary-light text-white flex items-center justify-center font-extrabold text-base shadow-sm">
                    AC
                </span>
                <span class="min-w-0">
                    <span class="block text-base sm:text-lg font-extrabold text-primary leading-tight truncate">Servis AC Masjid</span>
                    <span class="hidden sm:block text-xs font-medium text-text-muted">Perawatan Pendingin Ruang Ibadah</span>
                </span>
            </a>

            <div class="flex items-center gap-2 sm:gap-4">
                @auth
                    @if(Auth::user()->isAdmin())
                        <nav class="hidden md:flex items-center gap-1 bg-primary-subtle p-1 rounded-xl text-sm font-semibold" aria-label="Menu Admin">
                            <a href="{{ route('admin.dashboard') }}"
                               @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif
                               class="min-h-[44px] inline-flex items-center px-4 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-white shadow-sm' : 'text-primary hover:bg-white' }}">
                                Pesanan
                            </a>
                            <a href="{{ route('admin.masjids.index') }}"
                               @if(request()->routeIs('admin.masjids.*')) aria-current="page" @endif
                               class="min-h-[44px] inline-flex items-center px-4 rounded-lg transition-colors {{ request()->routeIs('admin.masjids.*') ? 'bg-primary text-white shadow-sm' : 'text-primary hover:bg-white' }}">
                                Data Masjid &amp; AC
                            </a>
                            <a href="{{ route('admin.users.index') }}"
                               @if(request()->routeIs('admin.users.*')) aria-current="page" @endif
                               class="min-h-[44px] inline-flex items-center px-4 rounded-lg transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-primary text-white shadow-sm' : 'text-primary hover:bg-white' }}">
                                Akun Petugas
                            </a>
                        </nav>
                    @endif

                    <div class="text-right hidden sm:block leading-tight">
                        <span class="block text-xs font-semibold uppercase tracking-wider text-text-muted">
                            {{ Auth::user()->isAdmin() ? 'Admin Pengelola' : 'Teknisi Lapangan' }}
                        </span>
                        <span class="block text-sm font-bold text-text-main mt-0.5">
                            {{ Auth::user()->name }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="tap-target inline-flex items-center justify-center px-4 text-sm font-bold text-primary hover:text-white bg-white hover:bg-primary rounded-xl transition-colors border-2 border-primary cursor-pointer">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="tap-target inline-flex items-center justify-center px-5 text-sm font-bold text-white bg-primary hover:bg-primary-hover rounded-xl transition-colors shadow-sm">
                        Masuk Petugas
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Notifikasi Flash Message -->
    @if (session('success') || session('error'))
        <div class="no-print max-w-5xl mx-auto px-4 w-full pt-4 space-y-3">
            @if (session('success'))
                <div x-data="{ open: true }" x-show="open" role="status" aria-live="polite"
                     class="flex items-start gap-3 p-4 rounded-2xl bg-[#DCFCE7] border-2 border-[#86EFAC] text-[#14532D]">
                    <svg class="w-7 h-7 shrink-0 text-[#15803D]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <p class="flex-1 text-base font-semibold leading-relaxed pt-0.5">{{ session('success') }}</p>
                    <button type="button" @click="open = false" aria-label="Tutup pemberitahuan"
                            class="tap-target -mr-1 -mt-1 inline-flex items-center justify-center rounded-xl text-2xl font-bold text-[#14532D] hover:bg-[#BBF7D0] cursor-pointer">
                        &times;
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ open: true }" x-show="open" role="alert"
                     class="flex items-start gap-3 p-4 rounded-2xl bg-[#FEE2E2] border-2 border-[#FCA5A5] text-[#7F1D1D]">
                    <svg class="w-7 h-7 shrink-0 text-[#B91C1C]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <p class="flex-1 text-base font-semibold leading-relaxed pt-0.5">{{ session('error') }}</p>
                    <button type="button" @click="open = false" aria-label="Tutup pemberitahuan"
                            class="tap-target -mr-1 -mt-1 inline-flex items-center justify-center rounded-xl text-2xl font-bold text-[#7F1D1D] hover:bg-[#FECACA] cursor-pointer">
                        &times;
                    </button>
                </div>
            @endif
        </div>
    @endif

    <!-- Konten Halaman Utama -->
    <main class="flex-1 max-w-5xl mx-auto px-4 py-5 sm:py-8 w-full">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="no-print bg-surface border-t border-primary-border py-5 mt-8">
        <div class="max-w-5xl mx-auto px-4 text-center text-sm text-text-muted font-medium">
            Servis AC Masjid &amp; Musholla &bull; Layanan Perawatan Mandiri
        </div>
    </footer>

    <!-- Bottom Navigation Admin (khusus HP) -->
    @auth
        @if(Auth::user()->isAdmin())
            <nav class="no-print md:hidden fixed bottom-0 inset-x-0 z-40 bg-surface border-t-2 border-primary-border shadow-[0_-4px_12px_rgba(19,34,27,0.08)] safe-bottom" aria-label="Menu Admin">
                <div class="grid grid-cols-3">
                    <a href="{{ route('admin.dashboard') }}"
                       @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif
                       class="flex flex-col items-center justify-center gap-1 min-h-[64px] py-2 text-xs font-bold transition-colors {{ request()->routeIs('admin.dashboard') ? 'text-primary bg-primary-subtle border-t-4 border-primary -mt-0.5' : 'text-text-muted' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                        <span>Pesanan</span>
                    </a>
                    <a href="{{ route('admin.masjids.index') }}"
                       @if(request()->routeIs('admin.masjids.*')) aria-current="page" @endif
                       class="flex flex-col items-center justify-center gap-1 min-h-[64px] py-2 text-xs font-bold transition-colors {{ request()->routeIs('admin.masjids.*') ? 'text-primary bg-primary-subtle border-t-4 border-primary -mt-0.5' : 'text-text-muted' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                        </svg>
                        <span>Masjid</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                       @if(request()->routeIs('admin.users.*')) aria-current="page" @endif
                       class="flex flex-col items-center justify-center gap-1 min-h-[64px] py-2 text-xs font-bold transition-colors {{ request()->routeIs('admin.users.*') ? 'text-primary bg-primary-subtle border-t-4 border-primary -mt-0.5' : 'text-text-muted' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <span>Petugas</span>
                    </a>
                </div>
            </nav>
        @endif
    @endauth

</body>
</html>
