<?php

namespace App\Http\Controllers;

use App\Models\AcUnit;
use App\Models\Order;
use App\Models\OrderPhoto;
use App\Models\OrderSparepart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TechnicianController extends Controller
{
    /**
     * Beranda Tugas Teknisi (Mobile View).
     */
    public function index(): View
    {
        $technicianId = Auth::id();

        // Tugas aktif hari ini (dijadwalkan atau sedang dikerjakan)
        $activeOrders = Order::with('masjid')
            ->where('technician_id', $technicianId)
            ->whereIn('status', [Order::STATUS_DIJADWALKAN, Order::STATUS_DIKERJAKAN])
            ->orderByRaw("CASE WHEN status = 'dikerjakan' THEN 1 WHEN status = 'dijadwalkan' THEN 2 ELSE 3 END")
            ->orderBy('service_date')
            ->get();

        // Tugas yang sudah selesai dikerjakan teknisi (pekerjaan selesai, menunggu verifikasi, menunggu bayar atau selesai)
        $completedOrders = Order::with('masjid')
            ->where('technician_id', $technicianId)
            ->whereIn('status', [
                Order::STATUS_PEKERJAAN_SELESAI,
                Order::STATUS_MENUNGGU_VERIFIKASI,
                Order::STATUS_MENUNGGU_BAYAR,
                Order::STATUS_SELESAI,
            ])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return view('technician.dashboard', compact('activeOrders', 'completedOrders'));
    }

    /**
     * Lembar Kerja Lapangan untuk Pesanan Tertentu.
     */
    public function show(int $id): View
    {
        $order = Order::with(['masjid.acUnits', 'photos', 'spareparts', 'serviceItems'])->findOrFail($id);

        // Pastikan teknisi hanya bisa mengakses order miliknya
        if ($order->technician_id !== Auth::id()) {
            abort(403, 'Anda tidak ditugaskan untuk pesanan ini.');
        }

        // Tautan navigasi Google Maps
        $encodedAddress = urlencode($order->masjid->name . ', ' . $order->masjid->address);
        $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$encodedAddress}";

        // Tautan WhatsApp ke DKM/Marbot
        $cleanPhone = preg_replace('/[^0-9]/', '', $order->masjid->pic_phone);
        if (str_starts_with($cleanPhone, '08')) {
            $cleanPhone = '628' . substr($cleanPhone, 2);
        }
        $waGreeting = urlencode("Assalamualaikum Pak {$order->masjid->pic_name}, saya " . Auth::user()->name . " dari tim Servis AC Masjid. Terkait jadwal servis di {$order->masjid->name}, saya sedang menuju ke lokasi.");
        $whatsappUrl = "https://wa.me/{$cleanPhone}?text={$waGreeting}";

        return view('technician.show', compact('order', 'mapsUrl', 'whatsappUrl'));
    }

    /**
     * Tombol 1: Mulai Bekerja di Lokasi (dijadwalkan -> dikerjakan).
     */
    public function startWork(int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status === Order::STATUS_DIJADWALKAN) {
            $order->update([
                'status' => Order::STATUS_DIKERJAKAN,
            ]);
        }

        return back()->with('success', 'Status diperbarui: Anda sedang mengerjakan servis di lokasi.');
    }

    /**
     * Batalkan Status Tiba (dikerjakan -> dijadwalkan).
     */
    public function revertWork(int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403);
        }

        if ($order->isWorkCompleted()) {
            return back()->with('error', 'Pekerjaan servis telah selesai dikonfirmasi dan data terkunci.');
        }

        if ($order->status === Order::STATUS_DIKERJAKAN) {
            $order->update([
                'status' => Order::STATUS_DIJADWALKAN,
            ]);
        }

        return back()->with('success', 'Status pengerjaan dibatalkan. Pesanan kembali ke status dijadwalkan.');
    }

    /**
     * Tombol 2: Unggah Foto Bukti Servis (Sebelum / Sesudah / Sparepart).
     */
    public function uploadPhoto(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403);
        }

        if ($order->isWorkCompleted()) {
            return back()->with('error', 'Pekerjaan servis telah selesai dikonfirmasi dan data foto terkunci.');
        }

        $request->validate([
            'photo' => ['required', 'image', 'max:5120'], // Max 5MB (di browser sudah dikompres < 1MB)
            'photo_type' => ['required', 'in:sebelum,sesudah,sparepart'],
            'caption' => ['nullable', 'string', 'max:255'],
        ], [
            'photo.required' => 'Foto bukti servis wajib dipilih atau diambil melalui kamera.',
            'photo.image' => 'File harus berupa gambar foto.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $file = $request->file('photo');
        $path = $file->store('order_photos', 'public');

        OrderPhoto::create([
            'order_id' => $order->id,
            'photo_url' => $path,
            'photo_type' => $request->input('photo_type'),
            'caption' => $request->input('caption'),
        ]);

        return back()->with('success', 'Foto bukti berhasil diunggah!');
    }

    /**
     * Perbarui Kategori & Keterangan Foto Bukti Servis.
     */
    public function updatePhoto(Request $request, int $photo_id): RedirectResponse
    {
        $photo = OrderPhoto::with('order')->findOrFail($photo_id);

        if ($photo->order->technician_id !== Auth::id()) {
            abort(403);
        }

        if ($photo->order->isWorkCompleted()) {
            return back()->with('error', 'Pekerjaan servis telah selesai dikonfirmasi dan data foto terkunci.');
        }

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
     * Hapus Foto Bukti Servis.
     */
    public function destroyPhoto(int $photo_id): RedirectResponse
    {
        $photo = OrderPhoto::with('order')->findOrFail($photo_id);

        if ($photo->order->technician_id !== Auth::id()) {
            abort(403);
        }

        if ($photo->order->isWorkCompleted()) {
            return back()->with('error', 'Pekerjaan servis telah selesai dikonfirmasi dan data foto terkunci.');
        }

        if ($photo->photo_url && Storage::disk('public')->exists($photo->photo_url)) {
            Storage::disk('public')->delete($photo->photo_url);
        }

        $photo->delete();

        return back()->with('success', 'Foto bukti servis berhasil dihapus.');
    }

    /**
     * Tombol 3: Tambah Suku Cadang / Freon / Tindakan Tambahan.
     */
    public function addSparepart(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403);
        }

        if ($order->isWorkCompleted()) {
            return back()->with('error', 'Pekerjaan servis telah selesai dikonfirmasi dan data suku cadang terkunci.');
        }

        if ($request->has('item_name')) {
            $validated = $request->validate([
                'item_name' => ['required', 'string', 'max:255'],
                'quantity' => ['required', 'integer', 'min:1', 'max:999'],
                'price' => ['required', 'numeric', 'min:0'],
            ], [
                'item_name.required' => 'Nama suku cadang / tindakan wajib diisi.',
                'quantity.min' => 'Jumlah minimal 1 unit.',
                'price.required' => 'Harga satuan wajib diisi.',
            ]);

            $subtotal = (float) $validated['price'] * (int) $validated['quantity'];

            OrderSparepart::create([
                'order_id' => $order->id,
                'item_name' => trim($validated['item_name']),
                'quantity' => (int) $validated['quantity'],
                'price' => (float) $validated['price'],
                'subtotal' => $subtotal,
            ]);

            $order->recalculateSpareparts();

            return back()->with('success', "Suku cadang '{$validated['item_name']}' berhasil ditambahkan.");
        }

        $request->validate([
            'additional_amount' => ['required', 'numeric', 'min:0'],
            'additional_notes' => ['required', 'string', 'max:1000'],
        ], [
            'additional_amount.required' => 'Nominal biaya tambahan wajib diisi.',
            'additional_notes.required' => 'Rincian suku cadang / tindakan tambahan wajib diisi.',
        ]);

        $order->spareparts()->delete();
        OrderSparepart::create([
            'order_id' => $order->id,
            'item_name' => trim((string) $request->input('additional_notes')),
            'quantity' => 1,
            'price' => (float) $request->input('additional_amount'),
            'subtotal' => (float) $request->input('additional_amount'),
        ]);

        $order->update([
            'additional_amount' => $request->input('additional_amount'),
            'additional_notes' => $request->input('additional_notes'),
        ]);

        return back()->with('success', 'Biaya tambahan sparepart berhasil dicatat.');
    }

    /**
     * Perbarui Rincian Suku Cadang Tertentu.
     */
    public function updateSparepart(Request $request, int $sparepart_id): RedirectResponse
    {
        $sparepart = OrderSparepart::with('order')->findOrFail($sparepart_id);

        if ($sparepart->order->technician_id !== Auth::id()) {
            abort(403);
        }

        if ($sparepart->order->isWorkCompleted()) {
            return back()->with('error', 'Pekerjaan servis telah selesai dikonfirmasi dan data suku cadang terkunci.');
        }

        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'price' => ['required', 'numeric', 'min:0'],
        ], [
            'item_name.required' => 'Nama suku cadang / tindakan wajib diisi.',
            'quantity.min' => 'Jumlah minimal 1 unit.',
            'price.required' => 'Harga satuan wajib diisi.',
        ]);

        $subtotal = (float) $validated['price'] * (int) $validated['quantity'];

        $sparepart->update([
            'item_name' => trim($validated['item_name']),
            'quantity' => (int) $validated['quantity'],
            'price' => (float) $validated['price'],
            'subtotal' => $subtotal,
        ]);

        $sparepart->order->recalculateSpareparts();

        return back()->with('success', 'Data suku cadang berhasil diperbarui.');
    }

    /**
     * Hapus Satu Item Suku Cadang.
     */
    public function destroySparepartItem(int $sparepart_id): RedirectResponse
    {
        $sparepart = OrderSparepart::with('order')->findOrFail($sparepart_id);

        if ($sparepart->order->technician_id !== Auth::id()) {
            abort(403);
        }

        if ($sparepart->order->isWorkCompleted()) {
            return back()->with('error', 'Pekerjaan servis telah selesai dikonfirmasi dan data suku cadang terkunci.');
        }

        $order = $sparepart->order;
        $sparepart->delete();
        $order->recalculateSpareparts();

        return back()->with('success', 'Item suku cadang berhasil dihapus.');
    }

    /**
     * Hapus Seluruh Biaya Tambahan Sparepart / Freon.
     */
    public function removeSparepart(int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403);
        }

        if ($order->isWorkCompleted()) {
            return back()->with('error', 'Pekerjaan servis telah selesai dikonfirmasi dan data suku cadang terkunci.');
        }

        $order->spareparts()->delete();
        $order->update([
            'additional_amount' => 0,
            'additional_notes' => null,
        ]);

        return back()->with('success', 'Catatan biaya tambahan sparepart berhasil dihapus.');
    }

    /**
     * Tombol 4: Konfirmasi Selesai Pekerjaan Servis (dikerjakan -> pekerjaan_selesai).
     * Wajib ada minimal 1 foto bukti terunggah di Langkah 2.
     * Setelah ini, data pengerjaan fisik TERKUNCI dan teknisi dialihkan ke halaman pelaporan pembayaran terpisah.
     */
    public function completeWork(Request $request, int $id): RedirectResponse
    {
        $order = Order::with('photos')->findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403);
        }

        if ($order->isWorkCompleted()) {
            return redirect()->route('technician.order.payment', $order->id);
        }

        // Validasi: Wajib ada minimal 1 foto bukti servis sebelum bisa diselesaikan
        if ($order->photos->isEmpty()) {
            return back()->with('error', 'Pekerjaan belum bisa diselesaikan: Mohon unggah minimal 1 foto bukti sebelum atau sesudah servis!');
        }

        $order->update([
            'status' => Order::STATUS_PEKERJAAN_SELESAI,
        ]);

        // Perbarui tanggal servis terakhir di master masjid
        $order->masjid->update([
            'last_service_date' => now()->toDateString(),
        ]);

        return redirect()->route('technician.order.payment', $order->id)
            ->with('success', "Alhamdulillah! Pekerjaan servis di {$order->masjid->name} telah selesai dikonfirmasi dan data pengerjaan terkunci. Silakan unggah bukti transfer/pembayaran dari pengurus DKM.");
    }

    /**
     * Halaman Khusus Pelaporan & Upload Bukti Pembayaran oleh Teknisi.
     */
    public function paymentView(int $id): View
    {
        $order = Order::with(['masjid', 'photos', 'spareparts', 'serviceItems'])->findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403, 'Anda tidak ditugaskan untuk pesanan ini.');
        }

        return view('technician.payment', compact('order'));
    }

    /**
     * Unggah Foto Bukti Pembayaran dari DKM ke Admin (status -> menunggu_verifikasi).
     */
    public function uploadPaymentProof(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:transfer,qris,cash'],
            'payment_proof' => ['nullable', 'image', 'max:5120'],
        ], [
            'payment_method.required' => 'Pilih metode pembayaran yang digunakan pengurus DKM.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'payment_proof.image' => 'File bukti transfer/pembayaran harus berupa gambar foto.',
            'payment_proof.max' => 'Ukuran foto bukti transfer maksimal 5 MB.',
        ]);

        $payload = [
            'payment_method' => $validated['payment_method'],
            'status' => Order::STATUS_MENUNGGU_VERIFIKASI,
        ];

        if ($request->hasFile('payment_proof')) {
            if ($order->payment_proof && Storage::disk('public')->exists($order->payment_proof)) {
                Storage::disk('public')->delete($order->payment_proof);
            }
            $payload['payment_proof'] = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        $order->update($payload);

        return redirect()->route('technician.order.payment', $order->id)
            ->with('success', 'Foto bukti pembayaran berhasil dikirim ke Admin. Status pesanan kini menunggu verifikasi Admin.');
    }

    /**
     * Tambah Unit AC Baru untuk Masjid yang Ditugaskan ke Teknisi Ini.
     */
    public function storeAcUnit(Request $request, int $id): RedirectResponse
    {
        $order = Order::with('masjid')->findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola unit AC masjid ini.');
        }

        $validated = $request->validate([
            'room_name' => ['required', 'string', 'max:100'],
            'ac_type' => ['required', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:50'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ], [
            'room_name.required' => 'Nama ruangan / lokasi penempatan AC wajib diisi.',
            'ac_type.required' => 'Tipe / PK AC wajib diisi.',
            'quantity.min' => 'Jumlah unit minimal 1.',
        ]);

        AcUnit::create([
            'masjid_id' => $order->masjid_id,
            'room_name' => trim($validated['room_name']),
            'ac_type' => trim($validated['ac_type']),
            'brand' => ! empty($validated['brand']) ? trim($validated['brand']) : null,
            'quantity' => (int) ($validated['quantity'] ?? 1),
        ]);

        return back()->with('success', 'Data unit AC baru untuk masjid ini berhasil ditambahkan.');
    }

    /**
     * Perbarui Data Unit AC Masjid yang Ditugaskan ke Teknisi Ini.
     */
    public function updateAcUnit(Request $request, int $id, int $unit_id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola unit AC masjid ini.');
        }

        $unit = AcUnit::where('masjid_id', $order->masjid_id)->findOrFail($unit_id);

        $validated = $request->validate([
            'room_name' => ['required', 'string', 'max:100'],
            'ac_type' => ['required', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:50'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ], [
            'room_name.required' => 'Nama ruangan / lokasi penempatan AC wajib diisi.',
            'ac_type.required' => 'Tipe / PK AC wajib diisi.',
            'quantity.min' => 'Jumlah unit minimal 1.',
        ]);

        $unit->update([
            'room_name' => trim($validated['room_name']),
            'ac_type' => trim($validated['ac_type']),
            'brand' => ! empty($validated['brand']) ? trim($validated['brand']) : null,
            'quantity' => (int) ($validated['quantity'] ?? $unit->quantity ?? 1),
        ]);

        return back()->with('success', 'Data unit AC berhasil diperbarui.');
    }

    /**
     * Hapus Unit AC Masjid yang Ditugaskan ke Teknisi Ini.
     */
    public function destroyAcUnit(int $id, int $unit_id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola unit AC masjid ini.');
        }

        $unit = AcUnit::where('masjid_id', $order->masjid_id)->findOrFail($unit_id);
        $unit->delete();

        return back()->with('success', 'Data unit AC berhasil dihapus.');
    }
}
