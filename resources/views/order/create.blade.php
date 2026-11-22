@extends('layouts.app', ['title' => 'Formulir Pemesanan Servis AC Masjid & Musholla'])

@section('content')
<div class="max-w-[520px] mx-auto py-2 sm:py-6" 
     x-data="{
        isNewMasjid: '0',
        selectedMasjidId: '{{ old('masjid_id', '') }}',
        masjids: {{ Js::from($masjids) }},
        selectedMasjidUnits: [],
        masjidQuery: '',
        filteredMasjids: [],
        showDropdown: false,
        address: '{{ old('address', '') }}',
        picName: '{{ old('pic_name', '') }}',
        picPhone: '{{ old('pic_phone', '') }}',

        // Multi-Item Service Rows (Mendukung lebih dari 1 jenis PK & kategori servis)
        serviceRows: [
            {
                room_name: '',
                ac_type: '1 PK',
                service_category: 'Cuci Rutin',
                quantity: 1,
                ac_unit_id: null,
            }
        ],

        ratesMatrix: {
            'Cuci Rutin': {
                '0.5 PK': 65000,
                '0.75 PK': 65000,
                '1 PK': 65000,
                '1.5 PK': 75000,
                '2 PK': 75000,
                '2.5 PK': 90000,
                '3 PK': 90000,
                '5 PK': 130000,
            },
            'Kurang Dingin / Bocor Air': {
                '0.5 PK': 80000,
                '0.75 PK': 80000,
                '1 PK': 80000,
                '1.5 PK': 90000,
                '2 PK': 90000,
                '2.5 PK': 110000,
                '3 PK': 110000,
                '5 PK': 160000,
            },
            'Mati Total / Rusak': {
                '0.5 PK': 100000,
                '0.75 PK': 100000,
                '1 PK': 100000,
                '1.5 PK': 120000,
                '2 PK': 120000,
                '2.5 PK': 140000,
                '3 PK': 140000,
                '5 PK': 200000,
            }
        },

        getRowRate(row) {
            let cat = row.service_category || 'Cuci Rutin';
            let catRates = this.ratesMatrix[cat] || this.ratesMatrix['Cuci Rutin'];
            let pk = row.ac_type || '1 PK';
            return catRates[pk] || 75000;
        },

        getRowSubtotal(row) {
            return (parseInt(row.quantity) || 1) * this.getRowRate(row);
        },

        get totalUnits() {
            return this.serviceRows.reduce((sum, r) => sum + (parseInt(r.quantity) || 1), 0);
        },

        get totalEstimatedAmount() {
            return this.serviceRows.reduce((sum, r) => sum + this.getRowSubtotal(r), 0);
        },

        get complaintSummary() {
            return this.serviceRows.map(r => {
                let room = r.room_name ? ` [${r.room_name}]` : '';
                return `${r.quantity}x ${r.ac_type} (${r.service_category}${room})`;
            }).join(', ');
        },

        addRow() {
            this.serviceRows.push({
                room_name: '',
                ac_type: '1 PK',
                service_category: 'Cuci Rutin',
                quantity: 1,
                ac_unit_id: null,
            });
        },

        removeRow(index) {
            if (this.serviceRows.length > 1) {
                this.serviceRows.splice(index, 1);
            }
        },

        importRegisteredUnits() {
            if (!this.selectedMasjidUnits || this.selectedMasjidUnits.length === 0) return;
            this.serviceRows = this.selectedMasjidUnits.map(unit => ({
                room_name: unit.room_name || '',
                ac_type: unit.ac_type || '1 PK',
                service_category: 'Cuci Rutin',
                quantity: parseInt(unit.quantity) || 1,
                ac_unit_id: unit.id,
            }));
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
            this.selectedMasjidUnits = item.ac_units || [];
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
    <div class="text-center mb-5">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
            Pemesanan Servis AC Masjid
        </h1>
        <p class="text-sm text-gray-600 mt-1 leading-relaxed">
            Formulir permohonan perawatan pendingin ruang ibadah untuk Pengurus DKM dan Marbot.
        </p>
    </div>

    <!-- Box Lacak Pesanan / Cek Status dengan Nomor Registrasi -->
    <div class="mb-5 bg-white rounded-xl border border-gray-200 p-4 shadow-sm" 
         x-data="{ openTrack: {{ $errors->has('track_order_number') || request('order_number') ? 'true' : 'false' }} }">
        <div class="flex items-center justify-between cursor-pointer" @click="openTrack = !openTrack">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-[#EBF2EE] text-[#1E3E34] flex items-center justify-center border border-[#1E3E34]/20 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Sudah Pernah Memesan?</h2>
                    <p class="text-xs text-gray-500">Cek status pengerjaan & bukti pembayaran pesanan</p>
                </div>
            </div>
            <button type="button" class="text-xs font-semibold text-[#1E3E34] hover:underline cursor-pointer">
                <span x-text="openTrack ? 'Tutup' : 'Cek Status &rarr;'"></span>
            </button>
        </div>

        <div x-show="openTrack" x-cloak class="mt-3 pt-3 border-t border-gray-100 space-y-2">
            <form method="GET" action="{{ route('order.track') }}" class="flex flex-col sm:flex-row gap-2">
                <div class="flex-1">
                    <input type="text" 
                           name="order_number" 
                           value="{{ old('track_order_number', request('order_number')) }}" 
                           required 
                           placeholder="Masukkan Nomor Registrasi (contoh: SO-{{ now()->format('Ymd') }}-001)" 
                           class="w-full min-h-[44px] px-3.5 py-2.5 rounded-lg border {{ $errors->has('track_order_number') ? 'border-red-400 bg-red-50/30' : 'border-gray-300 bg-white' }} text-xs text-gray-900 font-mono outline-none focus:border-[#1E3E34]">
                </div>
                <button type="submit" 
                        class="min-h-[44px] px-5 py-2.5 bg-[#1E3E34] hover:bg-[#142B24] text-white rounded-lg text-xs font-semibold cursor-pointer transition-colors whitespace-nowrap">
                    Lihat Pesanan
                </button>
            </form>
            @error('track_order_number')
                <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Pilihan Mode: Masjid Terdaftar / Masjid Baru (Di Atas Card) -->
    <div class="grid grid-cols-2 p-1 bg-gray-200/80 rounded-xl mb-4 text-xs sm:text-sm font-medium">
        <button type="button" 
                @click="isNewMasjid = '0'" 
                :class="isNewMasjid === '0' ? 'bg-[#1E3E34] text-white shadow-sm font-semibold' : 'text-gray-700 hover:text-gray-900'"
                class="min-h-[44px] py-2.5 px-3 rounded-lg transition-all cursor-pointer text-center flex items-center justify-center">
            Cari Masjid Terdaftar
        </button>
        <button type="button" 
                @click="isNewMasjid = '1'; selectedMasjidId = ''; masjidQuery = ''; selectedMasjidUnits = [];" 
                :class="isNewMasjid === '1' ? 'bg-[#1E3E34] text-white shadow-sm font-semibold' : 'text-gray-700 hover:text-gray-900'"
                class="min-h-[44px] py-2.5 px-3 rounded-lg transition-all cursor-pointer text-center flex items-center justify-center">
            + Daftarkan Masjid Baru
        </button>
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
                <div class="pb-2 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">
                        1. Lokasi Masjid atau Musholla
                    </h2>
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

                <!-- Tampilan Unit AC Terdaftar (Jika Masjid Sudah Pernah Servis / Sudah Diisi di Kelola Unit AC) -->
                <div x-show="isNewMasjid === '0' && selectedMasjidId" x-cloak class="space-y-2 pt-2">
                    <template x-if="selectedMasjidUnits.length > 0">
                        <div class="p-4 rounded-xl bg-[#EBF2EE] border border-[#1E3E34]/20 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#1E3E34]"></span>
                                    <span class="text-xs font-bold text-[#1E3E34] uppercase tracking-wider">
                                        Data Unit AC Terdaftar di Masjid Ini:
                                    </span>
                                </div>
                                <span class="px-2.5 py-1 rounded text-[11px] font-bold bg-[#1E3E34] text-white" 
                                      x-text="selectedMasjidUnits.reduce((acc, u) => acc + (parseInt(u.quantity) || 1), 0) + ' Unit'"></span>
                            </div>

                            <div class="divide-y divide-gray-200/70 bg-white rounded-lg border border-gray-200 overflow-hidden text-xs">
                                <template x-for="unit in selectedMasjidUnits" :key="unit.id">
                                    <div class="p-3 flex items-center justify-between hover:bg-gray-50/80">
                                        <div>
                                            <strong class="text-gray-900 block" x-text="unit.room_name"></strong>
                                            <span class="text-gray-500 text-[11px]" x-text="(unit.brand ? unit.brand + ' &bull; ' : '') + unit.ac_type"></span>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 font-bold text-[11px]"
                                              x-text="(unit.quantity || 1) + ' Unit'"></span>
                                    </div>
                                </template>
                            </div>
                            <p class="text-[11px] text-[#1E3E34] leading-relaxed">
                                Catatan: Data unit di atas merupakan catatan inventaris AC dari servis sebelumnya atau kelola unit AC.
                            </p>
                        </div>
                    </template>

                    <template x-if="selectedMasjidUnits.length === 0">
                        <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200 text-xs text-gray-600">
                            <span class="font-medium text-gray-800 block">Belum ada inventaris unit AC terdata untuk masjid ini.</span>
                            <span class="text-gray-500 text-[11px] block mt-0.5">Teknisi kami akan mendata dan mencatat spesifikasi PK unit AC saat melakukan servis di lokasi.</span>
                        </div>
                    </template>
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

            <!-- BAGIAN 3: RINCIAN AC & KATEGORI LAYANAN SERVIS -->
            <div class="space-y-4 pt-2">
                <div class="pb-2 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">
                            3. Rincian AC & Kategori Layanan Servis
                        </h2>
                        <p class="text-xs text-gray-500">
                            Bisa memilih lebih dari 1 jenis AC dan kategori servis dalam satu pesanan.
                        </p>
                    </div>

                    <!-- Shortcut: Tombol Impor AC Terdaftar jika ada -->
                    <template x-if="isNewMasjid === '0' && selectedMasjidUnits.length > 0">
                        <button type="button" 
                                @click="importRegisteredUnits()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#1E3E34] bg-[#EBF2EE] text-[#1E3E34] text-xs font-semibold hover:bg-[#1E3E34] hover:text-white transition-colors cursor-pointer self-start sm:self-auto">
                            <span>Gunakan Data AC Terdaftar &rarr;</span>
                        </button>
                    </template>
                </div>

                <!-- Fallback Hidden Inputs untuk Kompatibilitas Sistem -->
                <input type="hidden" name="dkm_complaint" :value="complaintSummary">
                <input type="hidden" name="ac_count" :value="totalUnits">

                <!-- DAFTAR BARIS ITEM AC (DYNAMIC ROWS) -->
                <div class="space-y-3">
                    <template x-for="(row, index) in serviceRows" :key="index">
                        <div class="p-4 rounded-xl border border-gray-200 bg-gray-50/70 space-y-3 text-xs relative">
                            <!-- Hidden inputs array untuk dikirim ke backend -->
                            <input type="hidden" :name="'service_items[' + index + '][ac_type]'" :value="row.ac_type">
                            <input type="hidden" :name="'service_items[' + index + '][service_category]'" :value="row.service_category">
                            <input type="hidden" :name="'service_items[' + index + '][room_name]'" :value="row.room_name">
                            <input type="hidden" :name="'service_items[' + index + '][quantity]'" :value="row.quantity">
                            <input type="hidden" :name="'service_items[' + index + '][ac_unit_id]'" :value="row.ac_unit_id || ''">

                            <!-- Header Baris: Nomor Item & Tombol Hapus -->
                            <div class="flex items-center justify-between pb-2 border-b border-gray-200">
                                <span class="font-bold text-gray-800 uppercase tracking-wider text-[11px]" x-text="'Item AC #' + (index + 1)"></span>
                                <button type="button" 
                                        x-show="serviceRows.length > 1"
                                        @click="removeRow(index)"
                                        class="text-red-600 hover:text-red-800 font-semibold cursor-pointer text-xs">
                                    &times; Hapus Baris
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Pilih Kapasitas / PK -->
                                <div class="space-y-1">
                                    <label class="block font-semibold text-gray-800">
                                        Kapasitas / PK AC: <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="row.ac_type" 
                                            class="w-full min-h-[42px] px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 font-medium outline-none focus:border-[#1E3E34]">
                                        <option value="0.5 PK">0.5 PK (1/2 PK)</option>
                                        <option value="0.75 PK">0.75 PK (3/4 PK)</option>
                                        <option value="1 PK">1 PK</option>
                                        <option value="1.5 PK">1.5 PK</option>
                                        <option value="2 PK">2 PK</option>
                                        <option value="2.5 PK">2.5 PK</option>
                                        <option value="3 PK">3 PK</option>
                                        <option value="5 PK">5 PK (Standing / Cassette)</option>
                                    </select>
                                </div>

                                <!-- Pilih Kategori Servis -->
                                <div class="space-y-1">
                                    <label class="block font-semibold text-gray-800">
                                        Kategori / Jenis Keluhan: <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="row.service_category" 
                                            class="w-full min-h-[42px] px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 font-medium outline-none focus:border-[#1E3E34]">
                                        <option value="Cuci Rutin">Cuci Rutin / Pembersihan</option>
                                        <option value="Kurang Dingin / Bocor Air">Kurang Dingin / Bocor Air</option>
                                        <option value="Mati Total / Rusak">Mati Total / Rusak Berat</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <!-- Nama Ruang / Penempatan (Opsional) -->
                                <div class="space-y-1">
                                    <label class="block font-semibold text-gray-700">
                                        Lokasi / Ruang AC: <span class="text-gray-400 font-normal">(opsional)</span>
                                    </label>
                                    <input type="text" 
                                           x-model="row.room_name" 
                                           placeholder="Contoh: Shaf Depan, Lantai 2" 
                                           class="w-full min-h-[42px] px-3.5 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 outline-none focus:border-[#1E3E34]">
                                </div>

                                <!-- Jumlah Unit (Quantity) Counter -->
                                <div class="space-y-1">
                                    <label class="block font-semibold text-gray-800">
                                        Jumlah Unit: <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" 
                                                @click="if (row.quantity > 1) row.quantity--" 
                                                class="w-10 h-10 rounded-lg bg-white border border-gray-300 text-gray-800 hover:bg-gray-100 font-bold text-lg flex items-center justify-center cursor-pointer">
                                            &minus;
                                        </button>
                                        <input type="number" 
                                               x-model.number="row.quantity" 
                                               min="1" 
                                               max="100" 
                                               class="w-16 h-10 text-center rounded-lg border border-gray-300 bg-white font-bold text-sm text-gray-900 outline-none focus:border-[#1E3E34]">
                                        <button type="button" 
                                                @click="if (row.quantity < 100) row.quantity++" 
                                                class="w-10 h-10 rounded-lg bg-[#1E3E34] text-white hover:bg-[#142B24] font-bold text-lg flex items-center justify-center cursor-pointer">
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Baris Rincian Tarif Subtotal per Item -->
                            <div class="pt-2 border-t border-gray-200 flex items-center justify-between text-xs">
                                <span class="text-gray-600">
                                    Tarif: <strong class="text-gray-900" x-text="'Rp ' + formatRupiah(getRowRate(row)) + ' / unit'"></strong>
                                </span>
                                <span class="font-bold text-[#1E3E34]" x-text="'Subtotal: Rp ' + formatRupiah(getRowSubtotal(row))"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Tombol Tambah Baris AC Lain -->
                <button type="button" 
                        @click="addRow()"
                        class="w-full min-h-[44px] py-2.5 px-4 rounded-xl border-2 border-dashed border-[#1E3E34]/40 bg-[#EBF2EE]/60 hover:bg-[#EBF2EE] text-[#1E3E34] font-bold text-xs flex items-center justify-center gap-2 cursor-pointer transition-colors">
                    <span>+ Tambah Jenis AC / Kategori Servis Lain</span>
                </button>

                <!-- Jadwal Kedatangan Pilihan -->
                <div class="space-y-3 pt-2">
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
            <div class="bg-[#EBF2EE] border border-[#1E3E34]/20 rounded-xl p-4 sm:p-5 space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="block text-xs uppercase tracking-wider text-gray-600 font-semibold">Perkiraan Biaya Awal</span>
                        <div class="text-xl sm:text-2xl font-bold text-[#1E3E34] mt-0.5">
                            Rp <span x-text="formatRupiah(totalEstimatedAmount)"></span>
                        </div>
                    </div>
                    <div class="text-right text-xs text-gray-600">
                        <span class="block font-bold text-gray-900" x-text="totalUnits + ' Unit AC Terpilih'"></span>
                        <span class="block text-[11px] text-gray-500">*Pelunasan setelah servis beres</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-[#1E3E34]/15 text-xs text-gray-600 flex items-start gap-1.5">
                    <span class="font-semibold text-gray-800 shrink-0">Rincian:</span>
                    <span class="text-[11px] leading-relaxed" x-text="complaintSummary"></span>
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
