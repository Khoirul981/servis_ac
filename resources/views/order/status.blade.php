@extends('layouts.app', ['title' => 'Pesanan Servis AC - ' . $order->order_number])

@section('content')
<div class="max-w-2xl mx-auto py-3 sm:py-6 space-y-5">

    <!-- Tombol Navigasi Atas -->
    <div class="flex items-center justify-between">
        <a href="{{ route('order.create') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900 inline-flex items-center gap-1 transition-colors">
            &larr; Formulir Pemesanan
        </a>
        <span class="text-xs text-gray-400">Pembaruan: {{ $order->updated_at->translatedFormat('d M Y, H:i') }} WIB</span>
    </div>

    <!-- Header Pesanan Berhasil Dikirim (Konsisten untuk Order Baru & Cek Nomor Registrasi) -->
    <div class="text-center space-y-2 pt-2">
        <span class="inline-block px-3 py-1 rounded-md text-xs font-semibold bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC] uppercase tracking-wider">
            Pesanan Diterima
        </span>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">
            Pesanan Anda Berhasil Dikirim
        </h1>
        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed max-w-lg mx-auto">
            Permintaan servis AC masjid telah masuk ke meja kerja admin pengelola kami.
        </p>
    </div>

    <!-- KARTU STATUS & RINCIAN PESANAN LENGKAP -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-7 shadow-sm space-y-6">

        <!-- Header Nomor & Status Pengerjaan -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-gray-200 gap-3">
            <div>
                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Nomor Registrasi Pesanan:</span>
                <span class="text-xl sm:text-2xl font-bold text-gray-900 font-mono tracking-tight mt-0.5 block">
                    {{ $order->order_number }}
                </span>
            </div>
            <div>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider
                    @if($order->status === 'selesai') bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC]
                    @elseif($order->status === 'menunggu_verifikasi') bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]
                    @elseif($order->status === 'pekerjaan_selesai') bg-amber-100 text-amber-900 border border-amber-300
                    @elseif($order->status === 'menunggu_bayar') bg-orange-100 text-orange-800 border border-orange-200
                    @elseif($order->status === 'dikerjakan') bg-amber-100 text-amber-900 border border-amber-200
                    @elseif($order->status === 'dijadwalkan') bg-blue-100 text-blue-800 border border-blue-200
                    @elseif($order->status === 'batal') bg-red-100 text-red-800 border border-red-200
                    @else bg-gray-100 text-gray-800 border border-gray-200
                    @endif">
                    Status: {{ $order->statusLabel() }}
                </span>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STATUS PEMBAYARAN (BELUM DIBAYAR VS SUDAH DIBAYAR) -->
        <!-- ========================================================================= -->
        @if($order->isPaid())
            <!-- KOTAK STATUS: SUDAH DIBAYAR -->
            <div class="p-4 sm:p-5 rounded-xl bg-[#DCFCE7]/70 border border-[#86EFAC] space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-[#15803D] text-white flex items-center justify-center font-bold text-xs shrink-0">
                                &#10003;
                            </span>
                            <span class="text-xs uppercase tracking-wider font-semibold text-[#15803D]">
                                Status Pembayaran:
                            </span>
                        </div>
                        <h2 class="text-lg font-bold text-[#15803D] pl-8 sm:pl-0">
                            SUDAH DIBAYAR (LUNAS)
                        </h2>
                    </div>
                    <span class="px-3 py-1 rounded-md bg-[#15803D] text-white font-bold text-xs shadow-xs self-start sm:self-center">
                        Terverifikasi Admin
                    </span>
                </div>

                <div class="text-xs text-gray-700 pt-2 border-t border-[#86EFAC]/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <span>Waktu Pelunasan: <strong>{{ $order->paid_at ? $order->paid_at->translatedFormat('d F Y, H:i') : '-' }} WIB</strong></span>
                        <span class="block sm:inline sm:ml-2">Metode: <strong class="uppercase">{{ $order->payment_method ?? 'Lunas' }}</strong></span>
                    </div>
                    <a href="{{ route('order.public_receipt', ['order_number' => $order->order_number]) }}" 
                       class="inline-flex items-center justify-center px-3.5 py-1.5 rounded-lg bg-[#1E3E34] hover:bg-[#142B24] text-white font-semibold text-xs text-decoration-none transition-colors">
                        Lihat Kuitansi Resmi &rarr;
                    </a>
                </div>
            </div>
        @else
            <!-- KOTAK STATUS: BELUM DIBAYAR -->
            <div class="p-4 sm:p-5 rounded-xl bg-amber-50 border border-amber-200 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                !
                            </span>
                            <span class="text-xs uppercase tracking-wider font-semibold text-amber-800">
                                Status Pembayaran:
                            </span>
                        </div>
                        <h2 class="text-lg font-bold text-amber-900 pl-8 sm:pl-0">
                            BELUM DIBAYAR
                        </h2>
                    </div>
                    <span class="px-3 py-1 rounded-md text-xs font-bold self-start sm:self-center
                        @if($order->status === 'menunggu_verifikasi') bg-blue-100 text-blue-900 border border-blue-300
                        @elseif($order->status === 'pekerjaan_selesai') bg-amber-100 text-amber-900 border border-amber-300
                        @else bg-amber-200 text-amber-900
                        @endif">
                        @if($order->status === 'menunggu_verifikasi')
                            Menunggu Verifikasi Admin
                        @elseif($order->status === 'pekerjaan_selesai')
                            Menunggu Pelaporan Teknisi
                        @else
                            Menunggu Verifikasi Admin
                        @endif
                    </span>
                </div>

                @if($order->status === 'menunggu_verifikasi' || ($order->status === 'pekerjaan_selesai' && $order->payment_proof))
                    <div class="pt-2 border-t border-amber-200/70 text-xs text-amber-900 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span class="font-bold">Bukti Pembayaran / Transfer Telah Dikirim Teknisi</span>
                        </div>
                        <p class="text-amber-800 text-[11px] leading-relaxed">
                            Foto bukti pembayaran ({{ $order->paymentMethodLabel() }}) telah masuk ke sistem dan saat ini sedang dicek dan diverifikasi oleh Admin.
                        </p>
                    </div>
                @elseif($order->status === 'pekerjaan_selesai')
                    <div class="pt-2 border-t border-amber-200/70 text-xs text-amber-900 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                            <span class="font-bold">Pekerjaan Servis Selesai di Lokasi</span>
                        </div>
                        <p class="text-amber-800 text-[11px] leading-relaxed">
                            Teknisi telah menyelesaikan pengerjaan AC di lokasi masjid. Menunggu teknisi mengirimkan laporan bukti pembayaran untuk diverifikasi Admin.
                        </p>
                    </div>
                @else
                    <p class="text-xs text-amber-800 leading-relaxed pt-2 border-t border-amber-200/70">
                        Pembayaran servis dapat diselesaikan saat teknisi selesai bekerja di lokasi masjid atau melalui transfer yang akan diverifikasi oleh Admin.
                    </p>
                @endif
            </div>
        @endif

        <!-- INFORMASI MASJID & JADWAL -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-1">
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-2">
                <span class="text-gray-400 font-semibold uppercase tracking-wider block text-[11px]">Lokasi Masjid / Musholla</span>
                <strong class="text-sm font-bold text-gray-900 block">{{ $order->masjid->name }}</strong>
                <p class="text-gray-600 leading-relaxed">{{ $order->masjid->address }}</p>
                <div class="pt-2 border-t border-gray-200 text-gray-700">
                    <span>Pengurus (DKM): <strong>{{ $order->masjid->pic_name }}</strong></span>
                    <span class="block font-mono text-gray-500 mt-0.5">{{ $order->masjid->pic_phone }}</span>
                </div>
            </div>

            <div class="p-4 rounded-lg bg-gray-50 border border-gray-200 space-y-2 flex flex-col justify-between">
                <div class="space-y-2">
                    <span class="text-gray-400 font-semibold uppercase tracking-wider block text-[11px]">Informasi Penugasan Servis</span>
                    <div>
                        <span class="text-gray-500 block">Jadwal Kunjungan:</span>
                        <strong class="text-gray-900 text-xs block">
                            {{ $order->service_date ? $order->service_date->translatedFormat('l, d F Y') : 'Menunggu Konfirmasi' }}
                            @if($order->service_time) &bull; {{ $order->service_time }} @endif
                        </strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Teknisi Bertugas:</span>
                        <strong class="text-gray-900 text-xs block">
                            {{ $order->technician->name ?? 'Belum Ditugaskan (Menunggu Admin)' }}
                        </strong>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-200">
                    <span class="text-gray-500">Kategori Keluhan: <strong>{{ $order->dkm_complaint }}</strong></span>
                </div>
            </div>
        </div>

        <!-- RINCIAN BIAYA SERVIS (DENGAN TULISAN BIAYA AWAL BELUM PASTI) -->
        <div class="space-y-3 pt-2">
            <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">
                Rincian Biaya Servis
            </h3>

            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50 text-gray-700 font-semibold border-b border-gray-200 uppercase text-[11px]">
                        <tr>
                            <th class="p-3">Keterangan Layanan / Komponen</th>
                            <th class="p-3 text-center">Jumlah</th>
                            <th class="p-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @if($order->serviceItems->isNotEmpty())
                            @foreach($order->serviceItems as $sItem)
                                <tr>
                                    <td class="p-3">
                                        <div class="font-bold text-gray-900 text-xs">
                                            {{ $sItem->service_category }} ({{ $sItem->ac_type }})
                                        </div>
                                        @if($sItem->room_name)
                                            <div class="text-[11px] text-gray-500">Ruangan: {{ $sItem->room_name }}</div>
                                        @endif
                                        <span class="text-[10px] text-gray-400">@ Rp {{ number_format($sItem->price_per_unit, 0, ',', '.') }} / unit</span>
                                    </td>
                                    <td class="p-3 text-center font-semibold text-gray-800">{{ $sItem->quantity }} Unit</td>
                                    <td class="p-3 text-right font-semibold text-gray-800">
                                        Rp {{ number_format($sItem->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="p-3">
                                    <strong class="text-gray-900 block">{{ $order->dkm_complaint }}</strong>
                                    <span class="text-gray-500 text-[11px]">Jasa perawatan pendingin ruang ibadah</span>
                                </td>
                                <td class="p-3 text-center font-semibold text-gray-800">{{ $order->ac_count }} Unit</td>
                                <td class="p-3 text-right font-semibold text-gray-800">
                                    Rp {{ number_format($order->base_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif

                        <!-- Rincian Multi-Item Suku Cadang Jika Ada -->
                        @if($order->spareparts->isNotEmpty())
                            @foreach($order->spareparts as $item)
                                <tr>
                                    <td class="p-3">
                                        <strong class="text-gray-900 block">{{ $item->item_name }}</strong>
                                        <span class="text-gray-500 text-[11px]">@ Rp {{ number_format($item->price, 0, ',', '.') }} / unit</span>
                                    </td>
                                    <td class="p-3 text-center font-semibold text-gray-800">{{ $item->quantity }}x</td>
                                    <td class="p-3 text-right font-semibold text-gray-800">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        @elseif($order->additional_amount > 0)
                            <tr>
                                <td class="p-3">
                                    <strong class="text-gray-900 block">Tambahan Suku Cadang / Freon</strong>
                                    <span class="text-gray-500 text-[11px]">{{ $order->additional_notes }}</span>
                                </td>
                                <td class="p-3 text-center font-semibold text-gray-800">1</td>
                                <td class="p-3 text-right font-semibold text-gray-800">
                                    Rp {{ number_format($order->additional_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        @if($order->additional_amount > 0)
                            <tr>
                                <td colspan="2" class="p-3 text-right font-semibold text-gray-600 text-xs">Biaya Awal:</td>
                                <td class="p-3 text-right font-semibold text-gray-800 text-xs">Rp {{ number_format($order->base_amount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="p-3 text-right font-semibold text-gray-600 text-xs">Tambahan Suku Cadang:</td>
                                <td class="p-3 text-right font-semibold text-gray-800 text-xs">Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="p-3.5 text-right font-bold text-gray-900 text-xs sm:text-sm">TOTAL AKHIR PEMBAYARAN:</td>
                                <td class="p-3.5 text-right font-bold text-[#1E3E34] text-base">
                                    Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="2" class="p-3.5 text-right font-bold text-gray-900 text-xs sm:text-sm">BIAYA AWAL (BELUM PASTI):</td>
                                <td class="p-3.5 text-right font-bold text-[#1E3E34] text-base">
                                    Rp {{ number_format($order->base_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
            <p class="text-[11px] text-gray-500 leading-relaxed italic">
                * Biaya di atas merupakan biaya awal dan belum pasti. Biaya akhir dapat disesuaikan jika terdapat kebutuhan penambahan freon, suku cadang, atau tindakan khusus setelah teknisi memeriksa unit AC di lokasi masjid.
            </p>
        </div>

        <!-- FOTO BUKTI SERVIS JIKA SUDAH DIUNGGAH -->
        @if($order->photos->isNotEmpty())
            <div class="space-y-3 pt-3 border-t border-gray-100">
                <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">
                    Foto Dokumentasi Pengerjaan di Lokasi ({{ $order->photos->count() }})
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($order->photos as $photo)
                        <div class="rounded-lg border border-gray-200 overflow-hidden bg-gray-50 text-xs">
                            <img src="{{ asset('storage/' . $photo->photo_url) }}" alt="Foto Servis" class="w-full h-32 object-cover">
                            <div class="p-2 space-y-0.5">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase
                                    @if($photo->photo_type === 'sebelum') bg-amber-100 text-amber-800
                                    @elseif($photo->photo_type === 'sesudah') bg-green-100 text-green-800
                                    @else bg-blue-100 text-blue-800
                                    @endif">
                                    {{ ucfirst($photo->photo_type) }}
                                </span>
                                <p class="text-gray-600 truncate mt-1 text-[11px]">{{ $photo->caption ?: 'Dokumentasi' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- TOMBOL KONFIRMASI WA ADMIN -->
        <div class="pt-4 border-t border-gray-100 space-y-3">
            <a href="{{ $whatsappUrl }}" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="w-full min-h-[48px] py-3.5 px-6 bg-[#1E3E34] hover:bg-[#142B24] text-white font-semibold text-sm rounded-lg transition-colors flex items-center justify-center text-decoration-none">
                Konfirmasi Pesanan via WhatsApp
            </a>
            <p class="text-center text-xs text-gray-500">
                Kirimkan rincian pesanan langsung ke WhatsApp Admin pengelola (<strong>{{ $adminPhone }}</strong>) agar jadwal teknisi dapat segera dikonfirmasi.
            </p>
        </div>

        <!-- TAUTAN BUAT PESANAN LAIN -->
        <div class="pt-4 border-t border-gray-100 text-center">
            <a href="{{ route('order.create') }}" 
               class="text-xs font-medium text-gray-600 hover:text-gray-900 transition-colors inline-block">
                &larr; Buat Pesanan Baru Lainnya
            </a>
        </div>

    </div>

    <!-- KOTAK CARI NOMOR PESANAN LAIN -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('order.track') }}" class="flex flex-col sm:flex-row gap-2">
            <input type="text" 
                   name="order_number" 
                   required 
                   placeholder="Mau cek nomor registrasi lain? (mis: SO-{{ now()->format('Ymd') }}-001)" 
                   class="flex-1 min-h-[44px] px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 font-mono outline-none focus:border-[#1E3E34] bg-white">
            <button type="submit" 
                    class="min-h-[44px] px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-xs font-semibold cursor-pointer transition-colors whitespace-nowrap">
                Cari Pesanan
            </button>
        </form>
    </div>

</div>
@endsection
