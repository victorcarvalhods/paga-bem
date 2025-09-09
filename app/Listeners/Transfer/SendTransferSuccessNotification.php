<?php

namespace App\Listeners\Transfer;

use App\Events\Transfer\TransferCompleted;
use App\Services\Notifications\NotificationGatewayInterface;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTransferSuccessNotification implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    protected $tries = 3;

    /**
     * The number of seconds to wait before retrying the queued listener.
     *
     * @var int
     */
    protected $backoff = 3;

    /**
     * Create the event listener.
     */
    public function __construct(private readonly NotificationGatewayInterface $notificationService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(TransferCompleted $event): void
    {
        $transfer = $event->transfer;

        $message = "Transfer of {$transfer->value} from wallet {$transfer->payer_id} to wallet {$transfer->payee_id} was successful.";

        $recipient = $transfer->payee->user->email;

        $this->notificationService->sendNotification($recipient, $message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(TransferCompleted $event, \Throwable $exception): void
    {
        Log::error('Failed to send transfer success notification', [
            'transfer_id' => $event->transfer->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
