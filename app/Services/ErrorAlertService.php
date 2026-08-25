<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ErrorAlertService
{
    /**
     * Non-critical exception classes that should NEVER trigger alerts.
     */
    protected array $ignoredExceptions = [
        \Illuminate\Validation\ValidationException::class,
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class,
        \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException::class,
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
        \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Http\Exceptions\PostTooLargeException::class,
    ];

    /**
     * Send an error notification alert to Telegram and/or WhatsApp.
     */
    public function notify(Throwable $exception): void
    {
        if (!$this->shouldAlert($exception)) {
            return;
        }

        $signature = $this->getErrorSignature($exception);
        $cooldownMinutes = (int) config('services.alerts.cooldown_minutes', 5);

        // Anti-Spam / Debounce Check
        $cacheKey = 'err_alert_' . $signature;
        $counterKey = 'err_count_' . $signature;

        if (Cache::has($cacheKey)) {
            // Error already alerted recently, increment occurrence count
            try {
                Cache::increment($counterKey);
            } catch (Throwable $e) {}
            return;
        }

        // Put in cache to prevent duplicate alerts for the same error within cooldown
        try {
            Cache::put($cacheKey, true, now()->addMinutes($cooldownMinutes));
            Cache::put($counterKey, 1, now()->addMinutes($cooldownMinutes));
        } catch (Throwable $e) {}

        // Dispatch alert to enabled channels
        $this->sendTelegramAlert($exception);
        $this->sendWhatsAppAlert($exception);
    }

    /**
     * Determine if an exception warrants an alert.
     */
    public function shouldAlert(Throwable $exception): bool
    {
        if (!config('services.alerts.enabled', true)) {
            return false;
        }

        foreach ($this->ignoredExceptions as $ignored) {
            if ($exception instanceof $ignored) {
                return false;
            }
        }

        // Ignore 4xx HTTP exceptions
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
            if ($exception->getStatusCode() < 500) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate unique signature hash for an error.
     */
    protected function getErrorSignature(Throwable $exception): string
    {
        return md5(
            get_class($exception) . '|' .
            $exception->getFile() . '|' .
            $exception->getLine() . '|' .
            $exception->getMessage()
        );
    }

    /**
     * Send alert via Telegram Bot.
     */
    protected function sendTelegramAlert(Throwable $exception): void
    {
        $enabled = config('services.alerts.telegram.enabled', false);
        $botToken = config('services.alerts.telegram.bot_token');
        $chatId = config('services.alerts.telegram.chat_id');

        if (!$enabled || empty($botToken) || empty($chatId)) {
            return;
        }

        try {
            $user = auth()->user();
            $request = request();

            $url = $request ? $request->fullUrl() : 'CLI / Background';
            $method = $request ? $request->method() : 'CLI';
            $ip = $request ? $request->ip() : '127.0.0.1';
            $userInfo = $user ? "#{$user->id} {$user->name} ({$user->role})" : 'Guest / System';
            $appName = config('app.name', 'PembdaHUB');
            $appEnv = strtoupper(config('app.env', 'production'));
            $timestamp = now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s T');

            $message = "🚨 <b>[{$appName}] CRITICAL ERROR ALERT</b>\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━\n";
            $message .= "🌐 <b>Env:</b> <code>{$appEnv}</code>\n";
            $message .= "⏰ <b>Waktu:</b> <code>{$timestamp}</code>\n";
            $message .= "🛑 <b>Exception:</b> <code>" . htmlspecialchars(get_class($exception)) . "</code>\n";
            $message .= "💬 <b>Pesan:</b> <code>" . htmlspecialchars(mb_substr($exception->getMessage(), 0, 250)) . "</code>\n";
            $message .= "📍 <b>Lokasi:</b> <code>" . htmlspecialchars($this->cleanFilePath($exception->getFile())) . ":{$exception->getLine()}</code>\n";
            $message .= "🔗 <b>Request:</b> <code>{$method} {$url}</code>\n";
            $message .= "👤 <b>User:</b> <code>" . htmlspecialchars($userInfo) . "</code> (IP: <code>{$ip}</code>)\n";
            $message .= "━━━━━━━━━━━━━━━━━━━━━\n";
            $message .= "ℹ️ <i>Pemberitahuan otomatis dari sistem pemantau PembdaHUB.</i>";

            Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            Log::channel('daily')->info('Telegram error alert sent successfully');
        } catch (Throwable $e) {
            Log::channel('daily')->warning('Failed to send Telegram error alert: ' . $e->getMessage());
        }
    }

    /**
     * Send alert via WhatsApp Admin.
     */
    protected function sendWhatsAppAlert(Throwable $exception): void
    {
        $enabled = config('services.alerts.whatsapp.enabled', false);
        $adminPhone = config('services.alerts.whatsapp.admin_phone');

        if (!$enabled || empty($adminPhone)) {
            return;
        }

        try {
            $user = auth()->user();
            $request = request();

            $url = $request ? $request->fullUrl() : 'CLI / Background';
            $method = $request ? $request->method() : 'CLI';
            $userInfo = $user ? "#{$user->id} {$user->name} ({$user->role})" : 'Guest / System';
            $appName = config('app.name', 'PembdaHUB');
            $appEnv = strtoupper(config('app.env', 'production'));
            $timestamp = now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s');

            $msg = "🚨 *[{$appName}] CRITICAL ERROR ALERT*\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "🌐 *Environment:* {$appEnv}\n";
            $msg .= "⏰ *Waktu:* {$timestamp} WIB\n";
            $msg .= "🛑 *Error:* " . get_class($exception) . "\n";
            $msg .= "💬 *Pesan:* " . mb_substr($exception->getMessage(), 0, 200) . "\n";
            $msg .= "📍 *File:* " . $this->cleanFilePath($exception->getFile()) . " (Baris {$exception->getLine()})\n";
            $msg .= "🔗 *Route:* {$method} {$url}\n";
            $msg .= "👤 *User:* {$userInfo}\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "⚠️ Segera periksa log server untuk detail exception.";

            app(WhatsAppService::class)->sendMessage($adminPhone, $msg);
            Log::channel('daily')->info('WhatsApp error alert sent successfully');
        } catch (Throwable $e) {
            Log::channel('daily')->warning('Failed to send WhatsApp error alert: ' . $e->getMessage());
        }
    }

    /**
     * Strip absolute server base path for cleaner alert display.
     */
    protected function cleanFilePath(string $path): string
    {
        $base = base_path();
        return str_replace([$base . DIRECTORY_SEPARATOR, $base . '/'], '', $path);
    }
}
