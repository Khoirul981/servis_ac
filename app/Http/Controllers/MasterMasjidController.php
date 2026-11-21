<?php

namespace App\Http\Controllers;

use App\Models\AcUnit;
use App\Models\Masjid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterMasjidController extends Controller
{
    /**
     * Daftar Master Masjid & Musholla.
     */
    public function index(Request $request): View
    {
        $search = trim($request->query('search', ''));

        $masjids = Masjid::withCount('acUnits')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%");
            })
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.masjids.index', compact('masjids', 'search'));
    }

    /**
     * Tambah Data Masjid Baru oleh Admin.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'pic_name' => ['required', 'string', 'max:150'],
            'pic_phone' => ['required', 'string', 'regex:/^(08|628|\+628)[0-9]{8,12}$/'],
        ], [
            'name.required' => 'Nama masjid / musholla wajib diisi.',
            'address.required' => 'Alamat masjid wajib diisi.',
            'pic_name.required' => 'Nama kontak pengurus / DKM wajib diisi.',
            'pic_phone.required' => 'Nomor WhatsApp pengurus wajib diisi.',
            'pic_phone.regex' => 'Format nomor WhatsApp tidak valid. Gunakan format contoh: 081234567890.',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $validated['pic_phone']);
        if (str_starts_with($cleanPhone, '628')) {
            $cleanPhone = '08' . substr($cleanPhone, 3);
        }

        $masjid = Masjid::create([
            'name' => trim($validated['name']),
            'address' => trim($validated['address']),
            'pic_name' => trim($validated['pic_name']),
            'pic_phone' => $cleanPhone,
        ]);

        return back()->with('success', "Data masjid {$masjid->name} berhasil ditambahkan.");
    }

    /**
     * Detail Masjid, Daftar Unit AC, dan Riwayat Servis.
     */
    public function show(int $id): View
    {
        $masjid = Masjid::with(['acUnits', 'orders.technician'])->findOrFail($id);

        return view('admin.masjids.show', compact('masjid'));
    }

    /**
     * Perbarui Data Profil Masjid.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $masjid = Masjid::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'pic_name' => ['required', 'string', 'max:150'],
            'pic_phone' => ['required', 'string', 'regex:/^(08|628|\+628)[0-9]{8,12}$/'],
        ], [
            'name.required' => 'Nama masjid / musholla wajib diisi.',
            'address.required' => 'Alamat masjid wajib diisi.',
            'pic_name.required' => 'Nama kontak pengurus / DKM wajib diisi.',
            'pic_phone.required' => 'Nomor WhatsApp pengurus wajib diisi.',
            'pic_phone.regex' => 'Format nomor WhatsApp tidak valid. Gunakan format contoh: 081234567890.',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $validated['pic_phone']);
        if (str_starts_with($cleanPhone, '628')) {
            $cleanPhone = '08' . substr($cleanPhone, 3);
        }

        $masjid->update([
            'name' => trim($validated['name']),
            'address' => trim($validated['address']),
            'pic_name' => trim($validated['pic_name']),
            'pic_phone' => $cleanPhone,
        ]);

        return back()->with('success', "Data masjid {$masjid->name} berhasil diperbarui.");
    }

    /**
     * Hapus Data Masjid.
     */
    public function destroy(int $id): RedirectResponse
    {
        $masjid = Masjid::findOrFail($id);
        $name = $masjid->name;
        $masjid->delete();

        return redirect()->route('admin.masjids.index')->with('success', "Data masjid {$name} berhasil dihapus.");
    }

    /**
     * Tambah Unit AC Baru ke Masjid.
     */
    public function storeAcUnit(Request $request, int $masjid_id): RedirectResponse
    {
        $masjid = Masjid::findOrFail($masjid_id);

        $validated = $request->validate([
            'room_name' => ['required', 'string', 'max:100'],
            'ac_type' => ['required', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:50'],
        ], [
            'room_name.required' => 'Nama ruangan / lokasi penempatan AC wajib diisi.',
            'ac_type.required' => 'Tipe / PK AC wajib diisi.',
        ]);

        AcUnit::create([
            'masjid_id' => $masjid->id,
            'room_name' => trim($validated['room_name']),
            'ac_type' => trim($validated['ac_type']),
            'brand' => ! empty($validated['brand']) ? trim($validated['brand']) : null,
        ]);

        return back()->with('success', 'Unit AC baru berhasil ditambahkan.');
    }

    /**
     * Perbarui Data Unit AC.
     */
    public function updateAcUnit(Request $request, int $id): RedirectResponse
    {
        $unit = AcUnit::findOrFail($id);

        $validated = $request->validate([
            'room_name' => ['required', 'string', 'max:100'],
            'ac_type' => ['required', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:50'],
        ], [
            'room_name.required' => 'Nama ruangan / lokasi penempatan AC wajib diisi.',
            'ac_type.required' => 'Tipe / PK AC wajib diisi.',
        ]);

        $unit->update([
            'room_name' => trim($validated['room_name']),
            'ac_type' => trim($validated['ac_type']),
            'brand' => ! empty($validated['brand']) ? trim($validated['brand']) : null,
        ]);

        return back()->with('success', 'Data unit AC berhasil diperbarui.');
    }

    /**
     * Hapus Unit AC.
     */
    public function destroyAcUnit(int $id): RedirectResponse
    {
        $unit = AcUnit::findOrFail($id);
        $unit->delete();

        return back()->with('success', 'Data unit AC berhasil dihapus.');
    }
}
