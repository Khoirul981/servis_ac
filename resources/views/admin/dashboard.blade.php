@extends('layouts.app', ['title' => 'Meja Pengelola Admin - Servis AC Masjid'])

@section('content')
<div class="space-y-6">
    <!-- Header Admin -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                <span>🛡️</span> MEJA PENGELOLA ADMIN
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Pusat Kendali Operasional Servis AC</h1>
            <p class="text-sm text-slate-600">Selamat datang, <strong class="text-slate-800">{{ Auth::user()->name }}</strong>. Siap mengelola pesanan hari ini.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-2 bg-slate-100 text-slate-700 font-semibold text-sm rounded-xl border border-slate-200">
                📅 {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>

    <!-- Ringkasan Statistik Singkat -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <span class="block text-xs font-semibold text-amber-700 uppercase">Pesanan Baru</span>
            <span class="block text-2xl font-black text-slate-900 mt-1">{{ \App\Models\Order::where('status', 'pesanan_baru')->count() }}</span>
            <span class="text-xs text-slate-500">Perlu dijadwalkan</span>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <span class="block text-xs font-semibold text-blue-700 uppercase">Sedang Dikerjakan</span>
            <span class="block text-2xl font-black text-slate-900 mt-1">{{ \App\Models\Order::whereIn('status', ['dijadwalkan', 'dikerjakan'])->count() }}</span>
            <span class="text-xs text-slate-500">Teknisi di lapangan</span>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <span class="block text-xs font-semibold text-purple-700 uppercase">Menunggu Bayar</span>
            <span class="block text-2xl font-black text-slate-900 mt-1">{{ \App\Models\Order::where('status', 'menunggu_bayar')->count() }}</span>
            <span class="text-xs text-slate-500">Belum lunas</span>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <span class="block text-xs font-semibold text-emerald-700 uppercase">Total Selesai</span>
            <span class="block text-2xl font-black text-slate-900 mt-1">{{ \App\Models\Order::where('status', 'selesai')->count() }}</span>
            <span class="text-xs text-slate-500">Arsip lunas</span>
        </div>
    </div>

    <!-- Notice Menuju Fase 5 -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 text-blue-900 shadow-sm">
        <h2 class="text-lg font-bold flex items-center gap-2 mb-2">
            <span>ℹ️</span> Autentikasi Admin Berhasil Aktif!
        </h2>
        <p class="text-sm leading-relaxed text-blue-800">
            Halaman ini adalah pintu masuk Meja Pengelola Admin. Pada <strong>Fase 5</strong>, halaman ini akan dilengkapi dengan <strong>Papan Kendali 4 Tab</strong>, modal penugasan teknisi, verifikasi pelunasan uang masuk, dan cetak kuitansi kirim-WA ke DKM.
        </p>
    </div>
</div>
@endsection
