<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InventoryAccessMiddleware
{
    /**
     * Handle an incoming request.
     * Hanya Yayasan dan Akun yang Ditunjuk yang berhak mengelola PembdaINVENTORY.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk mengakses PembdaINVENTORY.');
        }

        $user = auth()->user();

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda tidak aktif.');
        }

        if (!$user->canManageInventory()) {
            abort(403, 'Akses Terbatas: Menu PembdaINVENTORY hanya dapat dikelola oleh Yayasan dan Akun yang Ditunjuk.');
        }

        return $next($request);
    }
}
