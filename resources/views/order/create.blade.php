@extends('layouts.app', ['title' => 'Formulir Pemesanan Servis AC Masjid & Musholla'])

@section('content')
<div class="max-w-2xl mx-auto py-2 sm:py-6" 
     x-data="{
        isNewMasjid: '0',
        selectedMasjidId: '{{ old('masjid_id', '') }}',
        masjids: {{ Js::from($masjids) }},
        masjidQuery: '',
        filteredMasjids: [],
        showDropdown: false,
        address: '{{ old('address', '') }}',
        picName: '{{ old('pic_name', '') }}',
        picPhone: '{{ old('pic_phone', '') }}',
        complaint: '{{ old('dkm_complaint', 'Cuci Rutin') }}',
        acCount: {{ old('ac_count', 1) }},
        rates: {
            'Cuci Rutin': 75000,
            'Tidak Dingin / Bocor Air': 85000,
            'Mati Total / Rusak': 100000
        },
        get currentRate() {
            return this.rates[this.complaint] || 75000;
        },
        get estimatedTotal() {
            return this.currentRate * this.acCount;
        },
        formatRupiah(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        },
        selectMasjid(item) {
            this.selectedMasjidId = item.id;
            this.masjidQuery = item.name;
            this.address = item.address;
            this.picName = item.pic_name;
            this.picPhone = item.pic_phone;
            this.showDropdown = false;
        },
        searchMasjid() {
            if (!this.masjidQuery) {
                this.filteredMasjids = this.masjids;
            } else {
                let q = this.masjidQuery.toLowerCase();
                this.filteredMasjids = this.masjids.filter(m => 
                    m.name.toLowerCase().includes(q) || m.address.toLowerCase().includes(q)
                );
            }
        },
        increment() {
            if (this.acCount < 50) this.acCount++;
        },
        decrement() {
            if (this.acCount > 1) this.acCount--;
        }
     }"
     x-init="
        filteredMasjids = masjids;
        @if(old('masjid_id'))
            let found = masjids.find(m => m.id == {{ old('masjid_id') }});
            if (found) { selectMasjid(found); }
        @endif
     ">

    <!-- Header Form -->
    <div class="text-center mb-6">
        <div class="w-16 h-16 bg-emerald-600 text-white rounded-2xl flex items-center justify-center text-3xl mx-auto mb-3 shadow-md">
            ❄️
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Pemesanan Servis AC Masjid
        </h1>
        <p class="text-base text-slate-600 mt-1.5 max-w-lg mx-auto leading-relaxed">
            Formulir praktis tanpa ribet untuk Pengurus DKM & Marbot Musholla. Teknisi siap datang merawat AC rumah ibadah.
        </p>
    </div>

    <!-- Kotak Formulir -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 sm:p-8">
        <form method="POST" action="{{ route('order.store') }}" class="space-y-6">
            @csrf

            <!-- Kolom Jebakan Honeypot untuk Bot Spam -->
            <div style="display:none;" aria-hidden="true">
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            <input type="hidden" name="is_new_masjid" :value="isNewMasjid">
            <input type="hidden" name="masjid_id" :value="isNewMasjid === '0' ? selectedMasjidId : ''">

            <!-- BAGIAN 1: DATA MASJID -->
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 text-sm font-bold flex items-center justify-center">1</span>
                        <span>Lokasi Masjid / Musholla</span>
                    </h2>

                    <!-- Pilihan Masjid Baru / Terdaftar -->
                    <div class="flex bg-slate-100 p-1 rounded-xl">
                        <button type="button" 
                                @click="isNewMasjid = '0'" 
                                :class="isNewMasjid === '0' ? 'bg-white text-emerald-800 shadow-xs font-bold' : 'text-slate-600'"
                                class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
                            Cari Terdaftar
                        </button>
                        <button type="button" 
                                @click="isNewMasjid = '1'; selectedMasjidId = ''; masjidQuery = '';" 
                                :class="isNewMasjid === '1' ? 'bg-white text-emerald-800 shadow-xs font-bold' : 'text-slate-600'"
                                class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer">
                            + Masjid Baru
                        </button>
                    </div>
                </div>

                <!-- Input Pencarian Masjid Lama -->
                <div x-show="isNewMasjid === '0'" class="relative">
                    <label class="block text-sm font-bold text-slate-900 mb-1.5">
                        Cari Nama Masjid / Musholla
                    </label>
                    <input type="text" 
                           x-model="masjidQuery" 
                           @focus="showDropdown = true; searchMasjid()"
                           @input="searchMasjid(); showDropdown = true"
                           placeholder="Ketik nama masjid (contoh: Al-Muhajirin)" 
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 text-base text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    
                    <!-- Dropdown Hasil Autocomplete -->
                    <div x-show="showDropdown && filteredMasjids.length > 0" 
                         @click.away="showDropdown = false"
                         class="absolute z-30 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                        <template x-for="item in filteredMasjids" :key="item.id">
                            <button type="button" 
                                    @click="selectMasjid(item)"
                                    class="w-full text-left px-4 py-2.5 hover:bg-emerald-50 border-b border-slate-100 last:border-0 transition-colors cursor-pointer">
                                <span class="block text-sm font-bold text-slate-900" x-text="item.name"></span>
                                <span class="block text-xs text-slate-500 truncate" x-text="item.address"></span>
                            </button>
                        </template>
                    </div>

                    @error('masjid_id')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Input Nama Masjid Baru -->
                <div x-show="isNewMasjid === '1'">
                    <label for="new_masjid_name" class="block text-sm font-bold text-slate-900 mb-1.5">
                        Nama Masjid / Musholla Baru
                    </label>
                    <input type="text" 
                           name="new_masjid_name" 
                           id="new_masjid_name"
                           value="{{ old('new_masjid_name') }}"
                           placeholder="Contoh: Masjid Nurul Iman" 
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 text-base text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    @error('new_masjid_name')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat Lengkap -->
                <div>
                    <label for="address" class="block text-sm font-bold text-slate-900 mb-1.5">
                        Alamat Lengkap Masjid
                    </label>
                    <textarea name="address" 
                              id="address" 
                              rows="2" 
                              x-model="address"
                              required
                              placeholder="Jl. Nama Jalan No. XX, Kelurahan, Kecamatan, Kota (cantumkan patokan jika ada)" 
                              class="w-full px-4 py-3 rounded-xl border border-slate-300 text-base text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"></textarea>
                    @error('address')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- BAGIAN 2: KONTAK PENGURUS (DKM / MARBOT) -->
            <div class="space-y-4 pt-2">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 text-sm font-bold flex items-center justify-center">2</span>
                    <span>Kontak Pengurus (DKM / Marbot)</span>
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="pic_name" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Nama Pengurus / Pemesan
                        </label>
                        <input type="text" 
                               name="pic_name" 
                               id="pic_name" 
                               x-model="picName"
                               required
                               placeholder="Contoh: Pak Haji Bambang" 
                               class="w-full px-4 py-3 rounded-xl border border-slate-300 text-base text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none">
                        @error('pic_name')
                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="pic_phone" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Nomor WhatsApp Aktif
                        </label>
                        <input type="tel" 
                               name="pic_phone" 
                               id="pic_phone" 
                               x-model="picPhone"
                               required
                               placeholder="08xxxxxxxxxx" 
                               class="w-full px-4 py-3 rounded-xl border border-slate-300 text-base text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none font-mono">
                        @error('pic_phone')
                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- BAGIAN 3: KELUHAN & JUMLAH AC -->
            <div class="space-y-4 pt-2">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-800 text-sm font-bold flex items-center justify-center">3</span>
                    <span>Pilihan Servis & Jumlah AC</span>
                </h2>

                <input type="hidden" name="dkm_complaint" :value="complaint">
                <input type="hidden" name="ac_count" :value="acCount">

                <!-- 3 Tombol Sentuh Besar untuk Keluhan -->
                <div>
                    <label class="block text-sm font-bold text-slate-900 mb-2">
                        Pilih Kategori Servis AC:
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Cuci Rutin -->
                        <button type="button" 
                                @click="complaint = 'Cuci Rutin'"
                                :class="complaint === 'Cuci Rutin' ? 'border-emerald-600 bg-emerald-50/70 ring-2 ring-emerald-500' : 'border-slate-200 bg-white hover:border-slate-300'"
                                class="p-4 rounded-2xl border text-left transition-all cursor-pointer">
                            <span class="text-2xl block mb-1">❄️</span>
                            <span class="block text-base font-bold text-slate-900">Cuci Rutin</span>
                            <span class="block text-xs text-emerald-700 font-semibold mt-1">Rp 75.000 / unit</span>
                        </button>

                        <!-- Tidak Dingin / Bocor -->
                        <button type="button" 
                                @click="complaint = 'Tidak Dingin / Bocor Air'"
                                :class="complaint === 'Tidak Dingin / Bocor Air' ? 'border-emerald-600 bg-emerald-50/70 ring-2 ring-emerald-500' : 'border-slate-200 bg-white hover:border-slate-300'"
                                class="p-4 rounded-2xl border text-left transition-all cursor-pointer">
                            <span class="text-2xl block mb-1">💧</span>
                            <span class="block text-base font-bold text-slate-900">Kurang Dingin / Bocor</span>
                            <span class="block text-xs text-emerald-700 font-semibold mt-1">Rp 85.000 / unit</span>
                        </button>

                        <!-- Mati Total -->
                        <button type="button" 
                                @click="complaint = 'Mati Total / Rusak'"
                                :class="complaint === 'Mati Total / Rusak' ? 'border-emerald-600 bg-emerald-50/70 ring-2 ring-emerald-500' : 'border-slate-200 bg-white hover:border-slate-300'"
                                class="p-4 rounded-2xl border text-left transition-all cursor-pointer">
                            <span class="text-2xl block mb-1">⚠️</span>
                            <span class="block text-base font-bold text-slate-900">Mati Total / Rusak</span>
                            <span class="block text-xs text-emerald-700 font-semibold mt-1">Rp 100.000 (Pengecekan)</span>
                        </button>
                    </div>
                </div>

                <!-- Counter Jumlah AC Ramah Sentuh -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <span class="block text-base font-bold text-slate-900">Berapa Unit AC yang Mau Diservis?</span>
                        <span class="block text-xs text-slate-500">Tekan tombol plus (+) atau minus (-) di samping</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" 
                                @click="decrement()"
                                :disabled="acCount <= 1"
                                class="w-12 h-12 rounded-xl bg-white border border-slate-300 text-slate-800 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed font-black text-2xl flex items-center justify-center shadow-xs cursor-pointer">
                            &minus;
                        </button>

                        <div class="w-16 h-12 bg-white border border-emerald-500 rounded-xl flex items-center justify-center font-black text-xl text-emerald-900 shadow-inner">
                            <span x-text="acCount"></span>
                        </div>

                        <button type="button" 
                                @click="increment()"
                                class="w-12 h-12 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 font-black text-2xl flex items-center justify-center shadow-sm cursor-pointer">
                            +
                        </button>
                    </div>
                </div>

                <!-- Jadwal Kedatangan Pilihan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="service_date" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Rencana Hari Servis (Opsional)
                        </label>
                        <input type="date" 
                               name="service_date" 
                               id="service_date"
                               value="{{ old('service_date', now()->addDay()->toDateString()) }}"
                               min="{{ now()->toDateString() }}"
                               class="w-full px-4 py-3 rounded-xl border border-slate-300 text-base text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div>
                        <label for="service_time" class="block text-sm font-bold text-slate-900 mb-1.5">
                            Pilihan Waktu Datang
                        </label>
                        <select name="service_time" 
                                id="service_time"
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 text-base text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                            <option value="Pagi (08:30 - 11:30 WIB)">Pagi (08:30 - 11:30 WIB)</option>
                            <option value="Siang Ba'da Dzuhur (13:00 - 15:00 WIB)">Siang Ba'da Dzuhur (13:00 - 15:00 WIB)</option>
                            <option value="Sore Ba'da Ashar (16:00 - 17:30 WIB)">Sore Ba'da Ashar (16:00 - 17:30 WIB)</option>
                            <option value="Kapan Saja / Fleksibel">Kapan Saja / Fleksibel</option>
                        </select>
                    </div>
                </div>

                <!-- Catatan Tambahan -->
                <div>
                    <label for="notes" class="block text-sm font-bold text-slate-900 mb-1.5">
                        Catatan Tambahan (Opsional)
                    </label>
                    <input type="text" 
                           name="notes" 
                           id="notes"
                           value="{{ old('notes') }}"
                           placeholder="Contoh: AC ruang utama 2 unit di atas mimbar, butuh tangga tinggi" 
                           class="w-full px-4 py-3 rounded-xl border border-slate-300 text-base text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
            </div>

            <!-- KOTAK ESTIMASI BIAYA INTERAKTIF -->
            <div class="bg-emerald-900 text-white rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="block text-xs uppercase tracking-wider text-emerald-300 font-semibold">Perkiraan Biaya Awal</span>
                        <div class="text-2xl sm:text-3xl font-black mt-0.5">
                            Rp <span x-text="formatRupiah(estimatedTotal)"></span>
                        </div>
                    </div>
                    <div class="text-right text-xs text-emerald-200">
                        <span class="block font-semibold" x-text="acCount + ' Unit &bull; ' + complaint"></span>
                        <span class="block text-[11px] opacity-80">*Pelunasan setelah servis beres</span>
                    </div>
                </div>
            </div>

            <!-- TOMBOL SUBMIT BESAR (Minimal 48px) -->
            <button type="submit" 
                    class="w-full min-h-[52px] py-3.5 px-6 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black text-lg rounded-2xl shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                <span>🚀</span>
                <span>Kirim Pesanan Servis Sekarang</span>
            </button>
        </form>
    </div>

    <!-- Informasi Bantuan -->
    <div class="mt-6 text-center text-xs text-slate-500">
        Butuh bantuan darurat atau konsultasi langsung? Hubungi Admin via WhatsApp di nomor yang tertera di halaman berikutnya.
    </div>

</div>
@endsection
