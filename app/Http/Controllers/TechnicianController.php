<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPhoto;
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

        // Tugas yang sudah selesai dikerjakan teknisi (menunggu bayar atau selesai)
        $completedOrders = Order::with('masjid')
            ->where('technician_id', $technicianId)
            ->whereIn('status', [Order::STATUS_MENUNGGU_BAYAR, Order::STATUS_SELESAI])
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
        $order = Order::with(['masjid', 'photos'])->findOrFail($id);

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
     * Tombol 2: Unggah Foto Bukti Servis (Sebelum / Sesudah / Sparepart).
     */
    public function uploadPhoto(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403);
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
     * Tombol 3: Tambah Biaya Sparepart / Tindakan Tambahan.
     */
    public function addSparepart(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'additional_amount' => ['required', 'numeric', 'min:0'],
            'additional_notes' => ['required', 'string', 'max:1000'],
        ], [
            'additional_amount.required' => 'Nominal biaya tambahan wajib diisi.',
            'additional_notes.required' => 'Rincian suku cadang / tindakan tambahan wajib diisi.',
        ]);

        $order->update([
            'additional_amount' => $request->input('additional_amount'),
            'additional_notes' => $request->input('additional_notes'),
        ]);

        return back()->with('success', 'Biaya tambahan sparepart berhasil dicatat.');
    }

    /**
     * Tombol 4: Pekerjaan Selesai (dikerjakan -> menunggu_bayar).
     * Wajib ada minimal 1 foto bukti terunggah.
     */
    public function completeWork(int $id): RedirectResponse
    {
        $order = Order::with('photos')->findOrFail($id);

        if ($order->technician_id !== Auth::id()) {
            abort(403);
        }

        // Validasi: Wajib ada minimal 1 foto bukti sebelum bisa diselesaikan
        if ($order->photos->isEmpty()) {
            return back()->with('error', 'Pekerjaan belum bisa diselesaikan: Mohon unggah minimal 1 foto bukti sebelum atau sesudah servis!');
        }

        $order->update([
            'status' => Order::STATUS_MENUNGGU_BAYAR,
        ]);

        // Perbarui tanggal servis terakhir di master masjid
        $order->masjid->update([
            'last_service_date' => now()->toDateString(),
        ]);

        return redirect()->route('technician.dashboard')
            ->with('success', "Alhamdulillah! Pekerjaan servis di {$order->masjid->name} telah selesai. Pesanan diteruskan ke Admin untuk penagihan.");
    }
}
