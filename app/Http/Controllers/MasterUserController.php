<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MasterUserController extends Controller
{
    /**
     * Daftar Pengguna (Teknisi & Admin).
     */
    public function index(): View
    {
        $users = User::orderByRaw("CASE WHEN role = 'admin' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Tambah Akun Teknisi / Admin Baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^(08|628|\+628)[0-9]{8,12}$/', 'unique:users,phone'],
            'role' => ['required', 'in:admin,technician'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama lengkap petugas wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email sudah terdaftar pada pengguna lain.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.unique' => 'Nomor WhatsApp sudah terdaftar pada pengguna lain.',
            'phone.regex' => 'Format nomor WhatsApp tidak valid. Gunakan format contoh: 081234567890.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $request->input('phone'));
        if (str_starts_with($cleanPhone, '628')) {
            $cleanPhone = '08' . substr($cleanPhone, 3);
        }

        User::create([
            'name' => trim($request->input('name')),
            'email' => trim($request->input('email')),
            'phone' => $cleanPhone,
            'role' => $request->input('role'),
            'password' => Hash::make($request->input('password')),
        ]);

        return back()->with('success', "Petugas baru ({$request->input('name')}) berhasil ditambahkan.");
    }

    /**
     * Perbarui Data Akun Petugas.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'string', 'regex:/^(08|628|\+628)[0-9]{8,12}$/', 'unique:users,phone,' . $user->id],
            'role' => ['required', 'in:admin,technician'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama lengkap petugas wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email sudah terdaftar pada pengguna lain.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.unique' => 'Nomor WhatsApp sudah terdaftar pada pengguna lain.',
            'phone.regex' => 'Format nomor WhatsApp tidak valid. Gunakan format contoh: 081234567890.',
            'password.min' => 'Kata sandi minimal 6 karakter jika ingin diubah.',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $validated['phone']);
        if (str_starts_with($cleanPhone, '628')) {
            $cleanPhone = '08' . substr($cleanPhone, 3);
        }

        $payload = [
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'phone' => $cleanPhone,
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return back()->with('success', "Data akun petugas {$user->name} berhasil diperbarui.");
    }

    /**
     * Hapus Akun Petugas.
     */
    public function destroy(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Akun petugas {$name} berhasil dihapus.");
    }
}
