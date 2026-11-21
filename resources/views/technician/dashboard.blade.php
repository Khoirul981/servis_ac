@extends('layouts.app', ['title' => 'Tugas Teknisi Lapangan - Servis AC Masjid'])

@section('content')
<div class="max-w-xl mx-auto space-y-5">
    <!-- Header Mobile Teknisi -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-bold mb-2">
            <span>🔧</span> MODE TEKNISI LAPANGAN
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Halo, {{ Auth::user()->name }}!</h1>
        <p class="text-sm text-slate-600 mt-0.5">Tugas servis AC yang ditugaskan kepada Anda hari ini.</p>
    </div>

    <!-- Info Banner Menuju Fase 4 -->
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 text-emerald-900 shadow-sm">
        <h2 class="text-base font-bold flex items-center gap-2 mb-1.5">
            <span>📱</span> Autentikasi Teknisi Berhasil Aktif!
        </h2>
        <p class="text-sm leading-relaxed text-emerald-800">
            Akun Anda terdeteksi sebagai <strong>Teknisi Lapangan</strong>. Pada <strong>Fase 4</strong>, antarmuka ini akan memuat <strong>Kartu Tugas Harian</strong> bergaya aplikasi ojek online (lengkap dengan tombol Google Maps, chat WhatsApp DKM, kamera foto sebelum & sesudah, serta tombol selesai).
        </p>
    </div>

    <!-- Kartu Contoh Tugas Yang Sedang Terjadwal -->
    @php
        $myOrders = \App\Models\Order::with('masjid')->where('technician_id', Auth::id())->get();
    @endphp

    <div class="space-y-3">
        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Tugas Terjadwal ({{ $myOrders->count() }}):</h2>
        @forelse($myOrders as $order)
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 mb-1">
                            {{ $order->order_number }}
                        </span>
                        <h3 class="text-lg font-bold text-slate-900">{{ $order->masjid->name }}</h3>
                        <p class="text-sm text-slate-600 mt-0.5">{{ $order->masjid->address }}</p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800">
                        {{ $order->statusLabel() }}
                    </span>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                    <span>🕒 Jam: <strong>{{ $order->service_time ?? '-' }}</strong></span>
                    <span>❄️ Unit AC: <strong>{{ $order->ac_count }} Unit</strong></span>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-6 text-center text-slate-500">
                Belum ada tugas servis yang ditugaskan kepada Anda hari ini.
            </div>
        @endforelse
    </div>
</div>
@endsection
