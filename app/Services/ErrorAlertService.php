<?php

namespace App\Services;

use App\Models\Setting;
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
        $cooldownMinutes = (int) $this->getCooldownMinutes();

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
        if (!$this->isEnabled()) {
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
     * Check if master error alerting is enabled (DB setting priority > .env)
     */
    public function isEnabled(): bool
    {
        try {
            $dbSetting = Setting::getValue('error_alerts_enabled');
            if ($dbSetting !== null) {
                return (bool) $dbSetting;
            }
        } catch (Throwable $e) {}

        return (bool) config('services.alerts.enabled', true);
    }

    /**
     * Get cooldown duration in minutes
     */
    public function getCooldownMinutes(): int
    {
        try {
            $dbCooldown = Setting::getValue('error_alert_cooldown_minutes');
            if ($dbCooldown !== null && is_numeric($dbCooldown)) {
                return (int) $dbCooldown;
            }
        } catch (Throwable $e) {}

        return (int) config('services.alerts.cooldown_minutes', 5);
    }

    /**
     * Get alert configuration settings for display in Admin Panel
     */
    public function getAlertConfig(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'cooldown_minutes' => $this->getCooldownMinutes(),
            'whatsapp' => [
                'enabled' => (bool) (Setting::getValue('wa_alert_enabled') ?? config('services.alerts.whatsapp.enabled', false)),
                'admin_phone' => Setting::getValue('wa_alert_phone') ?: config('services.alerts.whatsapp.admin_phone', ''),
            ],
            'telegram' => [
                'enabled' => (bool) (Setting::getValue('telegram_alert_enabled') ?? config('services.alerts.telegram.enabled', false)),
                'bot_token' => Setting::getValue('telegram_alert_bot_token') ?: config('services.alerts.telegram.bot_token', ''),
                'chat_id' => Setting::getValue('telegram_alert_chat_id') ?: config('services.alerts.telegram.chat_id', ''),
            ],
        ];
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
    public function sendTelegramAlert(Throwable $exception): array
    {
        $enabled = (bool) (Setting::getValue('telegram_alert_enabled') ?? config('services.alerts.telegram.enabled', false));
        $botToken = Setting::getValue('telegram_alert_bot_token') ?: config('services.alerts.telegram.bot_token');
        $chatId = Setting::getValue('telegram_alert_chat_id') ?: config('services.alerts.telegram.chat_id');

        if (!$enabled || empty($botToken) || empty($chatId)) {
            return ['success' => false, 'message' => 'Telegram alert belum diaktifkan atau konfigurasi token/chat ID belum lengkap'];
        }

        try {
            $user = auth()->user();
            $request = request();

            $url = $request ? $request->fullUrl() : 'CLI / Background Task';
            $method = $request ? $request->method() : 'CLI';
            $ip = $request ? $request->ip() : '127.0.0.1';
            $userInfo = $user ? "#{$user->id} {$user->name} ({$user->role})" : 'Guest / Sistem Otomatis';
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

            $response = Http::timeout(6)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            $isSuccess = $response->successful() && ($response->json('ok') === true);

            if ($isSuccess) {
                Log::channel('daily')->info('Telegram error alert sent successfully');
                return ['success' => true, 'message' => 'Test alert Telegram berhasil terkirim ke Chat ID: ' . $chatId];
            } else {
                $errorDesc = $response->json('description') ?? 'Gagal mengirim pesan Telegram';
                return ['success' => false, 'message' => 'Telegram API Error: ' . $errorDesc];
            }
        } catch (Throwable $e) {
            Log::channel('daily')->warning('Failed to send Telegram error alert: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Koneksi Telegram Error: ' . $e->getMessage()];
        }
    }

    /**
     * Send alert via WhatsApp Admin.
     */
    public function sendWhatsAppAlert(Throwable $exception): array
    {
        $enabled = (bool) (Setting::getValue('wa_alert_enabled') ?? config('services.alerts.whatsapp.enabled', false));
        $adminPhone = Setting::getValue('wa_alert_phone') ?: config('services.alerts.whatsapp.admin_phone');

        if (!$enabled || empty($adminPhone)) {
            return ['success' => false, 'message' => 'WhatsApp alert belum diaktifkan atau nomor HP Super Admin belum diisi'];
        }

        try {
            $user = auth()->user();
            $request = request();

            $url = $request ? $request->fullUrl() : 'CLI / Background Task';
            $method = $request ? $request->method() : 'CLI';
            $userInfo = $user ? "#{$user->id} {$user->name} ({$user->role})" : 'Guest / Sistem Otomatis';
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
            $msg .= "⚠️ Segera periksa server log atau dashboard pemantau PembdaHUB.";

            $waService = app(WhatsAppService::class);
            $result = $waService->sendMessage($adminPhone, $msg);

            if (!empty($result['success'])) {
                Log::channel('daily')->info('WhatsApp error alert sent successfully');
                return ['success' => true, 'message' => "Test alert WhatsApp berhasil dikirim ke nomor {$adminPhone} via " . $waService->getProviderLabel()];
            } else {
                $err = $result['error'] ?? $result['response']['message'] ?? 'Gagal mengirim pesan WhatsApp';
                return ['success' => false, 'message' => "WhatsApp Alert Error: {$err}"];
            }
        } catch (Throwable $e) {
            Log::channel('daily')->warning('Failed to send WhatsApp error alert: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Koneksi WhatsApp Alert Error: ' . $e->getMessage()];
        }
    }

    /**
     * Send direct test error alert to verified channels
     */
    public function sendTestAlert(string $channel = 'all'): array
    {
        $testException = new \RuntimeException('[TEST ALERT] Ini adalah simulasi peringatan error dari Sistem Monitoring PembdaHUB. Sistem bekerja dengan normal!');

        $results = [];

        if ($channel === 'all' || $channel === 'whatsapp') {
            $results['whatsapp'] = $this->sendWhatsAppAlert($testException);
        }

        if ($channel === 'all' || $channel === 'telegram') {
            $results['telegram'] = $this->sendTelegramAlert($testException);
        }

        return $results;
    }

    /**
     * Parse recent error logs from Laravel log storage
     */
    public function getRecentErrorLogs(int $limit = 20): array
    {
        $logs = [];
        $logPath = storage_path('logs/laravel.log');

        // Check if file exists, if not check daily log files
        if (!file_exists($logPath)) {
            $dailyFiles = glob(storage_path('logs/laravel-*.log'));
            if (!empty($dailyFiles)) {
                rsort($dailyFiles);
                $logPath = $dailyFiles[0];
            }
        }

        if (!file_exists($logPath) || filesize($logPath) === 0) {
            return [];
        }

        try {
            // Read last 200KB of log file for performance
            $maxBytes = 200 * 1024;
            $fileSize = filesize($logPath);
            $fp = fopen($logPath, 'r');

            if ($fileSize > $maxBytes) {
                fseek($fp, $fileSize - $maxBytes);
                fgets($fp); // discard partial line
            }

            $content = fread($fp, $maxBytes);
            fclose($fp);

            // Pattern to match Laravel log entries
            $pattern = '/\[(\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2}[\.\d\s\+\-:]*)\]\s+([a-zA-Z0-9_\-]+)\.([A-Z]+):\s+(.*?)(?=\n\[\d{4}-\d{2}-\d{2}|$)/s';
            preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

            if (!empty($matches)) {
                $matches = array_reverse($matches); // newest first
                $count = 0;

                foreach ($matches as $match) {
                    $level = strtoupper($match[3] ?? 'INFO');
                    // Filter to ERROR, CRITICAL, EMERGENCY, ALERT
                    if (in_array($level, ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT'])) {
                        $fullMessage = trim($match[4] ?? '');
                        $firstLine = strtok($fullMessage, "\n");
                        
                        $logs[] = [
                            'timestamp' => $match[1] ?? '',
                            'environment' => $match[2] ?? 'production',
                            'level' => $level,
                            'short_message' => mb_substr($firstLine, 0, 180),
                            'full_message' => mb_substr($fullMessage, 0, 1000),
                        ];

                        $count++;
                        if ($count >= $limit) {
                            break;
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            Log::warning('Error parsing log file: ' . $e->getMessage());
        }

        return $logs;
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
