@extends('layouts.app', ['title' => 'Formulir Pemesanan Servis AC Masjid & Musholla'])

@section('content')
<div class="max-w-[520px] mx-auto py-2 sm:py-6" 
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

    <!-- Header Formulir -->
    <div class="text-center mb-6">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
            Pemesanan Servis AC Masjid
        </h1>
        <p class="text-sm text-gray-600 mt-1 leading-relaxed">
            Formulir permohonan perawatan pendingin ruang ibadah untuk Pengurus DKM dan Marbot.
        </p>
    </div>

    <!-- Kartu Formulir Solid (Tanpa Glassmorphism) -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 sm:p-7 shadow-sm">
        <form method="POST" action="{{ route('order.store') }}" class="space-y-6">
            @csrf

            <!-- Kolom Jebakan Honeypot untuk Bot Spam -->
            <div style="display:none;" aria-hidden="true">
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>

            <input type="hidden" name="is_new_masjid" :value="isNewMasjid">
            <input type="hidden" name="masjid_id" :value="isNewMasjid === '0' ? selectedMasjidId : ''">

            <!-- BAGIAN 1: LOKASI MASJID -->
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">
                        1. Lokasi Masjid atau Musholla
                    </h2>

                    <!-- Pilihan Masjid Baru / Terdaftar -->
                    <div class="flex bg-gray-100 p-0.5 rounded-lg text-xs font-medium">
                        <button type="button" 
                                @click="isNewMasjid = '0'" 
                                :class="isNewMasjid === '0' ? 'bg-white text-gray-900 shadow-xs font-semibold' : 'text-gray-500 hover:text-gray-900'"
                                class="px-2.5 py-1 rounded-md transition-all cursor-pointer">
                            Cari Terdaftar
                        </button>
                        <button type="button" 
                                @click="isNewMasjid = '1'; selectedMasjidId = ''; masjidQuery = '';" 
                                :class="isNewMasjid === '1' ? 'bg-white text-gray-900 shadow-xs font-semibold' : 'text-gray-500 hover:text-gray-900'"
                                class="px-2.5 py-1 rounded-md transition-all cursor-pointer">
                            + Masjid Baru
                        </button>
                    </div>
                </div>

                <!-- Input Pencarian Masjid Lama -->
                <div x-show="isNewMasjid === '0'" class="relative space-y-1.5">
                    <label class="block text-sm font-medium text-gray-900">
                        Nama Masjid atau Musholla
                    </label>
                    <input type="text" 
                           x-model="masjidQuery" 
                           @focus="showDropdown = true; searchMasjid()"
                           @input="searchMasjid(); showDropdown = true"
                           placeholder="Ketik nama masjid (contoh: Al-Muhajirin)" 
                           class="w-full min-h-[46px] px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 text-base placeholder-gray-400 focus:outline-none focus:border-[#1E3E34] focus:ring-1 focus:ring-[#1E3E34]">
                    
                    <!-- Kotak Rekomendasi Autocomplete -->
                    <div x-show="showDropdown && filteredMasjids.length > 0" 
                         @click.away="showDropdown = false"
                         class="absolute z-30 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto">
                        <template x-for="item in filteredMasjids" :key="item.id">
                            <button type="button" 
                                    @click="selectMasjid(item)"
                                    class="w-full text-left px-4 py-3 hover:bg-[#EBF2EE] border-b border-gray-100 last:border-0 transition-colors cursor-pointer">
                                <span class="block text-sm font-semibold text-gray-900" x-text="item.name"></span>
                                <span class="block text-xs text-gray-500 truncate mt-0.5" x-text="item.address"></span>
                            </button>
                        </template>
                    </div>

                    <div class="pt-1">
                        <button type="button" 
                                @click="isNewMasjid = '1'; selectedMasjidId = ''; masjidQuery = '';"
                                class="text-xs font-medium text-[#1E3E34] hover:underline cursor-pointer">
                            + Daftarkan nama masjid baru ini jika belum ada di daftar
                        </button>
                    </div>

                    @error('masjid_id')
                        <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Input Nama Masjid Baru -->
                <div x-show="isNewMasjid === '1'" class="space-y-1.5">
                    <label for="new_masjid_name" class="block text-sm font-medium text-gray-900">
                        Nama Masjid atau Musholla Baru
                    </label>
                    <input type="text" 
                           name="new_masjid_name" 
                           id="new_masjid_name"
                           value="{{ old('new_masjid_name') }}"
                           placeholder="Contoh: Masjid Jamie Al-Barkah" 
                           class="w-full min-h-[46px] px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 text-base placeholder-gray-400 focus:outline-none focus:border-[#1E3E34] focus:ring-1 focus:ring-[#1E3E34]">
                    @error('new_masjid_name')
                        <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat Lengkap -->
                <div class="space-y-1.5">
                    <label for="address" class="block text-sm font-medium text-gray-900">
                        Alamat Lengkap Masjid
                    </label>
                    <textarea name="address" 
                              id="address" 
                              rows="2" 
                              x-model="address"
                              required
                              placeholder="Jalan, nomor, kelurahan, kecamatan, kota (dan patokan)" 
                              class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:border-[#1E3E34] focus:ring-1 focus:ring-[#1E3E34]"></textarea>
                    @error('address')
                        <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- BAGIAN 2: KONTAK PENGURUS -->
            <div class="space-y-4 pt-2">
                <h2 class="text-base font-semibold text-gray-900 pb-2 border-b border-gray-100">
                    2. Kontak Pengurus (DKM / Marbot)
                </h2>

                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label for="pic_name" class="block text-sm font-medium text-gray-900">
                            Nama Pengurus atau Pemesan
                        </label>
                        <input type="text" 
                               name="pic_name" 
                               id="pic_name" 
                               x-model="picName"
                               required
                               placeholder="Contoh: Bapak H. Bambang" 
                               class="w-full min-h-[46px] px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 text-base placeholder-gray-400 focus:outline-none focus:border-[#1E3E34] focus:ring-1 focus:ring-[#1E3E34]">
                        @error('pic_name')
                            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="pic_phone" class="block text-sm font-medium text-gray-900">
                            Nomor WhatsApp Aktif
                        </label>
                        <input type="tel" 
                               name="pic_phone" 
                               id="pic_phone" 
                               x-model="picPhone"
                               required
                               placeholder="Contoh: 081234567890" 
                               class="w-full min-h-[46px] px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 text-base placeholder-gray-400 focus:outline-none focus:border-[#1E3E34] focus:ring-1 focus:ring-[#1E3E34] font-mono">
                        @error('pic_phone')
                            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- BAGIAN 3: KELUHAN & JUMLAH AC -->
            <div class="space-y-4 pt-2">
                <h2 class="text-base font-semibold text-gray-900 pb-2 border-b border-gray-100">
                    3. Kategori Layanan & Jumlah Unit
                </h2>

                <input type="hidden" name="dkm_complaint" :value="complaint">
                <input type="hidden" name="ac_count" :value="acCount">

                <!-- Card Radio Selector Bersih (Tanpa Emoji) -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-900">
                        Pilih Kategori Servis:
                    </label>
                    <div class="grid grid-cols-1 gap-2.5">
                        
                        <!-- Pilihan 1: Cuci Rutin -->
                        <div @click="complaint = 'Cuci Rutin'" 
                             :class="complaint === 'Cuci Rutin' ? 'border-[#1E3E34] bg-[#EBF2EE] ring-1 ring-[#1E3E34]' : 'border-gray-200 bg-white hover:border-gray-300'"
                             class="p-4 rounded-lg border text-left transition-all cursor-pointer flex items-center justify-between">
                            <div>
                                <span class="block text-base font-semibold text-gray-900">Cuci Rutin</span>
                                <span class="block text-xs text-gray-600 mt-0.5">Pembersihan filter, evaporator, dan blower</span>
                            </div>
                            <span class="text-sm font-bold text-[#1E3E34]">Rp 75.000 / unit</span>
                        </div>

                        <!-- Pilihan 2: Kurang Dingin / Bocor -->
                        <div @click="complaint = 'Tidak Dingin / Bocor Air'" 
                             :class="complaint === 'Tidak Dingin / Bocor Air' ? 'border-[#1E3E34] bg-[#EBF2EE] ring-1 ring-[#1E3E34]' : 'border-gray-200 bg-white hover:border-gray-300'"
                             class="p-4 rounded-lg border text-left transition-all cursor-pointer flex items-center justify-between">
                            <div>
                                <span class="block text-base font-semibold text-gray-900">Kurang Dingin / Bocor Air</span>
                                <span class="block text-xs text-gray-600 mt-0.5">Pengecekan saluran pembuangan dan tekanan gas</span>
                            </div>
                            <span class="text-sm font-bold text-[#1E3E34]">Rp 85.000 / unit</span>
                        </div>

                        <!-- Pilihan 3: Mati Total / Rusak -->
                        <div @click="complaint = 'Mati Total / Rusak'" 
                             :class="complaint === 'Mati Total / Rusak' ? 'border-[#1E3E34] bg-[#EBF2EE] ring-1 ring-[#1E3E34]' : 'border-gray-200 bg-white hover:border-gray-300'"
                             class="p-4 rounded-lg border text-left transition-all cursor-pointer flex items-center justify-between">
                            <div>
                                <span class="block text-base font-semibold text-gray-900">Mati Total / Rusak</span>
                                <span class="block text-xs text-gray-600 mt-0.5">Pemeriksaan komponen modul dan kelistrikan</span>
                            </div>
                            <span class="text-sm font-bold text-[#1E3E34]">Rp 100.000 (Cek)</span>
                        </div>

                    </div>
                </div>

                <!-- Counter Jumlah AC Ramah Jempol -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex items-center justify-between gap-4">
                    <div>
                        <span class="block text-sm font-semibold text-gray-900">Jumlah Unit AC</span>
                        <span class="block text-xs text-gray-500">Tekan tombol untuk mengubah</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="decrement()"
                                :disabled="acCount <= 1"
                                class="w-11 h-11 rounded-lg bg-white border border-gray-300 text-gray-800 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed font-bold text-xl flex items-center justify-center cursor-pointer">
                            &minus;
                        </button>

                        <div class="w-14 h-11 bg-white border border-gray-300 rounded-lg flex items-center justify-center font-bold text-base text-gray-900">
                            <span x-text="acCount"></span>
                        </div>

                        <button type="button" 
                                @click="increment()"
                                class="w-11 h-11 rounded-lg bg-[#1E3E34] text-white hover:bg-[#142B24] font-bold text-xl flex items-center justify-center cursor-pointer">
                            +
                        </button>
                    </div>
                </div>

                <!-- Jadwal Kedatangan Pilihan -->
                <div class="space-y-3">
                    <div class="space-y-1.5">
                        <label for="service_date" class="block text-sm font-medium text-gray-900">
                            Rencana Hari Servis (Opsional)
                        </label>
                        <input type="date" 
                               name="service_date" 
                               id="service_date"
                               value="{{ old('service_date', now()->addDay()->toDateString()) }}"
                               min="{{ now()->toDateString() }}"
                               class="w-full min-h-[46px] px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-900 text-sm focus:outline-none focus:border-[#1E3E34]">
                    </div>

                    <div class="space-y-1.5">
                        <label for="service_time" class="block text-sm font-medium text-gray-900">
                            Pilihan Waktu Kedatangan
                        </label>
                        <select name="service_time" 
                                id="service_time"
                                class="w-full min-h-[46px] px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-900 text-sm focus:outline-none focus:border-[#1E3E34]">
                            <option value="Pagi (08:30 - 11:30 WIB)">Pagi (08:30 - 11:30 WIB)</option>
                            <option value="Siang Ba'da Dzuhur (13:00 - 15:00 WIB)">Siang Ba'da Dzuhur (13:00 - 15:00 WIB)</option>
                            <option value="Sore Ba'da Ashar (16:00 - 17:30 WIB)">Sore Ba'da Ashar (16:00 - 17:30 WIB)</option>
                            <option value="Kapan Saja / Fleksibel">Kapan Saja / Fleksibel</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label for="notes" class="block text-sm font-medium text-gray-900">
                            Catatan Tambahan (Opsional)
                        </label>
                        <input type="text" 
                               name="notes" 
                               id="notes"
                               value="{{ old('notes') }}"
                               placeholder="Contoh: Butuh tangga tinggi untuk ruang sholat utama" 
                               class="w-full min-h-[46px] px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:border-[#1E3E34]">
                    </div>
                </div>
            </div>

            <!-- KOTAK ESTIMASI BIAYA SOLID -->
            <div class="bg-[#EBF2EE] border border-[#1E3E34]/20 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="block text-xs uppercase tracking-wider text-gray-600 font-semibold">Perkiraan Biaya Awal</span>
                        <div class="text-xl sm:text-2xl font-bold text-[#1E3E34] mt-0.5">
                            Rp <span x-text="formatRupiah(estimatedTotal)"></span>
                        </div>
                    </div>
                    <div class="text-right text-xs text-gray-600">
                        <span class="block font-semibold" x-text="acCount + ' Unit &bull; ' + complaint"></span>
                        <span class="block text-[11px] text-gray-500">*Pelunasan setelah servis beres</span>
                    </div>
                </div>
            </div>

            <!-- TOMBOL SUBMIT UTAMA (Minimal 48px, Hijau Khidmat #1E3E34) -->
            <button type="submit" 
                    class="w-full min-h-[48px] py-3.5 px-6 bg-[#1E3E34] hover:bg-[#142B24] text-white font-semibold text-base rounded-lg transition-colors focus:ring-2 focus:ring-[#1E3E34] focus:ring-offset-2 flex items-center justify-center cursor-pointer">
                Kirim Pesanan Servis
            </button>
        </form>
    </div>

    <!-- Informasi Bantuan -->
    <div class="mt-6 text-center text-xs text-gray-500">
        Butuh bantuan atau koordinasi langsung? Hubungi Admin melalui WhatsApp pada nomor yang tertera di halaman konfirmasi.
    </div>

</div>
@endsection
