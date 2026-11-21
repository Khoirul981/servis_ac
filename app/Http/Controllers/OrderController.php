<?php

namespace App\Http\Controllers;

use App\Models\Masjid;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Tampilkan formulir pemesanan servis publik (Ramah Bapak-Bapak / DKM).
     */
    public function create(): View
    {
        $masjids = Masjid::select('id', 'name', 'address', 'pic_name', 'pic_phone')
            ->orderBy('name')
            ->get();

        return view('order.create', compact('masjids'));
    }

    /**
     * Endpoint autocomplete / pencarian cepat data masjid.
     */
    public function searchMasjid(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));

        if (empty($query)) {
            return response()->json([]);
        }

        $results = Masjid::where('name', 'like', "%{$query}%")
            ->orWhere('address', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'address', 'pic_name', 'pic_phone']);

        return response()->json($results);
    }

    /**
     * Simpan pesanan servis baru dari DKM.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Perlindungan Anti-Spam (Honeypot)
        if (! empty($request->input('website'))) {
            // Kolom jebakan terisi -> bot spam, tolak tanpa memproses
            return redirect()->route('home');
        }

        // 2. Perlindungan Anti-Spam (Rate Limiter: maks 3 order per nomor WA dalam 1 jam)
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $request->input('pic_phone'));
        $rateLimitKey = 'order-submission:' . ($cleanPhone ?: $request->ip());

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = ceil($seconds / 60);

            return back()->withInput()->withErrors([
                'pic_phone' => "Terlalu banyak permintaan pemesanan dari nomor ini. Mohon tunggu {$minutes} menit lagi sebelum memesan kembali.",
            ]);
        }

        // 3. Validasi Data Masukan
        $validated = $request->validate([
            'is_new_masjid' => ['required', 'in:0,1'],
            'masjid_id' => ['nullable', 'required_if:is_new_masjid,0', 'exists:masjids,id'],
            'new_masjid_name' => ['nullable', 'required_if:is_new_masjid,1', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'pic_name' => ['required', 'string', 'max:150'],
            'pic_phone' => ['required', 'string', 'regex:/^(08|628|\+628)[0-9]{8,12}$/'],
            'dkm_complaint' => ['required', 'string'],
            'ac_count' => ['required', 'integer', 'min:1', 'max:100'],
            'service_date' => ['nullable', 'date'],
            'service_time' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'pic_phone.regex' => 'Format nomor WhatsApp tidak valid. Gunakan format contoh: 081234567890.',
            'pic_name.required' => 'Nama pengurus / kontak wajib diisi.',
            'address.required' => 'Alamat masjid wajib diisi.',
            'ac_count.min' => 'Jumlah unit AC minimal 1 unit.',
            'dkm_complaint.required' => 'Pilih salah satu keluhan AC.',
        ]);

        // Standarisasi nomor HP ke awalan 08
        if (str_starts_with($cleanPhone, '628')) {
            $cleanPhone = '08' . substr($cleanPhone, 3);
        }

        // 4. Proses Simpan / Ambil Data Masjid
        if ($validated['is_new_masjid'] === '1') {
            $masjid = Masjid::create([
                'name' => trim($validated['new_masjid_name']),
                'address' => trim($validated['address']),
                'pic_name' => trim($validated['pic_name']),
                'pic_phone' => $cleanPhone,
            ]);
        } else {
            /** @var Masjid $masjid */
            $masjid = Masjid::findOrFail($validated['masjid_id']);

            // Perbarui alamat atau kontak jika ada perubahan
            $masjid->update([
                'address' => trim($validated['address']),
                'pic_name' => trim($validated['pic_name']),
                'pic_phone' => $cleanPhone,
            ]);
        }

        // 5. Hitung Estimasi Biaya Awal (Base Amount)
        $ratePerUnit = match ($validated['dkm_complaint']) {
            'Tidak Dingin / Bocor Air' => 85000,
            'Mati Total / Rusak' => 100000,
            default => 75000, // Cuci Rutin
        };
        $baseAmount = $ratePerUnit * (int) $validated['ac_count'];

        // 6. Generate Nomor Pesanan Unik: SO-YYYYMMDD-XXX
        $todayPrefix = 'SO-' . now()->format('Ymd') . '-';
        $todayCount = Order::where('order_number', 'like', "{$todayPrefix}%")->count();
        $orderNumber = $todayPrefix . str_pad((string) ($todayCount + 1), 3, '0', STR_PAD_LEFT);

        // 7. Simpan Order
        $order = Order::create([
            'order_number' => $orderNumber,
            'masjid_id' => $masjid->id,
            'technician_id' => null,
            'status' => Order::STATUS_PESANAN_BARU,
            'service_date' => $validated['service_date'] ?? now()->addDay()->toDateString(),
            'service_time' => $validated['service_time'] ?? 'Pagi (09:00 WIB)',
            'ac_count' => (int) $validated['ac_count'],
            'base_amount' => $baseAmount,
            'additional_amount' => 0,
            'additional_notes' => $validated['notes'] ?? null,
            'dkm_complaint' => $validated['dkm_complaint'],
        ]);

        // Catat hit rate limiter (berlaku 1 jam = 3600 detik)
        RateLimiter::hit($rateLimitKey, 3600);

        return redirect()->route('order.success', ['order_number' => $order->order_number]);
    }

    /**
     * Tampilkan halaman sukses & tombol konfirmasi WhatsApp ke Admin.
     */
    public function success(string $order_number): View
    {
        $order = Order::with('masjid')->where('order_number', $order_number)->firstOrFail();

        // Cari nomor WhatsApp Admin pengelola
        $admin = User::where('role', User::ROLE_ADMIN)->first();
        $adminPhone = $admin?->phone ?? '081234567890';

        // Konversi ke format internasional 628... untuk wa.me
        $cleanAdminPhone = preg_replace('/[^0-9]/', '', $adminPhone);
        if (str_starts_with($cleanAdminPhone, '08')) {
            $cleanAdminPhone = '628' . substr($cleanAdminPhone, 2);
        }

        // Susun template pesan WhatsApp otomatis yang ramah & santun
        $waMessage = "Assalamualaikum Admin Servis AC Masjid,%0A%0A" .
            "Saya telah memesan servis AC melalui website dengan rincian berikut:%0A" .
            "📌 *No. Pesanan*: {$order->order_number}%0A" .
            "🕌 *Masjid*: {$order->masjid->name}%0A" .
            "📍 *Alamat*: {$order->masjid->address}%0A" .
            "👤 *Pengurus/PIC*: {$order->masjid->pic_name} ({$order->masjid->pic_phone})%0A" .
            "❄️ *Jumlah AC*: {$order->ac_count} Unit%0A" .
            "🔧 *Keluhan*: {$order->dkm_complaint}%0A" .
            "💰 *Estimasi Biaya*: Rp " . number_format($order->base_amount, 0, ',', '.') . "%0A%0A" .
            "Mohon info jadwal kedatangan teknisinya ya. Terima kasih!";

        $whatsappUrl = "https://wa.me/{$cleanAdminPhone}?text={$waMessage}";

        return view('order.success', compact('order', 'whatsappUrl', 'adminPhone'));
    }
}
