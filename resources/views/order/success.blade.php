@extends('layouts.app', ['title' => 'Pesanan Berhasil Dikirim - Servis AC Masjid'])

@section('content')
<div class="max-w-[520px] mx-auto py-4 sm:py-8">
    <div class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8 shadow-sm text-center space-y-6">

        <!-- Penanda Sukses Bersih -->
        <div>
            <span class="inline-block px-3 py-1 rounded-md text-xs font-semibold bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC] uppercase tracking-wider">
                Pesanan Diterima
            </span>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mt-3">
                Pesanan Anda Berhasil Dikirim
            </h1>
            <p class="text-sm text-gray-600 mt-1 leading-relaxed">
                Permintaan servis AC masjid telah masuk ke meja kerja admin pengelola kami.
            </p>
        </div>

        <!-- Kartu Ringkasan Solid -->
        <div class="p-5 rounded-lg bg-gray-50 border border-gray-200 text-left space-y-3">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <span class="text-xs font-medium text-gray-500 uppercase">Nomor Registrasi:</span>
                <span class="text-base font-bold text-[#1E3E34] font-mono">{{ $order->order_number }}</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                    <span class="block text-xs text-gray-500">Nama Masjid:</span>
                    <strong class="text-gray-900 block truncate">{{ $order->masjid->name }}</strong>
                </div>
                <div>
                    <span class="block text-xs text-gray-500">Pengurus:</span>
                    <strong class="text-gray-900 block truncate">{{ $order->masjid->pic_name }}</strong>
                </div>
            </div>

            <div class="text-sm">
                <span class="block text-xs text-gray-500">Alamat:</span>
                <span class="text-gray-700 text-xs leading-relaxed block">{{ $order->masjid->address }}</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm pt-2 border-t border-gray-200">
                <div>
                    <span class="block text-xs text-gray-500">Kategori:</span>
                    <strong class="text-gray-900">{{ $order->dkm_complaint }}</strong>
                </div>
                <div>
                    <span class="block text-xs text-gray-500">Jumlah Unit:</span>
                    <strong class="text-gray-900">{{ $order->ac_count }} Unit AC</strong>
                </div>
            </div>

            <div class="pt-2 border-t border-gray-200 flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-600">Estimasi Biaya Awal:</span>
                <span class="text-lg font-bold text-[#1E3E34]">Rp {{ number_format($order->base_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Tombol Aksi WhatsApp (Hijau Khidmat) -->
        <div class="space-y-3 pt-2">
            <a href="{{ $whatsappUrl }}" 
               target="_blank" 
               rel="noopener noreferrer"
               class="w-full min-h-[48px] py-3.5 px-6 bg-[#1E3E34] hover:bg-[#142B24] text-white font-semibold text-base rounded-lg transition-colors flex items-center justify-center text-decoration-none">
                Konfirmasi Pesanan via WhatsApp
            </a>

            <p class="text-xs text-gray-500 leading-relaxed">
                Kirimkan rincian pesanan langsung ke WhatsApp Admin pengelola (<strong>{{ $adminPhone }}</strong>) agar jadwal teknisi dapat segera dikonfirmasi.
            </p>
        </div>

        <!-- Tautan Buat Pesanan Baru -->
        <div class="pt-4 border-t border-gray-100">
            <a href="{{ route('order.create') }}" 
               class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors inline-block">
                &larr; Buat Pesanan Baru Lainnya
            </a>
        </div>

    </div>
</div>
@endsection
