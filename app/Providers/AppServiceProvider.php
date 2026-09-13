<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use App\Events\ModelActivityLogged;
use App\Listeners\LogModelActivity;
use App\Models\Employee;
use App\Models\AcademicYear;
use App\Observers\EmployeeObserver;
use App\Observers\AcademicYearObserver;
use App\Repositories\StudentRepository;
use App\Repositories\GradeRepository;
use App\Repositories\AttendanceRepository;
use App\Services\StudentService;
use App\Services\GradeService;
use App\Contracts\WhatsAppServiceInterface;
use App\Services\WhatsAppService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }

        // Register Repository bindings
        $this->app->singleton(StudentRepository::class, function ($app) {
            return new StudentRepository();
        });

        $this->app->singleton(GradeRepository::class, function ($app) {
            return new GradeRepository();
        });

        $this->app->singleton(AttendanceRepository::class, function ($app) {
            return new AttendanceRepository();
        });

        // Register Service bindings
        $this->app->singleton(StudentService::class, function ($app) {
            return new StudentService($app->make(StudentRepository::class));
        });

        $this->app->singleton(GradeService::class, function ($app) {
            return new GradeService($app->make(GradeRepository::class));
        });

        // Register WhatsApp service interface binding
        $this->app->singleton(WhatsAppServiceInterface::class, WhatsAppService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-create storage symlink if missing (useful for Hostinger Auto-Deploy)
        if (!file_exists(public_path('storage'))) {
            try { app('files')->link(storage_path('app/public'), public_path('storage')); } catch (\Exception $e) {}
        }

        // Fix CORS & Mixed Content: Force HTTPS in production or via perguruanpembda.com
        $isHttps = config('app.env') === 'production' 
            || str_contains(request()->getHost(), 'perguruanpembda.com')
            || (request()->hasHeader('X-Forwarded-Proto') && request()->header('X-Forwarded-Proto') === 'https');

        if ($isHttps) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            $rootUrl = str_replace('http://', 'https://', request()->root());
            \Illuminate\Support\Facades\URL::forceRootUrl($rootUrl);
            config(['app.asset_url' => $rootUrl]);
        } else {
            \Illuminate\Support\Facades\URL::forceRootUrl(request()->root());
            config(['app.asset_url' => request()->root()]);
        }
        // Define default password rules application-wide
        Password::defaults(function () {
            return Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers();
        });

        // Configure rate limiters
        $this->configureRateLimiting();

        // Register event listeners
        Event::listen(
            ModelActivityLogged::class,
            LogModelActivity::class,
        );

        // Register Observers
        Employee::observe(EmployeeObserver::class);
        AcademicYear::observe(AcademicYearObserver::class);
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Web rate limiter: 1000 requests per minute per IP
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(1000)
                ->by($request->ip())
                ->response(function () {
                    return response()->view('errors.429', [], 429);
                });
        });

        // API rate limiter: 60 requests per minute per user or IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        // Stricter limit for sensitive operations (login already has custom rate limit)
        RateLimiter::for('sensitive', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
