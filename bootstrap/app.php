<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->registered(function ($app) {
        // Jika di folder domains (Hostinger), paksa gunakan public_html
        // Perbaikan: gunakan base_path untuk menghitung path yang lebih akurat
        if (str_contains(base_path(), 'domains')) {
            $app->usePublicPath(path: realpath(base_path().'/../'));
        }
    })

    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('api/v1')
                ->middleware('api')
                ->group(base_path('routes/api_v1.php'));
            
            Route::middleware('web')->group(function () {
                require __DIR__.'/../routes/admin.php';
                require __DIR__.'/../routes/guru.php';
                require __DIR__.'/../routes/siswa.php';
                require __DIR__.'/../routes/treasurer.php';
                require __DIR__.'/../routes/orangtua.php';
                require __DIR__.'/../routes/yayasan.php';
                require __DIR__.'/../routes/mobile.php';
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'treasurer' => \App\Http\Middleware\TreasurerMiddleware::class,
            'yayasan' => \App\Http\Middleware\YayasanMiddleware::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'feature' => \App\Http\Middleware\CheckFeature::class,
        ]);

        // Smart Guest Redirection for Mobile App & PWA
        $middleware->redirectGuestsTo(function (Request $request) {
            $referer = $request->header('referer') ?? '';
            $userAgent = $request->userAgent() ?? '';
            $secChUaMobile = $request->header('sec-ch-ua-mobile') === '?1';

            $isMobileRequest = $request->is('m/*') 
                || $request->is('m')
                || str_contains($referer, '/m/') 
                || $request->cookie('app_mode') === 'mobile' 
                || session('is_mobile_app')
                || $secChUaMobile 
                || preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);

            if ($isMobileRequest && !session('prefer_desktop')) {
                return route('mobile.login');
            }

            return route('login');
        });

        // Register MobileModeHandler and PreserveFilters in web group so sessions are active
        $middleware->web(append: [
            \App\Http\Middleware\MobileModeHandler::class,
            \App\Http\Middleware\PreserveFilters::class,
        ]);

        // Security headers middleware
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Performance monitoring middleware (logs slow requests)
        $middleware->append(\App\Http\Middleware\PerformanceMonitor::class);

        // Force password change middleware (checks must_change_password flag)
        $middleware->append(\App\Http\Middleware\ForcePasswordChange::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Ukuran berkas yang diunggah melebihi batas maksimal server.'], 413);
            }
            return redirect()->back()->with('error', 'Ukuran berkas yang diunggah terlalu besar. Batas maksimal pengungahan adalah 10MB.');
        });

        // Render custom error pages for HTTP exceptions
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            $status = $e->getStatusCode();
            $view = "errors.{$status}";

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: __("HTTP Error {$status}"),
                    'status' => $status,
                ], $status);
            }

            if (view()->exists($view)) {
                return response()->view($view, [
                    'exception' => $e,
                    'message' => $e->getMessage(),
                ], $status);
            }

            // Fallback to generic error page
            if (view()->exists('errors.generic')) {
                return response()->view('errors.generic', [
                    'exception' => $e,
                    'message' => $e->getMessage(),
                    'code' => $status,
                ], $status);
            }

            return null; // Let Laravel handle it
        });

        // Report exceptions (log critical errors & dispatch alerts to Telegram/WhatsApp)
        $exceptions->report(function (\Throwable $e) {
            if ($e instanceof \Illuminate\Database\QueryException) {
                \Illuminate\Support\Facades\Log::channel('daily')->critical('Database Error', [
                    'message' => $e->getMessage(),
                    'sql' => method_exists($e, 'getSql') ? $e->getSql() : 'N/A',
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            // Real-time Critical Error Alert Notification (Telegram / WhatsApp)
            try {
                app(\App\Services\ErrorAlertService::class)->notify($e);
            } catch (\Throwable $alertEx) {
                // Never allow alerting failure to affect normal error handling
            }
        });
    })->create();
