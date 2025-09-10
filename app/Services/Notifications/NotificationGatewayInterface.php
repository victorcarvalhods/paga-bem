<?php

namespace App\Services\Notifications;

use Illuminate\Container\Attributes\Bind;

#[Bind(HttpNotificationGatewayService::class)] //This can be removed if binding is done in a service provider
interface NotificationGatewayInterface
{
    /**
     * Send a notification to a recipient.
     * @param string|null $recipient The recipient's identifier (e.g., email, phone number).
     * @param string $message The message content to be sent.
     */
    public function sendNotification(?string $recipient, string $message): void;
}
