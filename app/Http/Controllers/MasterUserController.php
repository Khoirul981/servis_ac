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
            'phone' => ['required', 'string', 'unique:users,phone'],
            'role' => ['required', 'in:admin,technician'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama lengkap petugas wajib diisi.',
            'email.unique' => 'Alamat email sudah terdaftar.',
            'phone.unique' => 'Nomor WhatsApp sudah terdaftar.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', $request->input('phone'));
        if (str_starts_with($cleanPhone, '628')) {
            $cleanPhone = '08' . substr($cleanPhone, 3);
        }

        User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $cleanPhone,
            'role' => $request->input('role'),
            'password' => Hash::make($request->input('password')),
        ]);

        return back()->with('success', "Petugas baru ({$request->input('name')}) berhasil ditambahkan.");
    }
}
