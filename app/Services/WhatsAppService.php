<?php

namespace App\Services;

use App\Contracts\WhatsAppServiceInterface;
use App\Exceptions\TemplateNotFoundException;
use App\Jobs\SendWhatsAppMessage;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

class WhatsAppService implements WhatsAppServiceInterface
{
    protected string $apiUrl;
    protected string $apiToken;
    protected bool $enabled;
    protected int $timeout;
    protected string $activeProvider;
    protected string $providerLabel;

    public function __construct()
    {
        $dbEnabled = Setting::getValue('wa_enabled');
        $this->enabled = ($dbEnabled !== null) ? (bool)$dbEnabled : config('services.whatsapp.enabled', true);
        $this->timeout = config('services.whatsapp.timeout', 15);

        // Tentukan provider aktif: prioritas DB Setting > .env
        $this->activeProvider = $this->resolveActiveProvider();

        // Ambil config sesuai provider aktif
        $providerConfig = config("services.whatsapp.providers.{$this->activeProvider}");
        $dbToken = Setting::getValue("wa_{$this->activeProvider}_token");
        $dbUrl = Setting::getValue("wa_{$this->activeProvider}_url");

        if ($providerConfig) {
            $this->apiUrl = !empty($dbUrl) ? rtrim($dbUrl, '/') : rtrim($providerConfig['api_url'] ?? '', '/');
            $this->apiToken = !empty($dbToken) ? $dbToken : ($providerConfig['api_token'] ?? '');
            $this->providerLabel = $providerConfig['label'] ?? $this->activeProvider;
        } else {
            // Fallback ke config lama (backward compatibility)
            $this->apiUrl = !empty($dbUrl) ? rtrim($dbUrl, '/') : rtrim(config('services.whatsapp.api_url', ''), '/');
            $this->apiToken = !empty($dbToken) ? $dbToken : config('services.whatsapp.api_token', '');
            $this->providerLabel = $this->activeProvider;
        }
    }

    /**
     * Resolve the active WhatsApp provider.
     * Priority: Database Setting > .env WHATSAPP_PROVIDER
     */
    protected function resolveActiveProvider(): string
    {
        try {
            $dbOverride = Setting::getValue('wa_active_provider');
            if ($dbOverride && in_array($dbOverride, ['fonnte', 'selfhosted'])) {
                return $dbOverride;
            }
        } catch (\Throwable $e) {
            // Table might not exist yet during migrations
        }

        return config('services.whatsapp.active_provider', 'fonnte');
    }

    /**
     * Get the currently active provider name.
     */
    public function getActiveProvider(): string
    {
        return $this->activeProvider;
    }

    /**
     * Get the human-readable label of the active provider.
     */
    public function getProviderLabel(): string
    {
        return $this->providerLabel;
    }

    /**
     * Get info about all available providers and which is active.
     */
    public function getProvidersInfo(): array
    {
        $providers = config('services.whatsapp.providers', []);
        $result = [];

        foreach ($providers as $key => $providerConfig) {
            $dbToken = Setting::getValue("wa_{$key}_token");
            $dbUrl = Setting::getValue("wa_{$key}_url");
            $token = !empty($dbToken) ? $dbToken : ($providerConfig['api_token'] ?? '');
            $url = !empty($dbUrl) ? $dbUrl : ($providerConfig['api_url'] ?? '');

            $result[$key] = [
                'key' => $key,
                'label' => $providerConfig['label'] ?? $key,
                'api_url' => $url,
                'api_token' => $token,
                'is_active' => ($key === $this->activeProvider),
                'has_token' => !empty($token),
                'is_custom_token' => !empty($dbToken),
            ];
        }

        return $result;
    }

    /**
     * Switch the active provider (persists to database).
     */
    public static function switchProvider(string $provider): array
    {
        $validProviders = array_keys(config('services.whatsapp.providers', []));

        if (!in_array($provider, $validProviders)) {
            return [
                'success' => false,
                'message' => "Provider '{$provider}' tidak valid. Pilihan: " . implode(', ', $validProviders),
            ];
        }

        Setting::setValue('wa_active_provider', $provider, 'string', 'whatsapp');

        $label = config("services.whatsapp.providers.{$provider}.label", $provider);

        Log::channel('whatsapp')->info("WhatsApp provider switched to: {$provider} ({$label})");

        return [
            'success' => true,
            'provider' => $provider,
            'label' => $label,
            'message' => "Provider WhatsApp berhasil diganti ke: {$label}",
        ];
    }

    /**
     * Send WhatsApp message.
     */
    public function sendMessage(string $phone, string $message, array $options = []): array
    {
        if (!$this->enabled) {
            $err = 'Layanan WhatsApp sedang nonaktif. Pastikan WHATSAPP_ENABLED=true di .env atau aktifkan pada Pengaturan.';
            Log::channel('whatsapp')->info('WhatsApp disabled. Message not sent', [
                'phone' => $phone,
                'message' => mb_substr($message, 0, 100),
            ]);

            return [
                'success' => false,
                'message' => $err,
                'error'   => $err,
                'mode'    => 'disabled',
            ];
        }

        $phone = $this->normalizePhoneNumber($phone);

        // Global System Footer
        $systemFooter = "\n\n---\n🤖 _Pesan ini dikirimkan secara otomatis oleh Sistem PembdaHUB Perguruan Pembda._";
        if (!str_contains($message, 'Sistem PembdaHUB') && !str_contains($message, 'PembdaHUB Executive')) {
            $message .= $systemFooter;
        }

        try {
            $data = [
                'target' => $phone,
                'message' => $message,
            ];

            if (isset($options['image'])) {
                $data['url'] = $options['image'];
            }

            if (isset($options['document'])) {
                $data['filename'] = $options['document'];
            }

            /** @var Response $response */
            $response = Http::timeout($this->timeout)
                ->connectTimeout(5)
                ->withHeaders([
                    'Authorization' => $this->apiToken,
                ])
                ->post($this->apiUrl . '/send', $data);

            $result = $response->json();
            $isHttpSuccess = $response->successful();
            $isDelivered = false;
            $failureReason = null;

            if ($this->activeProvider === 'fonnte') {
                // Fonnte returns HTTP 200 even on failures with JSON status: false
                if ($isHttpSuccess && isset($result['status']) && $result['status'] === true) {
                    $isDelivered = true;
                } else {
                    $isDelivered = false;
                    $failureReason = $result['reason'] ?? $result['message'] ?? 'Fonnte melaporkan status gagal (kemungkinan perangkat WhatsApp di Fonnte terputus/disconnected atau kuota habis)';
                }
            } elseif ($this->activeProvider === 'selfhosted') {
                if ($isHttpSuccess && (($result['status'] ?? '') === 'success' || ($result['success'] ?? false) === true)) {
                    $isDelivered = true;
                } else {
                    $isDelivered = false;
                    $failureReason = $result['message'] ?? $result['error'] ?? 'Node Baileys Gateway gagal memproses pesan';
                }
            } else {
                $isDelivered = $isHttpSuccess;
            }

            Log::channel('whatsapp')->info('WhatsApp message sent', [
                'provider' => $this->activeProvider,
                'phone' => $phone,
                'status' => $isDelivered ? 'success' : 'failed',
                'status_code' => $response->status(),
                'response' => $result,
                'failure_reason' => $failureReason,
            ]);

            return [
                'success' => $isDelivered,
                'provider' => $this->activeProvider,
                'response' => $result,
                'status_code' => $response->status(),
                'error' => $failureReason,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::channel('whatsapp')->error('WhatsApp connection timeout', [
                'provider' => $this->activeProvider,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'provider' => $this->activeProvider,
                'error' => "Connection timeout ({$this->providerLabel}): " . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('WhatsApp send failed', [
                'provider' => $this->activeProvider,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'provider' => $this->activeProvider,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send message using a named template.
     *
     * @throws TemplateNotFoundException
     */
    public function sendTemplate(string $phone, string $templateName, array $variables = []): array
    {
        $message = $this->renderTemplate($templateName, $variables);
        return $this->sendMessage($phone, $message);
    }

    /**
     * Render a message template from config.
     *
     * @throws TemplateNotFoundException
     */
    private function renderTemplate(string $templateName, array $variables): string
    {
        $settingKey = 'wa_tpl_' . str_replace('.', '_', $templateName);
        $customTemplate = Setting::getValue($settingKey, null);

        if ($customTemplate) {
            $template = $customTemplate;
        } else {
            $templates = config('whatsapp-templates');
            if (!isset($templates[$templateName])) {
                throw new TemplateNotFoundException($templateName);
            }
            $template = $templates[$templateName];
        }

        foreach ($variables as $key => $value) {
            $template = str_replace("{{$key}}", (string) $value, $template);
        }

        return $template;
    }

    /**
     * Normalize phone number to 62xxx format.
     */
    private function normalizePhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Check if the service is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Send bulk messages by dispatching individual jobs (non-blocking).
     */
    public function sendBulk(array $recipients, int $delay = 2): array
    {
        $dispatched = 0;

        foreach ($recipients as $index => $recipient) {
            SendWhatsAppMessage::dispatch(
                $recipient['phone'],
                $recipient['message'],
                $recipient['options'] ?? [],
                'bulk-send'
            )->delay(now()->addSeconds($index * $delay));

            $dispatched++;
        }

        Log::channel('whatsapp')->info('Bulk WhatsApp messages queued', [
            'provider' => $this->activeProvider,
            'total' => $dispatched,
            'delay_between' => $delay . 's',
        ]);

        return [
            'success' => true,
            'provider' => $this->activeProvider,
            'dispatched' => $dispatched,
            'message' => "{$dispatched} messages queued for delivery via {$this->providerLabel}",
        ];
    }

    /**
     * Get account info from the WhatsApp API.
     */
    public function getAccountInfo(): array
    {
        if (!$this->enabled) {
            return ['success' => false, 'message' => 'Service disabled'];
        }

        if (empty($this->apiToken) && $this->activeProvider === 'fonnte') {
            return ['success' => false, 'message' => 'Token Fonnte belum diisi'];
        }

        try {
            /** @var Response $response */
            if ($this->activeProvider === 'fonnte') {
                // Fonnte API requires POST method for /device
                $response = Http::timeout($this->timeout)
                    ->connectTimeout(5)
                    ->withHeaders([
                        'Authorization' => $this->apiToken,
                    ])
                    ->post($this->apiUrl . '/device');
            } else {
                // Selfhosted Baileys
                $response = Http::timeout($this->timeout)
                    ->connectTimeout(5)
                    ->withHeaders([
                        'Authorization' => $this->apiToken,
                    ])
                    ->get($this->apiUrl . '/device');
            }

            $jsonData = $response->json();
            $isSuccessful = $response->successful();

            if ($this->activeProvider === 'fonnte') {
                $deviceStatus = $jsonData['device_status'] ?? '';
                $isSuccessful = $isSuccessful && (($jsonData['status'] ?? false) === true || $deviceStatus === 'connect');
            } elseif ($this->activeProvider === 'selfhosted') {
                $isSuccessful = $isSuccessful && (($jsonData['status'] ?? '') === 'connected');
            }

            return [
                'success' => (bool)$isSuccessful,
                'provider' => $this->activeProvider,
                'label' => $this->providerLabel,
                'data' => $jsonData,
            ];
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('WhatsApp getAccountInfo failed', [
                'provider' => $this->activeProvider,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'provider' => $this->activeProvider,
                'error' => $e->getMessage(),
            ];
        }
    }
}
