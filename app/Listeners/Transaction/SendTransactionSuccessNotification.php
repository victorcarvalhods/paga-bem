<?php

namespace App\Listeners\Transaction;

use App\Events\Transaction\TransactionCompleted;
use App\Services\Notifications\NotificationGatewayInterface;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTransactionSuccessNotification implements ShouldQueueAfterCommit
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
    public function handle(TransactionCompleted $event): void
    {
        $transaction = $event->transaction;

        $message = "Transaction of {$transaction->value} from wallet {$transaction->payer_id} to wallet {$transaction->payee_id} was successful.";

        $recipient = $transaction->payee->user->email;

        $this->notificationService->sendNotification($recipient, $message);
    }

    /**
     * Handle a job failure.
     */
    public function failed(TransactionCompleted $event, \Throwable $exception): void
    {
        Log::error('Failed to send Transaction success notification', [
            'Transaction_id' => $event->transaction->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
