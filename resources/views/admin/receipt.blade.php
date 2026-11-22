@extends('layouts.app', ['title' => 'Kuitansi Servis AC - ' . $order->order_number])

@section('content')
<div class="max-w-2xl mx-auto space-y-6 py-2">

    <!-- Tombol Aksi Atas -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 no-print">
        <div class="flex items-center gap-3">
            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard', ['tab' => 'selesai']) }}" class="text-xs font-medium text-gray-600 hover:text-gray-900 inline-flex items-center gap-1">
                        &larr; Meja Admin
                    </a>
                    <span class="text-gray-300">|</span>
                @endif
            @endauth
            <a href="{{ route('order.status', ['order_number' => $order->order_number]) }}" class="text-xs font-semibold text-[#1E3E34] hover:underline inline-flex items-center gap-1">
                &larr; Halaman Pesanan (Status: Sudah Dibayar)
            </a>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-[#1E3E34] hover:bg-[#142B24] text-white text-xs font-semibold shadow-xs cursor-pointer">
                Cetak Kuitansi
            </button>
            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-lg bg-white hover:bg-gray-50 text-gray-800 text-xs font-medium border border-gray-300 shadow-xs text-decoration-none cursor-pointer">
                        Kirim ke WhatsApp DKM
                    </a>
                @endif
            @endauth
        </div>
    </div>

    <!-- KUITANSI RESMI CETAK (Print-Friendly) -->
    <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm print:border-none print:shadow-none print:p-0 space-y-6">
        
        <!-- Header Kuitansi -->
        <div class="flex items-start justify-between border-b-2 border-gray-900 pb-5">
            <div>
                <h1 class="text-xl font-bold text-gray-900 leading-tight">SERVIS AC MASJID & MUSHOLLA</h1>
                <p class="text-xs text-gray-500">Layanan Perawatan & Perbaikan Pendingin Ruang Ibadah</p>
            </div>
            <div class="text-right">
                <span class="block text-xs uppercase font-semibold text-gray-400">Bukti Pelunasan Resmi</span>
                <span class="block text-base font-bold text-gray-900 font-mono">{{ $order->order_number }}</span>
            </div>
        </div>

        <!-- Tautan & Status Pesanan Di Dalam Kuitansi -->
        <div class="p-3.5 rounded-lg bg-[#EBF2EE] border border-[#1E3E34]/20 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-[#DCFCE7] text-[#15803D] font-bold text-xs border border-[#86EFAC]">
                    STATUS: SUDAH DIBAYAR
                </span>
                <span class="text-gray-700">
                    Pembayaran servis telah diverifikasi lunas oleh admin.
                </span>
            </div>
            <a href="{{ route('order.status', ['order_number' => $order->order_number]) }}" 
               class="font-semibold text-[#1E3E34] hover:underline inline-flex items-center gap-1 no-print whitespace-nowrap">
                Lihat Halaman Data Pesanan &rarr;
            </a>
            <span class="hidden print:inline text-[11px] text-gray-500 font-mono">
                {{ route('order.status', ['order_number' => $order->order_number]) }}
            </span>
        </div>

        <!-- Identitas Masjid & Pelunasan -->
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="space-y-1">
                <span class="text-gray-400 uppercase font-semibold tracking-wider block">Ditagihkan Kepada:</span>
                <strong class="text-sm font-bold text-gray-900 block">{{ $order->masjid->name }}</strong>
                <p class="text-gray-600 leading-relaxed">{{ $order->masjid->address }}</p>
                <p class="text-gray-700">Kontak: <strong>{{ $order->masjid->pic_name }}</strong> ({{ $order->masjid->pic_phone }})</p>
            </div>
            <div class="space-y-1 text-right">
                <span class="text-gray-400 uppercase font-semibold tracking-wider block">Informasi Pembayaran:</span>
                <p class="text-gray-700">Waktu Bayar: <strong>{{ $order->paid_at ? $order->paid_at->translatedFormat('d F Y, H:i') : '-' }} WIB</strong></p>
                <p class="text-gray-700">Metode: <strong>{{ $metodeText }}</strong></p>
                <p class="text-gray-700">Teknisi: <strong>{{ $order->technician->name ?? 'Tim Teknisi' }}</strong></p>
                <span class="inline-block mt-1 px-2.5 py-1 rounded-md bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC] font-semibold text-xs">
                    LUNAS &amp; BERES
                </span>
            </div>
        </div>

        <!-- Tabel Rincian Biaya -->
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-50 text-gray-700 font-semibold border-b border-gray-200 uppercase text-[11px]">
                    <tr>
                        <th class="p-3.5">Deskripsi Pekerjaan Servis</th>
                        <th class="p-3.5 text-center">Unit</th>
                        <th class="p-3.5 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @if($order->serviceItems->isNotEmpty())
                        @foreach($order->serviceItems as $sItem)
                            <tr>
                                <td class="p-3.5">
                                    <strong class="text-gray-900 block text-xs">
                                        {{ $sItem->service_category }} ({{ $sItem->ac_type }})
                                    </strong>
                                    <span class="text-gray-500 text-[11px]">
                                        @if($sItem->room_name) Lokasi: {{ $sItem->room_name }} &bull; @endif
                                        @ Rp {{ number_format($sItem->price_per_unit, 0, ',', '.') }} / unit
                                    </span>
                                </td>
                                <td class="p-3.5 text-center font-semibold text-gray-800">{{ $sItem->quantity }}</td>
                                <td class="p-3.5 text-right font-semibold text-gray-800">Rp {{ number_format($sItem->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="p-3.5">
                                <strong class="text-gray-900 block text-xs">{{ $order->dkm_complaint }}</strong>
                                <span class="text-gray-500 text-[11px]">Pembersihan filter, evaporator, blower indoor, condenser outdoor & pengecekan kelistrikan</span>
                            </td>
                            <td class="p-3.5 text-center font-semibold text-gray-800">{{ $order->ac_count }}</td>
                            <td class="p-3.5 text-right font-semibold text-gray-800">Rp {{ number_format($order->base_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif

                    @if($order->spareparts->isNotEmpty())
                        @foreach($order->spareparts as $item)
                            <tr>
                                <td class="p-3.5">
                                    <strong class="text-gray-900 block text-xs">{{ $item->item_name }}</strong>
                                    <span class="text-gray-600 text-[11px]">@ Rp {{ number_format($item->price, 0, ',', '.') }} / unit</span>
                                </td>
                                <td class="p-3.5 text-center font-semibold text-gray-800">{{ $item->quantity }}x</td>
                                <td class="p-3.5 text-right font-semibold text-gray-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @elseif($order->additional_amount > 0)
                        <tr>
                            <td class="p-3.5">
                                <strong class="text-gray-900 block text-xs">Suku Cadang / Tindakan Tambahan</strong>
                                <span class="text-gray-600 text-[11px]">{{ $order->additional_notes }}</span>
                            </td>
                            <td class="p-3.5 text-center font-semibold text-gray-800">1</td>
                            <td class="p-3.5 text-right font-semibold text-gray-800">Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="2" class="p-3.5 text-right font-bold text-gray-900 text-sm">TOTAL DIBAYARKAN:</td>
                        <td class="p-3.5 text-right font-bold text-[#1E3E34] text-base">Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Catatan & Tanda Tangan -->
        <div class="pt-6 border-t border-gray-100 flex items-end justify-between text-xs">
            <div class="max-w-xs text-gray-500 leading-relaxed italic">
                Terima kasih atas kepercayaannya. Semoga AC masjid senantiasa sejuk dan memberikan kenyamanan bagi jamaah ibadah.
            </div>
            <div class="text-center space-y-12">
                <span class="block text-gray-500">Pengelola Servis AC</span>
                <span class="block font-semibold text-gray-900 border-t border-gray-300 pt-1 px-4">Admin Operasional</span>
            </div>
        </div>

    </div>

</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { background-color: white !important; }
    header, footer { display: none !important; }
}
</style>
@endsection
