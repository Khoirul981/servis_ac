@extends('layouts.app', ['title' => 'Pesanan Berhasil Dikirim - Servis AC Masjid'])

@section('content')
<div class="max-w-xl mx-auto py-4 sm:py-8">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 text-center">

        <!-- Ikon Sukses -->
        <div class="w-16 h-16 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 ring-8 ring-emerald-50">
            ✅
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
            Alhamdulillah, Pesanan Diterima!
        </h1>
        <p class="text-sm sm:text-base text-slate-600 mt-2">
            Permintaan servis AC masjid Anda sudah masuk ke meja pengelola kami.
        </p>

        <!-- Kartu Ringkasan Pesanan -->
        <div class="mt-6 p-5 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-3">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Nomor Registrasi:</span>
                <span class="text-base font-black text-emerald-800 font-mono">{{ $order->order_number }}</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                    <span class="block text-xs text-slate-500">Nama Masjid:</span>
                    <strong class="text-slate-900 block truncate">{{ $order->masjid->name }}</strong>
                </div>
                <div>
                    <span class="block text-xs text-slate-500">Pengurus / Pemesan:</span>
                    <strong class="text-slate-900 block truncate">{{ $order->masjid->pic_name }}</strong>
                </div>
            </div>

            <div class="text-sm">
                <span class="block text-xs text-slate-500">Alamat:</span>
                <span class="text-slate-700 text-xs leading-relaxed block">{{ $order->masjid->address }}</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm pt-2 border-t border-slate-200">
                <div>
                    <span class="block text-xs text-slate-500">Layanan:</span>
                    <strong class="text-slate-900">{{ $order->dkm_complaint }}</strong>
                </div>
                <div>
                    <span class="block text-xs text-slate-500">Jumlah Unit:</span>
                    <strong class="text-slate-900">{{ $order->ac_count }} Unit AC</strong>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600">Estimasi Biaya Awal:</span>
                <span class="text-lg font-black text-emerald-700">Rp {{ number_format($order->base_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Tombol WhatsApp Konfirmasi (Aksi Utama Super Besar) -->
        <div class="mt-6 space-y-3">
            <a href="{{ $whatsappUrl }}" 
               target="_blank" 
               rel="noopener noreferrer"
               class="w-full min-h-[52px] py-4 px-6 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-extrabold text-base sm:text-lg rounded-2xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2.5 text-decoration-none">
                <span class="text-2xl">💬</span>
                <span>Konfirmasi Pesanan ke WhatsApp Admin</span>
            </a>

            <p class="text-xs text-slate-500 leading-relaxed">
                Klik tombol di atas untuk mengirimkan rincian pesanan langsung ke WhatsApp Admin (<strong>{{ $adminPhone }}</strong>). Admin akan segera mengabari jadwal teknisi.
            </p>
        </div>

        <!-- Tombol Buat Pesanan Baru -->
        <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-center gap-4">
            <a href="{{ route('order.create') }}" 
               class="text-sm font-bold text-slate-600 hover:text-emerald-700 transition-colors inline-flex items-center gap-1.5">
                <span>➕</span>
                <span>Buat Pesanan Baru Lainnya</span>
            </a>
        </div>

    </div>
</div>
@endsection
