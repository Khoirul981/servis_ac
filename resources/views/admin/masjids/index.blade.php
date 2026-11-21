@extends('layouts.app', ['title' => 'Master Data Masjid - Servis AC Masjid'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900">Master Data Masjid & Musholla</h1>
            <p class="text-xs sm:text-sm text-slate-500">Daftar seluruh rumah ibadah yang telah terdaftar dalam sistem perawatan AC.</p>
        </div>

        <!-- Pencarian -->
        <form method="GET" action="{{ route('admin.masjids.index') }}" class="w-full sm:w-auto flex items-center gap-2">
            <input type="text" 
                   name="search" 
                   value="{{ $search }}"
                   placeholder="Cari nama / alamat masjid..." 
                   class="px-4 py-2 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-emerald-500 w-full sm:w-64">
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold cursor-pointer">Cari</button>
        </form>
    </div>

    <!-- Tabel Daftar Masjid -->
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 border-b border-slate-200 uppercase text-slate-700 font-bold text-[11px]">
                <tr>
                    <th class="p-4">Nama Masjid & Alamat</th>
                    <th class="p-4">Pengurus (DKM / Marbot)</th>
                    <th class="p-4 text-center">Unit AC</th>
                    <th class="p-4 text-center">Servis Terakhir</th>
                    <th class="p-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($masjids as $masjid)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="p-4">
                            <strong class="text-sm font-bold text-slate-900 block">{{ $masjid->name }}</strong>
                            <span class="text-slate-500 text-xs">{{ $masjid->address }}</span>
                        </td>
                        <td class="p-4">
                            <span class="font-bold text-slate-800 block">{{ $masjid->pic_name }}</span>
                            <span class="text-slate-500 font-mono">{{ $masjid->pic_phone }}</span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-black border border-blue-200">
                                {{ $masjid->ac_units_count }} Unit
                            </span>
                        </td>
                        <td class="p-4 text-center text-slate-600">
                            {{ $masjid->last_service_date ? $masjid->last_service_date->translatedFormat('d M Y') : 'Belum pernah' }}
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.masjids.show', $masjid->id) }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold border border-emerald-200 transition-colors inline-block text-decoration-none">
                                Kelola Unit AC &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400">Tidak ada data masjid ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $masjids->links() }}
    </div>

</div>
@endsection
