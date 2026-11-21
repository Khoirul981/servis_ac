@extends('layouts.app', ['title' => 'Master Data Masjid - Servis AC Masjid'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Master Data Masjid & Musholla</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Daftar seluruh rumah ibadah yang telah terdaftar dalam sistem perawatan AC.</p>
        </div>

        <!-- Pencarian -->
        <form method="GET" action="{{ route('admin.masjids.index') }}" class="w-full sm:w-auto flex items-center gap-2">
            <input type="text" 
                   name="search" 
                   value="{{ $search }}"
                   placeholder="Cari nama atau alamat masjid..." 
                   class="px-4 py-2 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] w-full sm:w-64 bg-white">
            <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white rounded-lg text-xs font-medium cursor-pointer transition-colors">Cari</button>
        </form>
    </div>

    <!-- Tabel Daftar Masjid -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <table class="w-full text-xs text-left">
            <thead class="bg-gray-50 border-b border-gray-200 uppercase text-gray-700 font-semibold text-[11px] tracking-wider">
                <tr>
                    <th class="py-3.5 px-4">Nama Masjid & Alamat</th>
                    <th class="py-3.5 px-4">Pengurus (DKM / Marbot)</th>
                    <th class="py-3.5 px-4 text-center">Unit AC</th>
                    <th class="py-3.5 px-4 text-center">Servis Terakhir</th>
                    <th class="py-3.5 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($masjids as $masjid)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="py-3.5 px-4">
                            <strong class="text-sm font-semibold text-gray-900 block">{{ $masjid->name }}</strong>
                            <span class="text-gray-500 text-xs">{{ $masjid->address }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-medium text-gray-800 block">{{ $masjid->pic_name }}</span>
                            <span class="text-gray-500 font-mono">{{ $masjid->pic_phone }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-md bg-[#EBF2EE] text-[#1E3E34] font-semibold border border-[#1E3E34]/20">
                                {{ $masjid->ac_units_count }} Unit
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center text-gray-600">
                            {{ $masjid->last_service_date ? $masjid->last_service_date->translatedFormat('d M Y') : 'Belum pernah' }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('admin.masjids.show', $masjid->id) }}" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-100 text-gray-800 font-medium transition-colors inline-block text-decoration-none">
                                Kelola Unit AC
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-400">Tidak ada data masjid ditemukan.</td>
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
