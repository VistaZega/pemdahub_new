<?php

namespace App\Contracts;

/**
 * Interface for WhatsApp messaging service.
 * 
 * Enables dependency injection and easier testing via mocks/fakes.
 */
interface WhatsAppServiceInterface
{
    /**
     * Send a plain WhatsApp message.
     */
    public function sendMessage(string $phone, string $message, array $options = []): array;

    /**
     * Send a WhatsApp message using a template.
     */
    public function sendTemplate(string $phone, string $templateName, array $variables = []): array;

    /**
     * Send bulk messages.
     */
    public function sendBulk(array $recipients, int $delay = 12): array;

    /**
     * Check if the service is enabled.
     */
    public function isEnabled(): bool;

    /**
     * Get account info from the WhatsApp API.
     */
    public function getAccountInfo(): array;
}
