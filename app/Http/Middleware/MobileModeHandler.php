<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MobileModeHandler
{
    /**
     * Handle an incoming request for Mobile App Mode persistence.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = $request->userAgent() ?? '';
        $secChUaMobile = $request->header('sec-ch-ua-mobile') === '?1';
        $isMobilePhone = $secChUaMobile || (bool) preg_match('/Android.*Mobile|iPhone|iPod|BlackBerry|IEMobile|Opera Mini|webOS|Windows Phone/i', $userAgent);

        // 1. Handle explicit mode switches
        if ($request->has('switch_mode')) {
            $mode = $request->query('switch_mode');
            if ($mode === 'desktop') {
                session(['prefer_desktop' => true]);
                session()->forget('is_mobile_app');
                cookie()->queue(cookie()->forget('app_mode'));
            } elseif ($mode === 'mobile') {
                session(['is_mobile_app' => true]);
                session()->forget('prefer_desktop');
                cookie()->queue('app_mode', 'mobile', 60 * 24 * 365);
            }
        }

        // 2. If request is to /m/* or /m on mobile phone, auto-remember mobile mode
        if ($request->is('m/*') || $request->is('m')) {
            session(['is_mobile_app' => true]);
            session()->forget('prefer_desktop');
        } elseif (!$isMobilePhone && !$request->is('m/*')) {
            // Pada PC / Desktop saat membuka rute desktop normal: pastikan mode desktop aktif
            if ($request->hasCookie('app_mode')) {
                cookie()->queue(cookie()->forget('app_mode'));
            }
            session()->forget('is_mobile_app');
        }

        return $next($request);
    }
}
