<?php

namespace Tests\Feature\Events;

use App\Events\Transaction\TransactionCompleted;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransactionCompletedTest extends TestCase
{
    #[Test]
    public function Transaction_completed_event_is_dispatched()
    {
        Event::fake();

        $transaction = Transaction::factory()->create();

        event(new TransactionCompleted($transaction));

        Event::assertDispatched(TransactionCompleted::class, function ($event) use ($transaction) {
            return $event->transaction->id === $transaction->id;
        });
    }

    #[Test]
    public function event_is_dispatched_after_database_commit()
    {
        Event::fake();

        $transaction = Transaction::factory()->create();

        DB::transaction(function () use ($transaction) {
            event(new TransactionCompleted($transaction));
        });

        Event::assertDispatched(TransactionCompleted::class);
    }
}
