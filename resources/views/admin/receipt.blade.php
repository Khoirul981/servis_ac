@extends('layouts.app', ['title' => 'Kuitansi Servis AC - ' . $order->order_number])

@section('content')
<div class="max-w-2xl mx-auto space-y-6 py-2">

    <!-- Tombol Aksi Atas -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('admin.dashboard', ['tab' => 'selesai']) }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1">
            <span>&larr;</span> Kembali ke Meja Admin
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold flex items-center gap-1.5 border border-slate-200 cursor-pointer">
                <span>🖨️</span> Cetak Kuitansi
            </button>
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black flex items-center gap-1.5 shadow-xs text-decoration-none cursor-pointer">
                <span>📲</span> Kirim ke WhatsApp DKM
            </a>
        </div>
    </div>

    <!-- KUITANSI RESMI CETAK (Print-Friendly) -->
    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm print:border-none print:shadow-none print:p-0 space-y-6">
        
        <!-- Header Kuitansi -->
        <div class="flex items-start justify-between border-b-2 border-slate-900 pb-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-2xl font-bold shadow-xs">
                    🕌
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 leading-tight">SERVIS AC MASJID & MUSHOLLA</h1>
                    <p class="text-xs text-slate-500">Layanan Perawatan & Perbaikan AC Rumah Ibadah</p>
                </div>
            </div>
            <div class="text-right">
                <span class="block text-xs uppercase font-extrabold text-slate-400">Bukti Pelunasan Resmi</span>
                <span class="block text-base font-black text-slate-900 font-mono">{{ $order->order_number }}</span>
            </div>
        </div>

        <!-- Identitas Masjid & Pelunasan -->
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="space-y-1">
                <span class="text-slate-400 uppercase font-bold tracking-wider block">Ditagihkan Kepada:</span>
                <strong class="text-sm font-black text-slate-900 block">{{ $order->masjid->name }}</strong>
                <p class="text-slate-600 leading-relaxed">{{ $order->masjid->address }}</p>
                <p class="text-slate-700">Kontak: <strong>{{ $order->masjid->pic_name }}</strong> ({{ $order->masjid->pic_phone }})</p>
            </div>
            <div class="space-y-1 text-right">
                <span class="text-slate-400 uppercase font-bold tracking-wider block">Informasi Pembayaran:</span>
                <p class="text-slate-700">Waktu Bayar: <strong>{{ $order->paid_at ? $order->paid_at->translatedFormat('d F Y, H:i') : '-' }} WIB</strong></p>
                <p class="text-slate-700">Metode: <strong>{{ $metodeText }}</strong></p>
                <p class="text-slate-700">Teknisi: <strong>{{ $order->technician->name ?? 'Tim Teknisi' }}</strong></p>
                <span class="inline-block mt-1 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-black text-xs">
                    LUNAS & BERES
                </span>
            </div>
        </div>

        <!-- Tabel Rincian Biaya -->
        <div class="border border-slate-200 rounded-2xl overflow-hidden">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase text-[11px]">
                    <tr>
                        <th class="p-3.5">Deskripsi Pekerjaan Servis</th>
                        <th class="p-3.5 text-center">Unit</th>
                        <th class="p-3.5 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="p-3.5">
                            <strong class="text-slate-900 block text-xs">{{ $order->dkm_complaint }}</strong>
                            <span class="text-slate-500 text-[11px]">Pembersihan filter, evaporator, blower indoor, condenser outdoor & pengecekan kelistrikan</span>
                        </td>
                        <td class="p-3.5 text-center font-bold text-slate-800">{{ $order->ac_count }}</td>
                        <td class="p-3.5 text-right font-bold text-slate-800">Rp {{ number_format($order->base_amount, 0, ',', '.') }}</td>
                    </tr>
                    @if($order->additional_amount > 0)
                        <tr>
                            <td class="p-3.5">
                                <strong class="text-slate-900 block text-xs">Suku Cadang / Tindakan Tambahan</strong>
                                <span class="text-amber-800 text-[11px]">{{ $order->additional_notes }}</span>
                            </td>
                            <td class="p-3.5 text-center font-bold text-slate-800">1</td>
                            <td class="p-3.5 text-right font-bold text-slate-800">Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot class="bg-slate-50 border-t border-slate-200">
                    <tr>
                        <td colspan="2" class="p-3.5 text-right font-black text-slate-900 text-sm">TOTAL DIBAYARKAN:</td>
                        <td class="p-3.5 text-right font-black text-emerald-700 text-base">Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Catatan & Tanda Tangan -->
        <div class="pt-6 border-t border-slate-100 flex items-end justify-between text-xs">
            <div class="max-w-xs text-slate-500 leading-relaxed italic">
                Terima kasih atas kepercayaannya. Semoga AC masjid senantiasa dingin, nyaman, dan menambah kekhusyukan ibadah para jamaah.
            </div>
            <div class="text-center space-y-12">
                <span class="block text-slate-500">Pengelola Servis AC</span>
                <span class="block font-bold text-slate-900 border-t border-slate-300 pt-1 px-4">Admin Operasional</span>
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
