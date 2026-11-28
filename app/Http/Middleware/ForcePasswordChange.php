<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     * Redirect users who must change their password to the change-password page.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && !session()->has('impersonated_by_superadmin')) {
            // Allow access to change-password, logout, and asset routes
            $allowedRoutes = ['password.change', 'password.change.update', 'logout'];

            if (!in_array($request->route()?->getName(), $allowedRoutes)) {
                return redirect()->route('password.change')
                    ->with('warning', 'Anda harus mengubah password sebelum melanjutkan.');
            }
        }

        return $next($request);
    }
}
