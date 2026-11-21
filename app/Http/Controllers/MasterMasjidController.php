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
     * Detail Masjid, Daftar Unit AC, dan Riwayat Servis.
     */
    public function show(int $id): View
    {
        $masjid = Masjid::with(['acUnits', 'orders.technician'])->findOrFail($id);

        return view('admin.masjids.show', compact('masjid'));
    }

    /**
     * Tambah Unit AC Baru ke Masjid.
     */
    public function storeAcUnit(Request $request, int $masjid_id): RedirectResponse
    {
        $masjid = Masjid::findOrFail($masjid_id);

        $request->validate([
            'room_name' => ['required', 'string', 'max:100'],
            'ac_type' => ['required', 'string', 'max:50'],
            'brand' => ['nullable', 'string', 'max:50'],
        ], [
            'room_name.required' => 'Nama ruangan / lokasi penempatan AC wajib diisi.',
            'ac_type.required' => 'Tipe / PK AC wajib diisi.',
        ]);

        AcUnit::create([
            'masjid_id' => $masjid->id,
            'room_name' => $request->input('room_name'),
            'ac_type' => $request->input('ac_type'),
            'brand' => $request->input('brand'),
        ]);

        return back()->with('success', 'Unit AC baru berhasil ditambahkan.');
    }
}
