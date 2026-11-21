@extends('layouts.app', ['title' => 'Dashboard Tugas Teknisi - Servis AC Masjid'])

@section('content')
<div class="max-w-xl mx-auto space-y-6 py-2">

    <!-- Header Profil Teknisi -->
    <div class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-2xl font-bold shadow-sm">
                🔧
            </div>
            <div>
                <span class="block text-xs font-bold uppercase tracking-wider text-blue-700">Teknisi Lapangan</span>
                <h1 class="text-xl font-extrabold text-slate-900 leading-tight">{{ Auth::user()->name }}</h1>
                <span class="text-xs text-slate-500 font-mono">{{ Auth::user()->phone ?? Auth::user()->email }}</span>
            </div>
        </div>
        <div class="text-right">
            <span class="inline-block px-3 py-1 rounded-full bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">
                {{ now()->translatedFormat('d M Y') }}
            </span>
        </div>
    </div>

    <!-- TUGAS AKTIF HARI INI -->
    <div class="space-y-4">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span>📋</span>
                <span>Tugas Aktif ({{ $activeOrders->count() }})</span>
            </h2>
            <span class="text-xs text-slate-500">Urut berdasarkan jadwal</span>
        </div>

        @forelse($activeOrders as $order)
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $order->masjid->pic_phone);
                if (str_starts_with($cleanPhone, '08')) { $cleanPhone = '628' . substr($cleanPhone, 2); }
                $waText = urlencode("Assalamualaikum Pak {$order->masjid->pic_name}, saya " . Auth::user()->name . " teknisi AC. Terkait jadwal servis di {$order->masjid->name}.");
                $waUrl = "https://wa.me/{$cleanPhone}?text={$waText}";
                
                $mapsQuery = urlencode($order->masjid->name . ', ' . $order->masjid->address);
                $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$mapsQuery}";
            @endphp

            <div class="bg-white rounded-3xl border {{ $order->status === 'dikerjakan' ? 'border-emerald-500 ring-2 ring-emerald-400' : 'border-slate-200' }} p-5 sm:p-6 shadow-sm space-y-4">
                
                <!-- Status & No Pesanan -->
                <div class="flex items-start justify-between gap-2 pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-xs font-mono font-bold text-slate-500 block">{{ $order->order_number }}</span>
                        <h3 class="text-lg font-black text-slate-900 mt-0.5">{{ $order->masjid->name }}</h3>
                    </div>
                    @if($order->status === 'dikerjakan')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-xs animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span>Sedang Dikerjakan</span>
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 font-bold text-xs">
                            Dijadwalkan
                        </span>
                    @endif
                </div>

                <!-- Info Lokasi & Pengurus -->
                <div class="text-sm space-y-2">
                    <div class="flex items-start gap-2 text-slate-700">
                        <span class="text-base shrink-0">📍</span>
                        <span class="text-xs leading-relaxed">{{ $order->masjid->address }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-700">
                        <span class="text-base shrink-0">👤</span>
                        <span class="text-xs font-semibold">{{ $order->masjid->pic_name }} ({{ $order->masjid->pic_phone }})</span>
                    </div>
                    <div class="flex items-center justify-between pt-2 text-xs text-slate-600 border-t border-slate-100">
                        <span>🕒 Jadwal: <strong>{{ $order->service_time ?? 'Fleksibel' }}</strong></span>
                        <span>❄️ <strong>{{ $order->ac_count }} Unit AC</strong> ({{ $order->dkm_complaint }})</span>
                    </div>
                </div>

                <!-- Tombol Navigasi Cepat (Ojol Style) -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a href="{{ $mapsUrl }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="min-h-[46px] px-3 py-2.5 rounded-xl border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-800 text-xs font-bold flex items-center justify-center gap-1.5 transition-colors text-decoration-none">
                        <span>🗺️</span>
                        <span>Buka Google Maps</span>
                    </a>

                    <a href="{{ $waUrl }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="min-h-[46px] px-3 py-2.5 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold flex items-center justify-center gap-1.5 transition-colors text-decoration-none">
                        <span>💬</span>
                        <span>Chat WA Pengurus</span>
                    </a>
                </div>

                <!-- Tombol Aksi Utama Lembar Kerja -->
                <a href="{{ route('technician.order.show', $order->id) }}" 
                   class="w-full min-h-[48px] py-3 px-4 {{ $order->status === 'dikerjakan' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-slate-900 hover:bg-slate-800' }} text-white font-extrabold text-sm rounded-xl shadow-sm flex items-center justify-center gap-2 transition-all text-decoration-none cursor-pointer">
                    <span>🚀</span>
                    <span>{{ $order->status === 'dikerjakan' ? 'Lanjutkan Pengerjaan Servis' : 'Buka Lembar Kerja Servis' }}</span>
                </a>

            </div>
        @empty
            <div class="bg-white rounded-3xl border border-dashed border-slate-300 p-8 text-center text-slate-500 space-y-2">
                <span class="text-4xl block">🎉</span>
                <p class="font-bold text-slate-800 text-base">Tidak ada tugas aktif saat ini!</p>
                <p class="text-xs text-slate-500">Semua pesanan yang ditugaskan kepada Anda sudah selesai dikerjakan.</p>
            </div>
        @endforelse
    </div>

    <!-- RIWAYAT TUGAS TERAKHIR -->
    @if($completedOrders->isNotEmpty())
        <div class="space-y-3 pt-4 border-t border-slate-200">
            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider px-1">
                Riwayat Selesai Baru-Baru Ini ({{ $completedOrders->count() }})
            </h2>

            <div class="space-y-2.5">
                @foreach($completedOrders as $order)
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs flex items-center justify-between text-xs">
                        <div>
                            <span class="font-bold text-slate-900 block text-sm">{{ $order->masjid->name }}</span>
                            <span class="text-slate-500">{{ $order->order_number }} &bull; {{ $order->ac_count }} Unit AC</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full font-bold {{ $order->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $order->statusLabel() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
