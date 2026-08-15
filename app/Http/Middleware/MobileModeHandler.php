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

        // 2. If request is to /m/* or /m, auto-remember mobile mode
        if ($request->is('m/*') || $request->is('m')) {
            session(['is_mobile_app' => true]);
            session()->forget('prefer_desktop');
            cookie()->queue('app_mode', 'mobile', 60 * 24 * 365);
        }

        return $next($request);
    }
}
