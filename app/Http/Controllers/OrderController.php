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
        $masjids = Masjid::with('acUnits')
            ->select('id', 'name', 'address', 'pic_name', 'pic_phone')
            ->orderBy('name')
            ->get();

        $pkOptions = \App\Services\AcPricingService::getPkOptions();
        $pricingCategories = \App\Services\AcPricingService::getCategories();

        return view('order.create', compact('masjids', 'pkOptions', 'pricingCategories'));
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

        $results = Masjid::with('acUnits')
            ->where('name', 'like', "%{$query}%")
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
            'dkm_complaint' => ['nullable', 'string'],
            'ac_count' => ['nullable', 'integer', 'min:1', 'max:100'],
            'service_items' => ['nullable', 'array', 'min:1'],
            'service_items.*.ac_type' => ['nullable', 'string', 'max:50'],
            'service_items.*.service_category' => ['nullable', 'string', 'max:100'],
            'service_items.*.room_name' => ['nullable', 'string', 'max:100'],
            'service_items.*.ac_unit_id' => ['nullable', 'integer', 'exists:ac_units,id'],
            'service_items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'service_date' => ['nullable', 'date'],
            'service_time' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'pic_phone.regex' => 'Format nomor WhatsApp tidak valid. Gunakan format contoh: 081234567890.',
            'pic_name.required' => 'Nama pengurus / kontak wajib diisi.',
            'address.required' => 'Alamat masjid wajib diisi.',
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

        // 5. Hitung Estimasi Biaya Awal & Rincian Multi-Item
        $rawServiceItems = $request->input('service_items');
        $serviceItems = [];
        $totalAcCount = 0;
        $baseAmount = 0.0;
        $complaintSummaries = [];

        if (is_array($rawServiceItems) && count($rawServiceItems) > 0) {
            foreach ($rawServiceItems as $item) {
                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $acType = ! empty($item['ac_type']) ? trim((string) $item['ac_type']) : '1 PK';
                $serviceCategory = ! empty($item['service_category']) ? trim((string) $item['service_category']) : 'Cuci Rutin';
                $roomName = ! empty($item['room_name']) ? trim((string) $item['room_name']) : null;
                $acUnitId = ! empty($item['ac_unit_id']) ? (int) $item['ac_unit_id'] : null;

                $ratePerUnit = \App\Services\AcPricingService::getRate($serviceCategory, $acType);
                $subtotal = $ratePerUnit * $qty;

                $totalAcCount += $qty;
                $baseAmount += $subtotal;

                $roomText = $roomName ? " [{$roomName}]" : '';
                $complaintSummaries[] = "{$qty}x {$acType} ({$serviceCategory}{$roomText})";

                $serviceItems[] = [
                    'ac_unit_id' => $acUnitId,
                    'room_name' => $roomName,
                    'ac_type' => $acType,
                    'service_category' => $serviceCategory,
                    'quantity' => $qty,
                    'price_per_unit' => $ratePerUnit,
                    'subtotal' => $subtotal,
                ];
            }

            $dkmComplaint = implode(', ', $complaintSummaries);
        } else {
            // Fallback backward-compatible mode untuk single complaint & ac_count
            $dkmComplaint = ! empty($validated['dkm_complaint']) ? $validated['dkm_complaint'] : 'Cuci Rutin';
            $totalAcCount = max(1, (int) ($validated['ac_count'] ?? 1));
            $ratePerUnit = match ($dkmComplaint) {
                'Tidak Dingin / Bocor Air', 'Kurang Dingin / Bocor Air' => 85000.0,
                'Mati Total / Rusak' => 100000.0,
                default => 75000.0, // Cuci Rutin
            };
            $baseAmount = $ratePerUnit * $totalAcCount;

            $serviceItems[] = [
                'ac_unit_id' => null,
                'room_name' => null,
                'ac_type' => '1 PK - 2 PK',
                'service_category' => $dkmComplaint,
                'quantity' => $totalAcCount,
                'price_per_unit' => $ratePerUnit,
                'subtotal' => $baseAmount,
            ];
        }

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
            'ac_count' => $totalAcCount,
            'base_amount' => $baseAmount,
            'additional_amount' => 0,
            'additional_notes' => $validated['notes'] ?? null,
            'dkm_complaint' => $dkmComplaint,
        ]);

        // Simpan setiap item rincian servis
        foreach ($serviceItems as $sItem) {
            $order->serviceItems()->create($sItem);
        }

        // Catat hit rate limiter (berlaku 1 jam = 3600 detik)
        RateLimiter::hit($rateLimitKey, 3600);

        return redirect()->route('order.status', ['order_number' => $order->order_number]);
    }

    /**
     * Tampilkan halaman sukses & tombol konfirmasi WhatsApp ke Admin (Menggunakan tampilan terpadu status pesanan).
     */
    public function success(string $order_number): View
    {
        return $this->status($order_number);
    }

    /**
     * Pencarian / Lacak Status Pesanan berdasarkan Nomor Registrasi (contoh: SO-20261121-003).
     */
    public function track(Request $request): RedirectResponse
    {
        $orderNumber = strtoupper(trim((string) $request->input('order_number', $request->query('order_number', ''))));

        if (empty($orderNumber)) {
            return redirect()->route('order.create')
                ->withErrors(['track_order_number' => 'Silakan masukkan nomor registrasi pesanan Anda.']);
        }

        $order = Order::where('order_number', $orderNumber)->first();

        if (! $order) {
            return redirect()->route('order.create')
                ->withInput(['track_order_number' => $orderNumber])
                ->withErrors([
                    'track_order_number' => "Pesanan dengan nomor '{$orderNumber}' tidak ditemukan. Mohon pastikan format nomor benar (contoh: SO-" . now()->format('Ymd') . "-001).",
                ]);
        }

        return redirect()->route('order.status', ['order_number' => $order->order_number]);
    }

    /**
     * Halaman Rincian & Status Pesanan Lengkap Publik (dengan Status Pembayaran: Belum / Sudah Dibayar).
     */
    public function status(string $order_number): View
    {
        $order = Order::with(['masjid', 'technician', 'photos', 'spareparts', 'serviceItems'])
            ->where('order_number', $order_number)
            ->firstOrFail();

        // Cari nomor WhatsApp Admin pengelola
        $admin = User::where('role', User::ROLE_ADMIN)->first();
        $adminPhone = $admin?->phone ?? '081234567890';
        $cleanAdminPhone = preg_replace('/[^0-9]/', '', $adminPhone);
        if (str_starts_with($cleanAdminPhone, '08')) {
            $cleanAdminPhone = '628' . substr($cleanAdminPhone, 2);
        }

        $orderUrl = route('order.status', ['order_number' => $order->order_number]);

        // Susun teks konfirmasi WhatsApp
        $waMessage = "Assalamualaikum Admin Servis AC Masjid,%0A%0A" .
            "Saya telah memesan servis AC melalui website (Pesanan Berhasil Dikirim):%0A" .
            "📌 *No. Pesanan*: {$order->order_number}%0A" .
            "🕌 *Masjid*: {$order->masjid->name}%0A" .
            "📍 *Alamat*: {$order->masjid->address}%0A" .
            "👤 *Pengurus/PIC*: {$order->masjid->pic_name} ({$order->masjid->pic_phone})%0A" .
            "❄️ *Jumlah AC*: {$order->ac_count} Unit%0A" .
            "🔧 *Keluhan*: {$order->dkm_complaint}%0A" .
            "💰 *Biaya Awal*: Rp " . number_format($order->base_amount, 0, ',', '.') . "%0A" .
            "💳 *Status Pembayaran*: " . ($order->isPaid() ? 'SUDAH DIBAYAR' : 'BELUM DIBAYAR') . "%0A%0A" .
            "🔗 *Lihat Data Pesanan Anda Berhasil Dikirim*:%0A" .
            "{$orderUrl}%0A%0A" .
            "Mohon info jadwal kedatangan teknisinya ya. Terima kasih!";

        $whatsappUrl = "https://wa.me/{$cleanAdminPhone}?text={$waMessage}";

        return view('order.status', compact('order', 'whatsappUrl', 'adminPhone', 'orderUrl'));
    }

    /**
     * Kuitansi Resmi Terbuka untuk DKM (setelah status diverifikasi Lunas / Sudah Dibayar).
     */
    public function publicReceipt(string $order_number): View
    {
        $order = Order::with(['masjid', 'technician', 'photos', 'spareparts', 'serviceItems'])
            ->where('order_number', $order_number)
            ->firstOrFail();

        $metodeText = match ($order->payment_method) {
            'transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            'cash' => 'Tunai (Dititipkan via Teknisi)',
            default => 'Lunas',
        };

        $orderUrl = route('order.status', ['order_number' => $order->order_number]);

        // Cari nomor WhatsApp DKM
        $cleanPhone = preg_replace('/[^0-9]/', '', $order->masjid->pic_phone);
        if (str_starts_with($cleanPhone, '08')) {
            $cleanPhone = '628' . substr($cleanPhone, 2);
        }

        $tglBayar = $order->paid_at ? $order->paid_at->translatedFormat('d F Y, H:i') . ' WIB' : now()->translatedFormat('d F Y');

        $waText = "Assalamualaikum Wr. Wb. Pak {$order->masjid->pic_name},%0A%0A" .
            "Bukti pelunasan resmi servis AC di *{$order->masjid->name}*:%0A" .
            "📌 *No. Kuitansi*: {$order->order_number}%0A" .
            "💰 *TOTAL DIBAYAR*: Rp " . number_format($order->grandTotal(), 0, ',', '.') . "%0A" .
            "💳 *Metode*: {$metodeText}%0A" .
            "📅 *Waktu*: {$tglBayar}%0A" .
            "✅ *Status*: SUDAH DIBAYAR%0A%0A" .
            "🔗 *Lihat Data Pesanan Anda Berhasil Dikirim*:%0A" .
            "{$orderUrl}";

        $whatsappUrl = "https://wa.me/{$cleanPhone}?text={$waText}";

        return view('admin.receipt', compact('order', 'whatsappUrl', 'metodeText', 'orderUrl'));
    }
}
