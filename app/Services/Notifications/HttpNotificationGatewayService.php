<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Exceptions\Services\HttpNotificationServiceUnavailableException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;

class HttpNotificationGatewayService implements NotificationGatewayInterface
{
    private string $serviceUrl;
    
    public function __construct(private readonly Client $httpClient)
    {
        $this->serviceUrl = config('services.notification_service.url');
    }

    /**
     * Send a notification to a recipient.
     * @param string $recipient The recipient's identifier (e.g., email, phone number).
     * @param string $message The message content to be sent.
     */
    public function sendNotification(string $recipient, string $message): void
    {
        $notificationPayload = $this->createNotificationPayload($recipient, $message);

        try {
            $this->sendRequest($notificationPayload);
        } catch (\Exception $e) {
            $this->logError($recipient, $message, $e);

            if ($e->getCode() === 504) {
                throw new HttpNotificationServiceUnavailableException();
            }

            throw $e;
        }
    }

    /**
     * Create the payload for the notification service.
     *
     * @param string $recipient
     * @param string $message
     * @return array<string, string>
     */
    private function createNotificationPayload(string $recipient, string $message): array
    {
        return [
            'recipient' => $recipient,
            'message' => $message,
        ];
    }

    /**
     * Send the HTTP request to the notification service.
     *
     * @param array<string, mixed> $payload
     * @return void
     * @throws \Exception
     */
    private function sendRequest(array $payload): void
    {
        $this->httpClient->post($this->serviceUrl, [
            'json' => $payload,
            'verify' => false, // Disable SSL verification for local development
        ]);
    }

    /**
     * Log error details.
     *
     * @param string $recipient
     * @param string $message
     * @param \Exception $e
     * @return void
     */
    private function logError(string $recipient, string $message, \Exception $e): void
    {
        Log::error('Failed to send notification', [
            'recipient' => $recipient,
            'message' => $message,
            'error' => $e->getMessage(),
        ]);
    }
}