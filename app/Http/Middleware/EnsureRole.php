<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->guest(route('login'));
        }

        $user = Auth::user();

        if (! in_array($user->role, $roles, true)) {
            // Jika teknisi membuka halaman admin
            if ($user->isTechnician()) {
                return redirect()->route('technician.dashboard')->with('error', 'Akses ditolak: Anda login sebagai Teknisi Lapangan.');
            }

            // Jika admin membuka halaman teknisi
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')->with('error', 'Akses dialihkan ke Meja Pengelola Admin.');
            }

            abort(403, 'Akses tidak diizinkan untuk peran ini.');
        }

        return $next($request);
    }
}
