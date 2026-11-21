<?php

namespace Database\Seeders;

use App\Models\AcUnit;
use App\Models\Masjid;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@servisac.com'],
            [
                'name' => 'Admin Pengelola',
                'phone' => '081234567890',
                'role' => User::ROLE_ADMIN,
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Akun Teknisi Lapangan
        $technician1 = User::firstOrCreate(
            ['email' => 'budi@servisac.com'],
            [
                'name' => 'Budi Santoso',
                'phone' => '081298765432',
                'role' => User::ROLE_TECHNICIAN,
                'password' => Hash::make('password123'),
            ]
        );

        $technician2 = User::firstOrCreate(
            ['email' => 'agus@servisac.com'],
            [
                'name' => 'Agus Prasetyo',
                'phone' => '081311223344',
                'role' => User::ROLE_TECHNICIAN,
                'password' => Hash::make('password123'),
            ]
        );

        // 3. Data Masjid 1
        $masjid1 = Masjid::firstOrCreate(
            ['name' => 'Masjid Jami Al-Muhajirin'],
            [
                'address' => 'Jl. Kebon Jeruk Raya No. 12, Kebon Jeruk, Jakarta Barat',
                'pic_name' => 'H. Abdullah',
                'pic_phone' => '081288889999',
                'last_service_date' => now()->subMonths(3)->toDateString(),
            ]
        );

        AcUnit::firstOrCreate(
            ['masjid_id' => $masjid1->id, 'room_name' => 'Ruang Sholat Utama'],
            ['ac_type' => '2 PK', 'brand' => 'Daikin']
        );
        AcUnit::firstOrCreate(
            ['masjid_id' => $masjid1->id, 'room_name' => 'Shaf Putri / Belakang'],
            ['ac_type' => '1.5 PK', 'brand' => 'Panasonic']
        );
        AcUnit::firstOrCreate(
            ['masjid_id' => $masjid1->id, 'room_name' => 'Ruang DKM'],
            ['ac_type' => '1 PK', 'brand' => 'Sharp']
        );

        // 4. Data Masjid 2
        $masjid2 = Masjid::firstOrCreate(
            ['name' => 'Musholla Baitul Muttaqin'],
            [
                'address' => 'Jl. Melati Indah No. 5, Pasar Minggu, Jakarta Selatan',
                'pic_name' => 'Pak Slamet (Marbot)',
                'pic_phone' => '085712345678',
                'last_service_date' => now()->subMonths(4)->toDateString(),
            ]
        );

        AcUnit::firstOrCreate(
            ['masjid_id' => $masjid2->id, 'room_name' => 'Ruang Sholat'],
            ['ac_type' => '1.5 PK', 'brand' => 'Gree']
        );

        // 5. Contoh Order 1: Pesanan Baru (Belum dijadwalkan)
        Order::firstOrCreate(
            ['order_number' => 'SO-' . now()->format('Ymd') . '-001'],
            [
                'masjid_id' => $masjid1->id,
                'technician_id' => null,
                'status' => Order::STATUS_PESANAN_BARU,
                'service_date' => now()->addDay()->toDateString(),
                'service_time' => '09:00 WIB',
                'ac_count' => 3,
                'dkm_complaint' => 'Cuci Rutin & Cek Freon',
                'base_amount' => 225000,
                'additional_amount' => 0,
            ]
        );

        // 6. Contoh Order 2: Dijadwalkan (Ditugaskan ke Budi)
        Order::firstOrCreate(
            ['order_number' => 'SO-' . now()->format('Ymd') . '-002'],
            [
                'masjid_id' => $masjid2->id,
                'technician_id' => $technician1->id,
                'status' => Order::STATUS_DIJADWALKAN,
                'service_date' => now()->toDateString(),
                'service_time' => '13:30 WIB',
                'ac_count' => 1,
                'dkm_complaint' => 'Tidak Dingin / Air Menetes di Bawah AC',
                'base_amount' => 85000,
                'additional_amount' => 0,
            ]
        );
    }
}
