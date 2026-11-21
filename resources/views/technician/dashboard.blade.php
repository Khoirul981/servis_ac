@extends('layouts.app', ['title' => 'Dashboard Tugas Teknisi - Servis AC Masjid'])

@section('content')
<div class="max-w-xl mx-auto space-y-6 py-2">

    <!-- Header Profil Teknisi -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm flex items-center justify-between">
        <div>
            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-500">Mode Teknisi Lapangan</span>
            <h1 class="text-xl font-bold text-gray-900 mt-0.5">{{ Auth::user()->name }}</h1>
            <span class="text-xs text-gray-500 font-mono">{{ Auth::user()->phone ?? Auth::user()->email }}</span>
        </div>
        <span class="px-3 py-1.5 rounded-md bg-[#EBF2EE] text-[#1E3E34] font-semibold text-xs border border-[#1E3E34]/20">
            {{ now()->translatedFormat('d F Y') }}
        </span>
    </div>

    <!-- TUGAS AKTIF HARI INI -->
    <div class="space-y-4">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-base font-bold text-gray-900">
                Tugas Hari Ini ({{ $activeOrders->count() }})
            </h2>
            <span class="text-xs text-gray-500">Urut berdasarkan jadwal</span>
        </div>

        @forelse($activeOrders as $index => $order)
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $order->masjid->pic_phone);
                if (str_starts_with($cleanPhone, '08')) { $cleanPhone = '628' . substr($cleanPhone, 2); }
                $waText = urlencode("Assalamualaikum Pak {$order->masjid->pic_name}, saya " . Auth::user()->name . " dari tim Servis AC Masjid terkait jadwal servis di {$order->masjid->name}.");
                $waUrl = "https://wa.me/{$cleanPhone}?text={$waText}";
                
                $mapsQuery = urlencode($order->masjid->name . ', ' . $order->masjid->address);
                $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$mapsQuery}";
            @endphp

            <div class="bg-white rounded-xl border {{ $order->status === 'dikerjakan' ? 'border-[#1E3E34] ring-1 ring-[#1E3E34]' : 'border-gray-200' }} p-5 shadow-sm space-y-4">
                
                <!-- Status & No Pesanan -->
                <div class="flex items-start justify-between gap-2 pb-3 border-b border-gray-100">
                    <div>
                        <span class="text-xs font-mono font-medium text-gray-500 block">{{ $order->order_number }}</span>
                        <h3 class="text-lg font-bold text-gray-900 mt-0.5">{{ $order->masjid->name }}</h3>
                    </div>
                    @if($order->status === 'dikerjakan')
                        <span class="px-3 py-1 rounded-md text-xs font-semibold bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]">
                            Sedang Dikerjakan
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-md text-xs font-semibold bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]">
                            Dijadwalkan
                        </span>
                    @endif
                </div>

                <!-- Info Lokasi & Kontak DKM -->
                <div class="text-sm space-y-1.5">
                    <p class="text-xs text-gray-700 leading-relaxed">{{ $order->masjid->address }}</p>
                    <p class="text-xs font-medium text-gray-900">
                        Kontak: {{ $order->masjid->pic_name }} ({{ $order->masjid->pic_phone }})
                    </p>
                    <div class="flex items-center justify-between pt-2 text-xs text-gray-600 border-t border-gray-100">
                        <span>Jadwal: <strong>{{ $order->service_time ?? 'Fleksibel' }}</strong></span>
                        <span><strong>{{ $order->ac_count }} Unit AC</strong> ({{ $order->dkm_complaint }})</span>
                    </div>
                </div>

                <!-- Dua Tombol Utilitas Berdampingan (Sesuai Panduan) -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a href="{{ $mapsUrl }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="min-h-[46px] px-3 py-2.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-800 text-xs font-medium flex items-center justify-center transition-colors text-decoration-none">
                        Buka Google Maps
                    </a>

                    <a href="{{ $waUrl }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="min-h-[46px] px-3 py-2.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-800 text-xs font-medium flex items-center justify-center transition-colors text-decoration-none">
                        Chat WhatsApp
                    </a>
                </div>

                <!-- Tombol Aksi Lembar Kerja (Minimal 48px, Hijau Khidmat #1E3E34) -->
                <a href="{{ route('technician.order.show', $order->id) }}" 
                   class="w-full min-h-[48px] py-3 px-4 bg-[#1E3E34] hover:bg-[#142B24] text-white font-medium text-sm rounded-lg shadow-xs flex items-center justify-center transition-colors text-decoration-none cursor-pointer">
                    {{ $order->status === 'dikerjakan' ? 'Lanjutkan Pengerjaan Servis' : 'Buka Lembar Kerja Servis' }}
                </a>

            </div>
        @empty
            <div class="bg-white rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-500 space-y-1">
                <p class="font-semibold text-gray-900 text-base">Tidak ada tugas aktif saat ini</p>
                <p class="text-xs text-gray-500">Seluruh jadwal servis yang ditugaskan kepada Anda telah diselesaikan.</p>
            </div>
        @endforelse
    </div>

    <!-- RIWAYAT TUGAS TERAKHIR -->
    @if($completedOrders->isNotEmpty())
        <div class="space-y-3 pt-4 border-t border-gray-200">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-1">
                Riwayat Selesai Baru-Baru Ini ({{ $completedOrders->count() }})
            </h2>

            <div class="space-y-2">
                @foreach($completedOrders as $order)
                    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-xs flex items-center justify-between text-xs">
                        <div>
                            <span class="font-semibold text-gray-900 block text-sm">{{ $order->masjid->name }}</span>
                            <span class="text-gray-500">{{ $order->order_number }} &bull; {{ $order->ac_count }} Unit AC</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-md font-medium text-xs {{ $order->status === 'selesai' ? 'bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC]' : 'bg-[#FFEDD5] text-[#C2410C] border border-[#FDBA74]' }}">
                            {{ $order->statusLabel() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
