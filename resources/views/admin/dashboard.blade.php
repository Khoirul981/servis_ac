@extends('layouts.app', ['title' => 'Meja Operasional Admin - Servis AC Masjid'])

@section('content')
<div class="space-y-6" x-data="{
    showAssignModal: false,
    showPayModal: false,
    showPhotosModal: false,
    selectedOrder: null,
    activePhotos: [],
    activeOrderNumber: '',
    openAssign(order) {
        this.selectedOrder = order;
        this.showAssignModal = true;
    },
    openPay(order) {
        this.selectedOrder = order;
        this.showPayModal = true;
    },
    openPhotos(photos, orderNumber) {
        this.activePhotos = photos;
        this.activeOrderNumber = orderNumber;
        this.showPhotosModal = true;
    }
}">

    <!-- Header Meja Admin Bersih Sesuai Panduan Section 5.3 -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Meja Kerja Operasional Servis AC</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Penjadwalan teknisi, pemantauan pekerjaan di masjid, dan verifikasi pelunasan.</p>
        </div>
        <div class="flex items-center gap-3 text-xs">
            <span class="px-3 py-1.5 bg-[#EBF2EE] text-[#1E3E34] font-semibold rounded-md border border-[#1E3E34]/20">
                {{ now()->translatedFormat('l, d F Y') }}
            </span>
            <span class="text-gray-500 font-medium">Petugas: <strong>{{ Auth::user()->name }}</strong></span>
        </div>
    </div>

    <!-- BILAH TAB STATUS HORIZONTAL RAPI (Sesuai Panduan Section 5.3) -->
    <div class="bg-white border border-gray-200 rounded-xl p-1.5 flex flex-wrap gap-1 text-xs sm:text-sm font-medium shadow-xs">
        <!-- Tab 1: Pesanan Baru -->
        <a href="{{ route('admin.dashboard', ['tab' => 'pesanan_baru']) }}" 
           class="flex-1 min-w-[130px] py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition-all {{ $activeTab === 'pesanan_baru' ? 'bg-[#1E3E34] text-white font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
            <span>Pesanan Baru</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $activeTab === 'pesanan_baru' ? 'bg-white text-[#1E3E34]' : 'bg-[#FEF3C7] text-[#92400E]' }}">
                {{ $counts['pesanan_baru'] }}
            </span>
        </a>

        <!-- Tab 2: Sedang Berjalan -->
        <a href="{{ route('admin.dashboard', ['tab' => 'berjalan']) }}" 
           class="flex-1 min-w-[130px] py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition-all {{ $activeTab === 'berjalan' ? 'bg-[#1E3E34] text-white font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
            <span>Sedang Berjalan</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $activeTab === 'berjalan' ? 'bg-white text-[#1E3E34]' : 'bg-[#E0F2FE] text-[#0369A1]' }}">
                {{ $counts['berjalan'] }}
            </span>
        </a>

        <!-- Tab 3: Menunggu Bayar -->
        <a href="{{ route('admin.dashboard', ['tab' => 'menunggu_bayar']) }}" 
           class="flex-1 min-w-[130px] py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition-all {{ $activeTab === 'menunggu_bayar' ? 'bg-[#1E3E34] text-white font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
            <span>Menunggu Pembayaran</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $activeTab === 'menunggu_bayar' ? 'bg-white text-[#1E3E34]' : 'bg-[#FFEDD5] text-[#C2410C]' }}">
                {{ $counts['menunggu_bayar'] }}
            </span>
        </a>

        <!-- Tab 4: Riwayat Beres -->
        <a href="{{ route('admin.dashboard', ['tab' => 'selesai']) }}" 
           class="flex-1 min-w-[130px] py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition-all {{ $activeTab === 'selesai' ? 'bg-[#1E3E34] text-white font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
            <span>Selesai & Lunas</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $activeTab === 'selesai' ? 'bg-white text-[#1E3E34]' : 'bg-[#DCFCE7] text-[#15803D]' }}">
                {{ $counts['selesai'] }}
            </span>
        </a>
    </div>

    <!-- TABEL DATA RAPI DENGAN COMFORTABLE PADDING 14PX (Section 5.3) -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-700 font-semibold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">No. Order & Waktu</th>
                        <th class="py-3.5 px-4">Masjid & Alamat</th>
                        <th class="py-3.5 px-4">Layanan & Keluhan</th>
                        <th class="py-3.5 px-4">Teknisi Bertugas</th>
                        <th class="py-3.5 px-4 text-right">Total Biaya</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            
                            <!-- No. Order -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-gray-900 block">{{ $order->order_number }}</span>
                                <span class="text-gray-500 text-[11px]">{{ $order->created_at->translatedFormat('d M, H:i') }}</span>
                            </td>

                            <!-- Masjid & Kontak -->
                            <td class="py-3.5 px-4 max-w-[220px]">
                                <strong class="text-sm font-semibold text-gray-900 block truncate">{{ $order->masjid->name }}</strong>
                                <span class="text-gray-500 text-[11px] block truncate">{{ $order->masjid->address }}</span>
                                <span class="text-gray-600 text-[11px] block mt-0.5">{{ $order->masjid->pic_name }} ({{ $order->masjid->pic_phone }})</span>
                            </td>

                            <!-- Layanan & AC -->
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-gray-900 block">{{ $order->dkm_complaint }}</span>
                                <span class="text-gray-500 text-[11px]">{{ $order->ac_count }} Unit AC &bull; Jadwal: {{ $order->service_time ?? '-' }}</span>
                                @if($order->additional_amount > 0)
                                    <span class="block text-[11px] text-amber-800 font-medium mt-0.5">
                                        + Sparepart: {{ $order->additional_notes }}
                                    </span>
                                @endif
                                @if($order->photos->isNotEmpty())
                                    <div class="mt-1">
                                        <button type="button" 
                                                @click="openPhotos({{ Js::from($order->photos) }}, '{{ $order->order_number }}')"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-[11px] cursor-pointer">
                                            {{ $order->photos->count() }} Foto Bukti
                                        </button>
                                    </div>
                                @endif
                            </td>

                            <!-- Teknisi -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($order->technician)
                                    <span class="font-semibold text-gray-900 block">{{ $order->technician->name }}</span>
                                    <span class="text-gray-500 text-[11px] font-mono">{{ $order->technician->phone }}</span>
                                @else
                                    <span class="text-amber-800 font-medium text-xs">Belum ditugaskan</span>
                                @endif
                            </td>

                            <!-- Total Biaya -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <span class="font-bold text-sm text-gray-900 block">
                                    Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}
                                </span>
                                @if($order->additional_amount > 0)
                                    <span class="text-[11px] text-gray-500">Dasar: {{ number_format($order->base_amount, 0, ',', '.') }}</span>
                                @endif
                            </td>

                            <!-- Status Badge Bersih Sesuai Palet Warna Section 2.3 -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-block px-2.5 py-1 rounded-md text-xs font-semibold
                                    {{ $order->status === 'pesanan_baru' ? 'bg-[#FEF3C7] text-[#92400E] border border-[#FCD34D]' : '' }}
                                    {{ in_array($order->status, ['dijadwalkan', 'dikerjakan']) ? 'bg-[#E0F2FE] text-[#0369A1] border border-[#BAE6FD]' : '' }}
                                    {{ $order->status === 'menunggu_bayar' ? 'bg-[#FFEDD5] text-[#C2410C] border border-[#FDBA74]' : '' }}
                                    {{ $order->status === 'selesai' ? 'bg-[#DCFCE7] text-[#15803D] border border-[#86EFAC]' : '' }}
                                    {{ $order->status === 'batal' ? 'bg-[#FEE2E2] text-[#B91C1C] border border-[#FCA5A5]' : '' }}">
                                    {{ $order->statusLabel() }}
                                </span>
                            </td>

                            <!-- Tombol Aksi Teks Langsung (Section 5.3) -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($order->status === 'pesanan_baru')
                                        <button type="button" 
                                                @click="openAssign({{ Js::from($order) }})"
                                                class="px-3 py-1.5 rounded-md bg-[#1E3E34] hover:bg-[#142B24] text-white font-medium text-xs transition-colors cursor-pointer">
                                            Tugaskan
                                        </button>
                                        <form method="POST" action="{{ route('admin.order.cancel', $order->id) }}" onsubmit="return confirm('Batalkan pesanan ini?');" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-1.5 text-gray-500 hover:text-red-700 text-xs font-medium cursor-pointer">
                                                Batal
                                            </button>
                                        </form>
                                    @elseif(in_array($order->status, ['dijadwalkan', 'dikerjakan']))
                                        <button type="button" 
                                                @click="openAssign({{ Js::from($order) }})"
                                                class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-100 text-gray-800 font-medium text-xs transition-colors cursor-pointer">
                                            Ubah Jadwal
                                        </button>
                                    @elseif($order->status === 'menunggu_bayar')
                                        <button type="button" 
                                                @click="openPay({{ Js::from($order) }})"
                                                class="px-3.5 py-1.5 rounded-md bg-[#1E3E34] hover:bg-[#142B24] text-white font-semibold text-xs transition-colors cursor-pointer">
                                            Verifikasi Pelunasan
                                        </button>
                                    @elseif($order->status === 'selesai')
                                        <a href="{{ route('admin.order.receipt', $order->id) }}" 
                                           class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-100 text-gray-800 font-medium text-xs transition-colors inline-block text-decoration-none">
                                            Kuitansi
                                        </a>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-400 text-xs">
                                Tidak ada data pesanan pada tab ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginasi -->
    <div>
        {{ $orders->links() }}
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL PENUGASAN TEKNISI -->
    <!-- ========================================================================= -->
    <div x-show="showAssignModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div @click.away="showAssignModal = false" 
             class="bg-white rounded-xl border border-gray-200 max-w-md w-full p-6 shadow-xl space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">
                    Penugasan Teknisi & Jadwal Servis
                </h3>
                <button type="button" @click="showAssignModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold cursor-pointer">&times;</button>
            </div>

            <template x-if="selectedOrder">
                <form :action="'/admin/order/' + selectedOrder.id + '/assign'" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-xs">
                        <span class="text-gray-500 block">Masjid:</span>
                        <strong class="text-gray-900 text-sm block" x-text="selectedOrder.masjid.name"></strong>
                        <span class="text-gray-600 block mt-0.5" x-text="selectedOrder.ac_count + ' Unit AC &bull; ' + selectedOrder.dkm_complaint"></span>
                    </div>

                    <!-- Pilih Teknisi -->
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-gray-800">Pilih Teknisi Lapangan:</label>
                        <select name="technician_id" required class="w-full min-h-[44px] px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                            <option value="">-- Pilih Teknisi --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->phone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal Servis -->
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-gray-800">Tanggal Kedatangan:</label>
                        <input type="date" name="service_date" required 
                               :value="selectedOrder.service_date ? selectedOrder.service_date.split('T')[0] : '{{ now()->toDateString() }}'"
                               class="w-full min-h-[44px] px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34]">
                    </div>

                    <!-- Jam Servis -->
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-gray-800">Jam Kedatangan:</label>
                        <select name="service_time" required class="w-full min-h-[44px] px-3.5 py-2.5 rounded-lg border border-gray-300 text-xs text-gray-900 outline-none focus:border-[#1E3E34] bg-white">
                            <option value="Pagi (08:30 - 11:30 WIB)">Pagi (08:30 - 11:30 WIB)</option>
                            <option value="Siang Ba'da Dzuhur (13:00 - 15:00 WIB)">Siang Ba'da Dzuhur (13:00 - 15:00 WIB)</option>
                            <option value="Sore Ba'da Ashar (16:00 - 17:30 WIB)">Sore Ba'da Ashar (16:00 - 17:30 WIB)</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showAssignModal = false" class="px-4 py-2.5 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-lg cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#1E3E34] hover:bg-[#142B24] rounded-lg cursor-pointer transition-colors">Simpan Penugasan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL VERIFIKASI PEMBAYARAN -->
    <!-- ========================================================================= -->
    <div x-show="showPayModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div @click.away="showPayModal = false" 
             class="bg-white rounded-xl border border-gray-200 max-w-md w-full p-6 shadow-xl space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">
                    Verifikasi Pelunasan Pembayaran
                </h3>
                <button type="button" @click="showPayModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold cursor-pointer">&times;</button>
            </div>

            <template x-if="selectedOrder">
                <form :action="'/admin/order/' + selectedOrder.id + '/pay'" method="POST" class="space-y-4">
                    @csrf

                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 text-xs space-y-1.5">
                        <span class="text-gray-900 font-bold block" x-text="selectedOrder.masjid.name"></span>
                        <div class="flex justify-between text-gray-600">
                            <span>Biaya Servis Dasar:</span>
                            <span x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedOrder.base_amount)"></span>
                        </div>
                        <template x-if="selectedOrder.additional_amount > 0">
                            <div class="flex justify-between text-gray-800 font-medium">
                                <span>Biaya Sparepart:</span>
                                <span x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedOrder.additional_amount)"></span>
                            </div>
                        </template>
                        <div class="pt-2 border-t border-gray-200 flex justify-between font-bold text-sm text-gray-900">
                            <span>Total Pembayaran:</span>
                            <span class="text-[#1E3E34]" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(parseFloat(selectedOrder.base_amount) + parseFloat(selectedOrder.additional_amount))"></span>
                        </div>
                    </div>

                    <!-- Pilih Metode Bayar -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-gray-800">Metode Pembayaran:</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="border border-gray-300 rounded-lg p-2.5 text-center cursor-pointer text-xs font-medium hover:border-[#1E3E34]">
                                <input type="radio" name="payment_method" value="transfer" checked class="mb-1 block mx-auto">
                                <span>Transfer</span>
                            </label>
                            <label class="border border-gray-300 rounded-lg p-2.5 text-center cursor-pointer text-xs font-medium hover:border-[#1E3E34]">
                                <input type="radio" name="payment_method" value="qris" class="mb-1 block mx-auto">
                                <span>QRIS</span>
                            </label>
                            <label class="border border-gray-300 rounded-lg p-2.5 text-center cursor-pointer text-xs font-medium hover:border-[#1E3E34]">
                                <input type="radio" name="payment_method" value="cash" class="mb-1 block mx-auto">
                                <span>Tunai</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showPayModal = false" class="px-4 py-2.5 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-lg cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#1E3E34] hover:bg-[#142B24] rounded-lg cursor-pointer transition-colors">Verifikasi Lunas & Buat Kuitansi</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL LIHAT FOTO BUKTI SERVIS OLEH ADMIN -->
    <!-- ========================================================================= -->
    <div x-show="showPhotosModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div @click.away="showPhotosModal = false" 
             class="bg-white rounded-xl border border-gray-200 max-w-2xl w-full p-6 shadow-xl space-y-4 max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">
                        Foto Bukti Servis Lapangan
                    </h3>
                    <p class="text-xs text-gray-500" x-text="'Nomor Pesanan: ' + activeOrderNumber"></p>
                </div>
                <button type="button" @click="showPhotosModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold cursor-pointer">&times;</button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <template x-for="photo in activePhotos" :key="photo.id">
                    <div class="rounded-xl border border-gray-200 overflow-hidden bg-white shadow-xs flex flex-col justify-between">
                        <div class="relative bg-gray-100">
                            <img :src="photo.url || ('/storage/' + photo.photo_url)" alt="Bukti Servis" class="w-full h-40 object-cover">
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider shadow-sm"
                                  :class="photo.photo_type === 'sebelum' ? 'bg-amber-100 text-amber-800 border border-amber-300' : (photo.photo_type === 'sesudah' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-blue-100 text-blue-800 border border-blue-300')"
                                  x-text="photo.photo_type"></span>
                        </div>
                        <div class="p-3 text-xs space-y-2">
                            <p class="text-gray-800 font-medium" x-text="photo.caption || 'Tanpa keterangan'"></p>
                            <div class="flex items-center justify-end pt-1.5 border-t border-gray-100">
                                <form :action="'/admin/order/foto/' + photo.id" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto bukti servis ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded border border-red-200 text-red-600 hover:bg-red-50 font-medium text-xs cursor-pointer">
                                        Hapus Foto
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex justify-end pt-3 border-t border-gray-100">
                <button type="button" @click="showPhotosModal = false" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
