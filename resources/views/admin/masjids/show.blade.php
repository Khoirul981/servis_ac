@extends('layouts.app', ['title' => 'Detail Masjid - ' . $masjid->name])

@section('content')
<div class="space-y-6">

    <!-- Tombol Kembali -->
    <a href="{{ route('admin.masjids.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1">
        <span>&larr;</span> Kembali ke Daftar Masjid
    </a>

    <!-- Header Profil Masjid -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-2">
        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Profil Masjid / Musholla</span>
        <h1 class="text-2xl font-black text-slate-900">{{ $masjid->name }}</h1>
        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ $masjid->address }}</p>
        <div class="pt-2 border-t border-slate-100 flex items-center gap-6 text-xs text-slate-700">
            <span>👤 PIC: <strong>{{ $masjid->pic_name }}</strong></span>
            <span>📱 WhatsApp: <strong>{{ $masjid->pic_phone }}</strong></span>
            <span>🕒 Servis Terakhir: <strong>{{ $masjid->last_service_date ? $masjid->last_service_date->translatedFormat('d M Y') : 'Belum pernah' }}</strong></span>
        </div>
    </div>

    <!-- DAFTAR UNIT AC & FORM TAMBAH -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                <span>❄️</span>
                <span>Daftar Unit AC Terpasang ({{ $masjid->acUnits->count() }})</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @forelse($masjid->acUnits as $unit)
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 space-y-1 text-xs">
                    <span class="block font-black text-sm text-slate-900">{{ $unit->room_name }}</span>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Kapasitas: <strong>{{ $unit->ac_type }}</strong></span>
                        <span>Merk: <strong>{{ $unit->brand ?? '-' }}</strong></span>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-3 p-6 text-center text-slate-400 text-xs">
                    Belum ada data unit AC terdaftar untuk masjid ini.
                </div>
            @endforelse
        </div>

        <!-- Form Tambah Unit AC Baru -->
        <div class="pt-4 border-t border-slate-100">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">+ Tambah Unit AC Baru:</h3>
            <form method="POST" action="{{ route('admin.masjids.ac.store', $masjid->id) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                @csrf
                <input type="text" name="room_name" required placeholder="Nama Ruang (mis: Shaf Putri)" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none">
                <input type="text" name="ac_type" required placeholder="Tipe / PK (mis: 1.5 PK)" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none">
                <input type="text" name="brand" placeholder="Merk (mis: Daikin)" class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold cursor-pointer">Simpan Unit AC</button>
            </form>
        </div>
    </div>

    <!-- RIWAYAT SERVIS DI MASJID INI -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-3">
        <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
            <span>📜</span>
            <span>Riwayat Seluruh Servis AC</span>
        </h2>

        <div class="space-y-2">
            @forelse($masjid->orders as $order)
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-mono font-bold text-slate-900 block">{{ $order->order_number }}</span>
                        <span class="text-slate-500">{{ $order->ac_count }} Unit AC &bull; {{ $order->dkm_complaint }}</span>
                    </div>
                    <div class="text-right">
                        <span class="px-2.5 py-1 rounded-full font-bold text-[11px] {{ $order->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-800' }}">
                            {{ $order->statusLabel() }}
                        </span>
                        <span class="block text-[11px] text-slate-400 mt-1">{{ $order->created_at->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400">Belum ada riwayat servis untuk masjid ini.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
