<?php

namespace App\Services;

use App\Contracts\WhatsAppServiceInterface;
use App\Exceptions\TemplateNotFoundException;
use App\Jobs\SendWhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

class WhatsAppService implements WhatsAppServiceInterface
{
    protected string $apiUrl;
    protected string $apiToken;
    protected bool $enabled;
    protected int $timeout;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url', '');
        $this->apiToken = config('services.whatsapp.api_token', '');
        $this->enabled = config('services.whatsapp.enabled', false);
        $this->timeout = config('services.whatsapp.timeout', 15);
    }

    /**
     * Send WhatsApp message.
     */
    public function sendMessage(string $phone, string $message, array $options = []): array
    {
        if (!$this->enabled) {
            Log::channel('whatsapp')->info('WhatsApp disabled. Message not sent', [
                'phone' => $phone,
                'message' => mb_substr($message, 0, 100),
            ]);

            return [
                'success' => false,
                'message' => 'WhatsApp service is disabled',
                'mode' => 'disabled',
            ];
        }

        $phone = $this->normalizePhoneNumber($phone);

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

            Log::channel('whatsapp')->info('WhatsApp message sent', [
                'phone' => $phone,
                'status' => $response->successful() ? 'success' : 'failed',
                'status_code' => $response->status(),
                'response' => $result,
            ]);

            return [
                'success' => $response->successful(),
                'response' => $result,
                'status_code' => $response->status(),
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::channel('whatsapp')->error('WhatsApp connection timeout', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Connection timeout: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('WhatsApp send failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
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
        $customTemplate = \App\Models\Setting::getValue($settingKey, null);

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
            'total' => $dispatched,
            'delay_between' => $delay . 's',
        ]);

        return [
            'success' => true,
            'dispatched' => $dispatched,
            'message' => "{$dispatched} messages queued for delivery",
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

        try {
            /** @var Response $response */
            $response = Http::timeout($this->timeout)
                ->connectTimeout(5)
                ->withHeaders([
                    'Authorization' => $this->apiToken,
                ])
                ->get($this->apiUrl . '/device');

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('WhatsApp getAccountInfo failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
