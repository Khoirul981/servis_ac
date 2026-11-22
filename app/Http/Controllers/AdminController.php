<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPhoto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Papan Operasional Meja Admin (4 Tab Utama).
     */
    public function dashboard(Request $request): View
    {
        $activeTab = $request->query('tab', Order::STATUS_PESANAN_BARU);

        // Counter untuk masing-masing tab
        $counts = [
            'pesanan_baru' => Order::where('status', Order::STATUS_PESANAN_BARU)->count(),
            'berjalan' => Order::whereIn('status', [Order::STATUS_DIJADWALKAN, Order::STATUS_DIKERJAKAN])->count(),
            'verifikasi_bukti' => Order::whereIn('status', [
                Order::STATUS_PEKERJAAN_SELESAI,
                Order::STATUS_MENUNGGU_VERIFIKASI,
                Order::STATUS_MENUNGGU_BAYAR,
            ])->count(),
            'selesai' => Order::whereIn('status', [Order::STATUS_SELESAI, Order::STATUS_BATAL])->count(),
        ];

        // Query pesanan sesuai tab aktif
        $ordersQuery = Order::with(['masjid', 'technician', 'photos', 'spareparts', 'serviceItems']);

        match ($activeTab) {
            'berjalan' => $ordersQuery->whereIn('status', [Order::STATUS_DIJADWALKAN, Order::STATUS_DIKERJAKAN])
                ->orderByRaw("CASE WHEN status = 'dikerjakan' THEN 1 ELSE 2 END")
                ->orderBy('service_date'),
            'verifikasi_bukti', 'menunggu_bayar', Order::STATUS_MENUNGGU_BAYAR => $ordersQuery->whereIn('status', [
                    Order::STATUS_PEKERJAAN_SELESAI,
                    Order::STATUS_MENUNGGU_VERIFIKASI,
                    Order::STATUS_MENUNGGU_BAYAR,
                ])
                ->orderByRaw("CASE WHEN status = 'menunggu_verifikasi' THEN 1 WHEN status = 'pekerjaan_selesai' THEN 2 ELSE 3 END")
                ->latest('updated_at'),
            Order::STATUS_SELESAI => $ordersQuery->whereIn('status', [Order::STATUS_SELESAI, Order::STATUS_BATAL])
                ->latest('paid_at'),
            default => $ordersQuery->where('status', Order::STATUS_PESANAN_BARU)
                ->latest('created_at'),
        };

        $orders = $ordersQuery->paginate(15)->withQueryString();

        // Daftar teknisi aktif untuk dropdown penugasan
        $technicians = User::where('role', User::ROLE_TECHNICIAN)->orderBy('name')->get();

        return view('admin.dashboard', compact('orders', 'counts', 'activeTab', 'technicians'));
    }

    /**
     * Penugasan Teknisi & Penetapan Jadwal (dengan Deteksi Bentrok Jadwal).
     */
    public function assignTechnician(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'technician_id' => ['required', 'exists:users,id'],
            'service_date' => ['required', 'date'],
            'service_time' => ['required', 'string', 'max:100'],
        ], [
            'technician_id.required' => 'Pilih salah satu teknisi lapangan.',
            'service_date.required' => 'Tentukan tanggal kedatangan teknisi.',
            'service_time.required' => 'Tentukan jam kedatangan teknisi.',
        ]);

        $technicianId = (int) $request->input('technician_id');
        $serviceDate = $request->input('service_date');
        $serviceTime = $request->input('service_time');

        // Deteksi Bentrok Jadwal: Cek apakah teknisi sudah memiliki tugas servis aktif di tanggal & jam/sesi yang sama persis
        $conflict = Order::where('technician_id', $technicianId)
            ->where('id', '!=', $order->id)
            ->whereIn('status', [Order::STATUS_DIJADWALKAN, Order::STATUS_DIKERJAKAN])
            ->whereDate('service_date', $serviceDate)
            ->where('service_time', $serviceTime)
            ->with('masjid')
            ->first();

        if ($conflict) {
            $tech = User::find($technicianId);
            $techName = $tech?->name ?? 'Teknisi terpilih';
            $masjidName = $conflict->masjid?->name ?? 'masjid lain';
            $tglIndo = Carbon::parse($serviceDate)->translatedFormat('d F Y');

            return back()->withInput()->with('error', "Jadwal Bentrok! {$techName} sudah memiliki jadwal tugas servis di {$masjidName} pada tanggal {$tglIndo} sesi {$serviceTime}. Silakan pilih sesi jam lain atau teknisi yang tersedia.");
        }

        $order->update([
            'technician_id' => $technicianId,
            'service_date' => $serviceDate,
            'service_time' => $serviceTime,
            'status' => Order::STATUS_DIJADWALKAN,
        ]);

        return back()->with('success', "Pesanan {$order->order_number} berhasil dijadwalkan dan ditugaskan ke {$order->technician->name}.");
    }

    /**
     * API Pemeriksaan Jadwal & Ketersediaan Teknisi pada Tanggal Tertentu.
     */
    public function technicianSchedule(Request $request): JsonResponse
    {
        $date = $request->query('date', now()->toDateString());
        $excludeOrderId = (int) $request->query('exclude_order_id', 0);

        $technicians = User::where('role', User::ROLE_TECHNICIAN)
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        $busyOrders = Order::with('masjid:id,name')
            ->whereIn('status', [Order::STATUS_DIJADWALKAN, Order::STATUS_DIKERJAKAN])
            ->whereDate('service_date', $date)
            ->when($excludeOrderId > 0, fn ($q) => $q->where('id', '!=', $excludeOrderId))
            ->get(['id', 'technician_id', 'masjid_id', 'service_time', 'order_number']);

        $schedules = $technicians->map(function ($tech) use ($busyOrders) {
            $assigned = $busyOrders->where('technician_id', $tech->id)->values();

            return [
                'id' => $tech->id,
                'name' => $tech->name,
                'phone' => $tech->phone,
                'task_count' => $assigned->count(),
                'tasks' => $assigned->map(fn ($o) => [
                    'order_id' => $o->id,
                    'order_number' => $o->order_number,
                    'masjid_name' => $o->masjid?->name ?? 'Masjid',
                    'service_time' => $o->service_time,
                ]),
            ];
        });

        return response()->json([
            'date' => $date,
            'technicians' => $schedules,
        ]);
    }

    /**
     * Verifikasi Pelunasan Pembayaran & Terbitkan Kuitansi.
     */
    public function confirmPayment(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'payment_method' => ['required', 'in:transfer,qris,cash'],
        ], [
            'payment_method.required' => 'Pilih metode pembayaran yang diterima.',
        ]);

        $order->update([
            'status' => Order::STATUS_SELESAI,
            'payment_method' => $request->input('payment_method'),
            'paid_at' => now(),
        ]);

        return redirect()->route('admin.order.receipt', $order->id)
            ->with('success', "Pembayaran untuk {$order->order_number} berhasil diverifikasi LUNAS. Kuitansi resmi siap dikirim.");
    }

    /**
     * Batalkan Pesanan.
     */
    public function cancelOrder(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        $order->update([
            'status' => Order::STATUS_BATAL,
            'additional_notes' => trim(($order->additional_notes ?? '') . ' [Dibatalkan oleh Admin: ' . $request->input('reason', 'Atas permintaan pemesan') . ']'),
        ]);

        return back()->with('success', "Pesanan {$order->order_number} telah dibatalkan.");
    }

    /**
     * Tampilan Kuitansi / Lembar Pelunasan Resmi.
     */
    public function receipt(int $id): View
    {
        $order = Order::with(['masjid', 'technician', 'photos', 'spareparts', 'serviceItems'])->findOrFail($id);

        // Susun teks pesan kuitansi ke WhatsApp DKM
        $cleanPhone = preg_replace('/[^0-9]/', '', $order->masjid->pic_phone);
        if (str_starts_with($cleanPhone, '08')) {
            $cleanPhone = '628' . substr($cleanPhone, 2);
        }

        $metodeText = match ($order->payment_method) {
            'transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            'cash' => 'Tunai (Dititipkan via Teknisi)',
            default => 'Lunas',
        };

        $tglBayar = $order->paid_at ? $order->paid_at->translatedFormat('d F Y, H:i') . ' WIB' : now()->translatedFormat('d F Y');
        $orderUrl = route('order.status', ['order_number' => $order->order_number]);

        $waText = "Assalamualaikum Wr. Wb. Pak {$order->masjid->pic_name},%0A%0A" .
            "Terima kasih atas kepercayaannya merawat AC di *{$order->masjid->name}*. Berikut bukti pelunasan resmi servis AC:%0A%0A" .
            "🧾 *KUITANSI SERVIS AC MASJID*%0A" .
            "-------------------------------------%0A" .
            "📌 *No. Kuitansi*: {$order->order_number}%0A" .
            "🕌 *Masjid*: {$order->masjid->name}%0A" .
            "👨‍🔧 *Teknisi*: " . ($order->technician->name ?? 'Tim Servis') . "%0A" .
            "❄️ *Layanan*: {$order->ac_count} Unit AC ({$order->dkm_complaint})%0A" .
            "💵 *Biaya Servis*: Rp " . number_format($order->base_amount, 0, ',', '.') . "%0A";

        if ($order->additional_amount > 0) {
            $waText .= "⚙️ *Tambahan Sparepart*: Rp " . number_format($order->additional_amount, 0, ',', '.') . " ({$order->additional_notes})%0A";
        }

        $waText .= "💰 *TOTAL DIBAYAR*: Rp " . number_format($order->grandTotal(), 0, ',', '.') . "%0A" .
            "💳 *Metode*: {$metodeText}%0A" .
            "📅 *Waktu Pelunasan*: {$tglBayar}%0A" .
            "✅ *Status*: LUNAS & SUDAH DIBAYAR%0A" .
            "-------------------------------------%0A%0A" .
            "🔗 *Lihat Data Pesanan Anda Berhasil Dikirim*:%0A" .
            "{$orderUrl}%0A%0A" .
            "Jazakumullahu khairan katsiran. Semoga AC masjid senantiasa sejuk dan membawa kenyamanan bagi para jamaah ibadah. Aamiin ya Rabbal 'Alamin.";

        $whatsappUrl = "https://wa.me/{$cleanPhone}?text={$waText}";

        return view('admin.receipt', compact('order', 'whatsappUrl', 'metodeText', 'orderUrl'));
    }

    /**
     * Perbarui Kategori & Keterangan Foto Bukti Servis oleh Admin.
     */
    public function updatePhoto(Request $request, int $photo_id): RedirectResponse
    {
        $photo = OrderPhoto::findOrFail($photo_id);

        $validated = $request->validate([
            'photo_type' => ['required', 'in:sebelum,sesudah,sparepart'],
            'caption' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ], [
            'photo.image' => 'File pengganti harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $payload = [
            'photo_type' => $validated['photo_type'],
            'caption' => $validated['caption'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            if ($photo->photo_url && Storage::disk('public')->exists($photo->photo_url)) {
                Storage::disk('public')->delete($photo->photo_url);
            }
            $payload['photo_url'] = $request->file('photo')->store('order_photos', 'public');
        }

        $photo->update($payload);

        return back()->with('success', 'Data foto bukti berhasil diperbarui.');
    }

    /**
     * Hapus Foto Bukti Servis oleh Admin.
     */
    public function destroyPhoto(int $photo_id): RedirectResponse
    {
        $photo = OrderPhoto::findOrFail($photo_id);

        if ($photo->photo_url && Storage::disk('public')->exists($photo->photo_url)) {
            Storage::disk('public')->delete($photo->photo_url);
        }

        $photo->delete();

        return back()->with('success', 'Foto bukti servis berhasil dihapus.');
    }
}
