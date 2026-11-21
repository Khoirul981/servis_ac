@extends('layouts.app', ['title' => 'Meja Operasional Admin - Servis AC Masjid'])

@section('content')
<div class="space-y-6" x-data="{
    showAssignModal: false,
    showPayModal: false,
    selectedOrder: null,
    openAssign(order) {
        this.selectedOrder = order;
        this.showAssignModal = true;
    },
    openPay(order) {
        this.selectedOrder = order;
        this.showPayModal = true;
    }
}">

    <!-- Header Meja Admin -->
    <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-1.5">
                <span>🛡️</span> MEJA PENGELOLA OPERASIONAL
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900">Pusat Kendali Pesanan Servis AC</h1>
            <p class="text-xs sm:text-sm text-slate-500">Kelola jadwal teknisi, pantau pekerjaan di masjid, dan verifikasi pelunasan kuitansi.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3.5 py-2 bg-slate-100 text-slate-700 font-bold text-xs rounded-xl border border-slate-200">
                📅 {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>

    <!-- 4 TAB STATUS OPERASIONAL -->
    <div class="bg-slate-200/70 p-1.5 rounded-2xl flex flex-wrap gap-1 border border-slate-200 text-xs sm:text-sm font-bold">
        <!-- Tab 1: Pesanan Masuk -->
        <a href="{{ route('admin.dashboard', ['tab' => 'pesanan_baru']) }}" 
           class="flex-1 min-w-[140px] py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition-all {{ $activeTab === 'pesanan_baru' ? 'bg-white text-amber-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <span>📥 Pesanan Masuk</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-black {{ $activeTab === 'pesanan_baru' ? 'bg-amber-100 text-amber-800' : 'bg-slate-300 text-slate-700' }}">
                {{ $counts['pesanan_baru'] }}
            </span>
        </a>

        <!-- Tab 2: Sedang Berjalan -->
        <a href="{{ route('admin.dashboard', ['tab' => 'berjalan']) }}" 
           class="flex-1 min-w-[140px] py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition-all {{ $activeTab === 'berjalan' ? 'bg-white text-blue-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <span>🚀 Sedang Berjalan</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-black {{ $activeTab === 'berjalan' ? 'bg-blue-100 text-blue-800' : 'bg-slate-300 text-slate-700' }}">
                {{ $counts['berjalan'] }}
            </span>
        </a>

        <!-- Tab 3: Menunggu Bayar -->
        <a href="{{ route('admin.dashboard', ['tab' => 'menunggu_bayar']) }}" 
           class="flex-1 min-w-[140px] py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition-all {{ $activeTab === 'menunggu_bayar' ? 'bg-white text-purple-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <span>💰 Menunggu Bayar</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-black {{ $activeTab === 'menunggu_bayar' ? 'bg-purple-100 text-purple-800' : 'bg-slate-300 text-slate-700' }}">
                {{ $counts['menunggu_bayar'] }}
            </span>
        </a>

        <!-- Tab 4: Riwayat Beres -->
        <a href="{{ route('admin.dashboard', ['tab' => 'selesai']) }}" 
           class="flex-1 min-w-[140px] py-2.5 px-3 rounded-xl flex items-center justify-center gap-2 transition-all {{ $activeTab === 'selesai' ? 'bg-white text-emerald-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <span>✅ Riwayat Beres</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-black {{ $activeTab === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-300 text-slate-700' }}">
                {{ $counts['selesai'] }}
            </span>
        </a>
    </div>

    <!-- KONTEN TABEL / KARTU PESANAN SESUAI TAB -->
    <div class="space-y-4">
        @forelse($orders as $order)
            <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4 hover:border-slate-300 transition-all">
                
                <!-- Baris Atas: Nomor Order & Status -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800">
                            {{ $order->order_number }}
                        </span>
                        <span class="text-xs text-slate-400">Masuk: {{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
                    </div>

                    <span class="px-3 py-1 rounded-full text-xs font-black self-start sm:self-auto
                        {{ $order->status === 'pesanan_baru' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $order->status === 'dijadwalkan' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $order->status === 'dikerjakan' ? 'bg-emerald-100 text-emerald-800 animate-pulse' : '' }}
                        {{ $order->status === 'menunggu_bayar' ? 'bg-purple-100 text-purple-800' : '' }}
                        {{ $order->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $order->status === 'batal' ? 'bg-rose-100 text-rose-800' : '' }}">
                        {{ $order->statusLabel() }}
                    </span>
                </div>

                <!-- Informasi Masjid & Detail Order -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    
                    <!-- Kolom 1: Info Masjid -->
                    <div class="space-y-1">
                        <span class="text-slate-400 font-bold block uppercase tracking-wider">Masjid & Lokasi</span>
                        <h2 class="text-base font-black text-slate-900">{{ $order->masjid->name }}</h2>
                        <p class="text-slate-600 leading-relaxed">{{ $order->masjid->address }}</p>
                        <div class="text-slate-700 pt-1">
                            <span>👤 PIC: <strong>{{ $order->masjid->pic_name }}</strong> ({{ $order->masjid->pic_phone }})</span>
                        </div>
                    </div>

                    <!-- Kolom 2: Rincian Servis -->
                    <div class="space-y-1">
                        <span class="text-slate-400 font-bold block uppercase tracking-wider">Rincian Servis AC</span>
                        <p class="text-slate-800">❄️ <strong>{{ $order->ac_count }} Unit AC</strong> &bull; {{ $order->dkm_complaint }}</p>
                        <p class="text-slate-600">🕒 Rencana Datang: <strong>{{ $order->service_time ?? '-' }}</strong> ({{ $order->service_date ? $order->service_date->translatedFormat('d M Y') : 'Fleksibel' }})</p>
                        @if($order->additional_notes)
                            <p class="text-amber-800 bg-amber-50 p-2 rounded-lg border border-amber-200 mt-1">
                                <strong>Catatan:</strong> {{ $order->additional_notes }}
                            </p>
                        @endif
                        @if($order->technician)
                            <p class="text-blue-800 font-bold pt-1">
                                🔧 Teknisi: {{ $order->technician->name }} ({{ $order->technician->phone }})
                            </p>
                        @endif
                    </div>

                    <!-- Kolom 3: Biaya & Foto -->
                    <div class="space-y-2">
                        <span class="text-slate-400 font-bold block uppercase tracking-wider">Biaya & Pembayaran</span>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-1">
                            <div class="flex justify-between text-slate-600">
                                <span>Biaya Awal:</span>
                                <span>Rp {{ number_format($order->base_amount, 0, ',', '.') }}</span>
                            </div>
                            @if($order->additional_amount > 0)
                                <div class="flex justify-between text-amber-800 font-semibold">
                                    <span>Tambahan Sparepart:</span>
                                    <span>Rp {{ number_format($order->additional_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="pt-1 border-t border-slate-200 flex justify-between font-black text-sm text-slate-900">
                                <span>Total Tagihan:</span>
                                <span class="text-emerald-700">Rp {{ number_format($order->grandTotal(), 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Galeri Mini Foto Bukti -->
                        @if($order->photos->isNotEmpty())
                            <div class="flex items-center gap-1.5 pt-1 overflow-x-auto">
                                @foreach($order->photos as $photo)
                                    <a href="{{ $photo->url }}" target="_blank" class="shrink-0 rounded-lg overflow-hidden border border-slate-200 w-12 h-12 block group">
                                        <img src="{{ $photo->url }}" alt="Bukti" class="w-full h-full object-cover group-hover:opacity-80">
                                    </a>
                                @endforeach
                                <span class="text-[11px] text-slate-500 font-bold ml-1">({{ $order->photos->count() }} Foto)</span>
                            </div>
                        @endif
                    </div>

                </div>

                <!-- Baris Aksi Sesuai Status -->
                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-end gap-2">
                    
                    <!-- Aksi Tab 1: Pesanan Baru -> Tugaskan Teknisi -->
                    @if($order->status === 'pesanan_baru')
                        <button type="button" 
                                @click="openAssign({{ Js::from($order) }})"
                                class="min-h-[44px] px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs flex items-center gap-1.5 cursor-pointer">
                            <span>👨‍🔧</span>
                            <span>Tugaskan Teknisi & Jadwalkan</span>
                        </button>

                        <form method="POST" action="{{ route('admin.order.cancel', $order->id) }}" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?');" class="inline">
                            @csrf
                            <button type="submit" class="min-h-[44px] px-3.5 py-2 text-rose-700 hover:bg-rose-50 rounded-xl text-xs font-bold transition-colors cursor-pointer border border-rose-200">
                                Batalkan
                            </button>
                        </form>
                    @endif

                    <!-- Aksi Tab 2: Sedang Berjalan -> Re-assign atau Pantau -->
                    @if(in_array($order->status, ['dijadwalkan', 'dikerjakan']))
                        <button type="button" 
                                @click="openAssign({{ Js::from($order) }})"
                                class="min-h-[44px] px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl border border-slate-300 flex items-center gap-1.5 cursor-pointer">
                            <span>🔄</span>
                            <span>Ubah Teknisi / Jadwal</span>
                        </button>
                    @endif

                    <!-- Aksi Tab 3: Menunggu Bayar -> Verifikasi Lunas -->
                    @if($order->status === 'menunggu_bayar')
                        <button type="button" 
                                @click="openPay({{ Js::from($order) }})"
                                class="min-h-[46px] px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-xl shadow-xs flex items-center gap-1.5 cursor-pointer">
                            <span>💰</span>
                            <span>Verifikasi Pelunasan & Terbitkan Kuitansi</span>
                        </button>
                    @endif

                    <!-- Aksi Tab 4: Riwayat Beres -> Kuitansi & WhatsApp -->
                    @if($order->status === 'selesai')
                        <a href="{{ route('admin.order.receipt', $order->id) }}" 
                           class="min-h-[44px] px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl flex items-center gap-1.5 text-decoration-none">
                            <span>🧾</span>
                            <span>Buka Lembar Kuitansi</span>
                        </a>
                    @endif

                </div>

            </div>
        @empty
            <div class="bg-white rounded-3xl border border-dashed border-slate-300 p-10 text-center text-slate-500 space-y-2">
                <span class="text-4xl block">📭</span>
                <p class="font-bold text-slate-800 text-base">Tidak ada pesanan pada tab ini!</p>
                <p class="text-xs text-slate-500">Semua pesanan telah diproses sesuai status alur kerjanya.</p>
            </div>
        @endforelse

        <!-- Paginasi -->
        <div>
            {{ $orders->links() }}
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL PENUGASAN TEKNISI -->
    <!-- ========================================================================= -->
    <div x-show="showAssignModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="showAssignModal = false" 
             class="bg-white rounded-3xl max-w-md w-full p-6 shadow-xl space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <span>👨‍🔧</span>
                    <span>Tugaskan Teknisi Servis</span>
                </h3>
                <button type="button" @click="showAssignModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <template x-if="selectedOrder">
                <form :action="'/admin/order/' + selectedOrder.id + '/assign'" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                        <span class="text-slate-500 block">Masjid:</span>
                        <strong class="text-slate-900 text-sm block" x-text="selectedOrder.masjid.name"></strong>
                        <span class="text-slate-600 block mt-0.5" x-text="selectedOrder.ac_count + ' Unit AC (' + selectedOrder.dkm_complaint + ')'"></span>
                    </div>

                    <!-- Pilih Teknisi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">Pilih Teknisi Lapangan:</label>
                        <select name="technician_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                            <option value="">-- Pilih Teknisi --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->phone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal Servis -->
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">Tanggal Kedatangan:</label>
                        <input type="date" name="service_date" required 
                               :value="selectedOrder.service_date ? selectedOrder.service_date.split('T')[0] : '{{ now()->toDateString() }}'"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Jam Servis -->
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">Jam Kedatangan:</label>
                        <select name="service_time" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                            <option value="Pagi (08:30 - 11:30 WIB)">Pagi (08:30 - 11:30 WIB)</option>
                            <option value="Siang Ba'da Dzuhur (13:00 - 15:00 WIB)">Siang Ba'da Dzuhur (13:00 - 15:00 WIB)</option>
                            <option value="Sore Ba'da Ashar (16:00 - 17:30 WIB)">Sore Ba'da Ashar (16:00 - 17:30 WIB)</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showAssignModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-black text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl cursor-pointer shadow-xs">Simpan Penugasan</button>
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
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="showPayModal = false" 
             class="bg-white rounded-3xl max-w-md w-full p-6 shadow-xl space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <span>💰</span>
                    <span>Verifikasi Uang Masuk</span>
                </h3>
                <button type="button" @click="showPayModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <template x-if="selectedOrder">
                <form :action="'/admin/order/' + selectedOrder.id + '/pay'" method="POST" class="space-y-4">
                    @csrf

                    <div class="p-4 bg-purple-50 rounded-2xl border border-purple-200 text-xs space-y-1.5">
                        <span class="text-purple-700 font-bold block" x-text="selectedOrder.masjid.name"></span>
                        <div class="flex justify-between text-slate-700">
                            <span>Biaya Servis:</span>
                            <span x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedOrder.base_amount)"></span>
                        </div>
                        <template x-if="selectedOrder.additional_amount > 0">
                            <div class="flex justify-between text-amber-800 font-semibold">
                                <span>Sparepart:</span>
                                <span x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedOrder.additional_amount)"></span>
                            </div>
                        </template>
                        <div class="pt-2 border-t border-purple-200 flex justify-between font-black text-sm text-purple-900">
                            <span>Total Pelunasan:</span>
                            <span x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(parseFloat(selectedOrder.base_amount) + parseFloat(selectedOrder.additional_amount))"></span>
                        </div>
                    </div>

                    <!-- Pilih Metode Bayar -->
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">Metode Pembayaran:</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="border rounded-xl p-3 text-center cursor-pointer text-xs font-bold">
                                <input type="radio" name="payment_method" value="transfer" checked class="mb-1 block mx-auto">
                                <span>Transfer</span>
                            </label>
                            <label class="border rounded-xl p-3 text-center cursor-pointer text-xs font-bold">
                                <input type="radio" name="payment_method" value="qris" class="mb-1 block mx-auto">
                                <span>QRIS</span>
                            </label>
                            <label class="border rounded-xl p-3 text-center cursor-pointer text-xs font-bold">
                                <input type="radio" name="payment_method" value="cash" class="mb-1 block mx-auto">
                                <span>Tunai</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showPayModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-xs font-black text-white bg-purple-600 hover:bg-purple-700 rounded-xl cursor-pointer shadow-xs">Konfirmasi Lunas & Terbitkan Kuitansi</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
