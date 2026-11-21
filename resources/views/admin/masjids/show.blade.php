@extends('layouts.app', ['title' => 'Detail Masjid - ' . $masjid->name])

@section('content')
<div class="space-y-6">

    <!-- Tombol Kembali -->
    <a href="{{ route('admin.masjids.index') }}" class="text-xs font-medium text-gray-600 hover:text-gray-900 inline-flex items-center gap-1">
        &larr; Kembali ke Daftar Masjid
    </a>

    <!-- Header Profil Masjid -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-2">
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Profil Masjid & Musholla</span>
        <h1 class="text-2xl font-bold text-gray-900">{{ $masjid->name }}</h1>
        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">{{ $masjid->address }}</p>
        <div class="pt-2 border-t border-gray-100 flex items-center gap-6 text-xs text-gray-700">
            <span>Pengurus: <strong>{{ $masjid->pic_name }}</strong></span>
            <span>WhatsApp: <strong>{{ $masjid->pic_phone }}</strong></span>
            <span>Servis Terakhir: <strong>{{ $masjid->last_service_date ? $masjid->last_service_date->translatedFormat('d M Y') : 'Belum pernah' }}</strong></span>
        </div>
    </div>

    <!-- DAFTAR UNIT AC & FORM TAMBAH -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">
                Daftar Unit AC Terpasang ({{ $masjid->acUnits->count() }})
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @forelse($masjid->acUnits as $unit)
                <div class="p-4 rounded-lg border border-gray-200 bg-gray-50 space-y-1 text-xs">
                    <span class="block font-semibold text-sm text-gray-900">{{ $unit->room_name }}</span>
                    <div class="flex items-center justify-between text-gray-600 pt-1">
                        <span>Kapasitas: <strong>{{ $unit->ac_type }}</strong></span>
                        <span>Merk: <strong>{{ $unit->brand ?? '-' }}</strong></span>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-3 p-6 text-center text-gray-400 text-xs">
                    Belum ada data unit AC terdaftar untuk masjid ini.
                </div>
            @endforelse
        </div>

        <!-- Form Tambah Unit AC Baru -->
        <div class="pt-4 border-t border-gray-100">
            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Tambah Unit AC Baru:</h3>
            <form method="POST" action="{{ route('admin.masjids.ac.store', $masjid->id) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                @csrf
                <input type="text" name="room_name" required placeholder="Nama Ruang (mis: Shaf Putri)" class="px-3.5 py-2 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                <input type="text" name="ac_type" required placeholder="Tipe / PK (mis: 1.5 PK)" class="px-3.5 py-2 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                <input type="text" name="brand" placeholder="Merk (mis: Daikin)" class="px-3.5 py-2 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                <button type="submit" class="px-4 py-2 bg-[#1E3E34] hover:bg-[#142B24] text-white rounded-lg text-xs font-medium cursor-pointer transition-colors">Simpan Unit AC</button>
            </form>
        </div>
    </div>

    <!-- RIWAYAT SERVIS DI MASJID INI -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-3">
        <h2 class="text-base font-semibold text-gray-900">
            Riwayat Servis AC di Masjid Ini
        </h2>

        <div class="space-y-2">
            @forelse($masjid->orders as $order)
                <div class="p-3.5 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-mono font-medium text-gray-900 block">{{ $order->order_number }}</span>
                        <span class="text-gray-500">{{ $order->ac_count }} Unit AC &bull; {{ $order->dkm_complaint }}</span>
                    </div>
                    <div class="text-right">
                        <span class="px-2.5 py-1 rounded-md font-semibold text-[11px] {{ $order->status === 'selesai' ? 'bg-[#DCFCE7] text-[#15803D]' : 'bg-gray-200 text-gray-800' }}">
                            {{ $order->statusLabel() }}
                        </span>
                        <span class="block text-[11px] text-gray-400 mt-1">{{ $order->created_at->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-400">Belum ada riwayat servis untuk masjid ini.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
