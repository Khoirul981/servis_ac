<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F8F9FA]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Servis AC Masjid & Musholla' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans antialiased text-[#111827] bg-[#F8F9FA] flex flex-col justify-between selection:bg-[#1E3E34] selection:text-white">

    <!-- Bilah Navigasi Atas (Header) -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-decoration-none">
                <div class="w-9 h-9 rounded-lg bg-[#1E3E34] text-white flex items-center justify-center font-bold text-base">
                    AC
                </div>
                <div>
                    <span class="block text-base sm:text-lg font-bold text-gray-900 leading-tight">Servis AC Masjid</span>
                    <span class="block text-xs font-normal text-gray-500">Perawatan Pendingin Ruang Ibadah</span>
                </div>
            </a>

            <div class="flex items-center gap-3">
                @auth
                    @if(Auth::user()->isAdmin())
                        <nav class="hidden md:flex items-center gap-1 bg-[#EBF2EE] p-1 rounded-lg text-sm font-medium">
                            <a href="{{ route('admin.dashboard') }}" 
                               class="px-3 py-1.5 rounded-md transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#1E3E34] text-white font-semibold' : 'text-gray-700 hover:text-gray-900' }}">
                                Meja Operasional
                            </a>
                            <a href="{{ route('admin.masjids.index') }}" 
                               class="px-3 py-1.5 rounded-md transition-colors {{ request()->routeIs('admin.masjids.*') ? 'bg-[#1E3E34] text-white font-semibold' : 'text-gray-700 hover:text-gray-900' }}">
                                Data Masjid & AC
                            </a>
                            <a href="{{ route('admin.users.index') }}" 
                               class="px-3 py-1.5 rounded-md transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-[#1E3E34] text-white font-semibold' : 'text-gray-700 hover:text-gray-900' }}">
                                Akun Petugas
                            </a>
                        </nav>
                    @endif

                    <div class="text-right hidden sm:block">
                        <span class="block text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ Auth::user()->isAdmin() ? 'Admin Pengelola' : 'Teknisi Lapangan' }}
                        </span>
                        <span class="block text-sm font-semibold text-gray-900 leading-none mt-0.5">
                            {{ Auth::user()->name }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-gray-700 hover:text-red-700 bg-white hover:bg-gray-50 rounded-lg transition-colors border border-gray-300 cursor-pointer">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 bg-white hover:bg-gray-50 rounded-lg transition-colors border border-gray-300">
                        Masuk Petugas
                    </a>
                @endauth
            </div>
        </div>

        @auth
            @if(Auth::user()->isAdmin())
                <nav class="md:hidden border-t border-gray-100 px-4 py-2 flex items-center justify-around text-xs font-medium bg-[#EBF2EE]">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'text-[#1E3E34] font-bold' : 'text-gray-600' }}">
                        Operasional
                    </a>
                    <a href="{{ route('admin.masjids.index') }}" class="{{ request()->routeIs('admin.masjids.*') ? 'text-[#1E3E34] font-bold' : 'text-gray-600' }}">
                        Masjid & AC
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'text-[#1E3E34] font-bold' : 'text-gray-600' }}">
                        Petugas
                    </a>
                </nav>
            @endif
        @endauth
    </header>

    <!-- Notifikasi Flash Message -->
    <div class="max-w-5xl mx-auto px-4 w-full pt-4">
        @if (session('success'))
            <div class="mb-4 p-4 rounded-lg bg-[#DCFCE7] border border-[#86EFAC] text-[#15803D] text-sm font-medium leading-relaxed">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 rounded-lg bg-[#FEE2E2] border border-[#FCA5A5] text-[#B91C1C] text-sm font-medium leading-relaxed">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <!-- Konten Halaman Utama -->
    <main class="flex-1 max-w-5xl mx-auto px-4 py-4 sm:py-6 w-full">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Kaki Halaman (Footer) -->
    <footer class="bg-white border-t border-gray-200 py-5 mt-10">
        <div class="max-w-5xl mx-auto px-4 text-center text-xs text-gray-500">
            Sistem Servis AC Masjid & Musholla &bull; Layanan Perawatan Mandiri &bull; Terhubung WhatsApp & Google Maps
        </div>
    </footer>

</body>
</html>
