<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\CirclePointService;
use Symfony\Component\HttpFoundation\Response;

class EnsureSpaceIsActive
{
    /**
     * Handle an incoming request.
     * Mencegah akses ke PembdaSPACE setelah masa transisi 7 hari berakhir,
     * dan mengarahkan pengguna secara otomatis ke PembdaCIRCLE.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (CirclePointService::isSpaceClosed()) {
            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'PembdaSPACE telah resmi ditutup setelah masa transisi 7 hari. Seluruh aktivitas dan komunitas kini berpusat di PembdaCIRCLE.',
                    'redirect' => route('circle.feed'),
                ], 410);
            }

            return redirect()->route('circle.feed')->with('info', 'PembdaSPACE telah resmi ditutup setelah masa transisi 7 hari. Seluruh ruang diskusi, tanya jawab, dan komunitas kini berpusat di PembdaCIRCLE.');
        }

        return $next($request);
    }
}
