<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPhoto;
use App\Models\User;
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
            'menunggu_bayar' => Order::where('status', Order::STATUS_MENUNGGU_BAYAR)->count(),
            'selesai' => Order::whereIn('status', [Order::STATUS_SELESAI, Order::STATUS_BATAL])->count(),
        ];

        // Query pesanan sesuai tab aktif
        $ordersQuery = Order::with(['masjid', 'technician', 'photos']);

        match ($activeTab) {
            'berjalan' => $ordersQuery->whereIn('status', [Order::STATUS_DIJADWALKAN, Order::STATUS_DIKERJAKAN])
                ->orderByRaw("CASE WHEN status = 'dikerjakan' THEN 1 ELSE 2 END")
                ->orderBy('service_date'),
            Order::STATUS_MENUNGGU_BAYAR => $ordersQuery->where('status', Order::STATUS_MENUNGGU_BAYAR)
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
     * Penugasan Teknisi & Penetapan Jadwal.
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

        $order->update([
            'technician_id' => $request->input('technician_id'),
            'service_date' => $request->input('service_date'),
            'service_time' => $request->input('service_time'),
            'status' => Order::STATUS_DIJADWALKAN,
        ]);

        return back()->with('success', "Pesanan {$order->order_number} berhasil dijadwalkan dan ditugaskan ke {$order->technician->name}.");
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
        $order = Order::with(['masjid', 'technician', 'photos'])->findOrFail($id);

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
