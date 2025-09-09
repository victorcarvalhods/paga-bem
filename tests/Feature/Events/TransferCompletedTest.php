<?php

namespace Tests\Feature\Events;

use App\Events\Transfer\TransferCompleted;
use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransferCompletedTest extends TestCase
{
    #[Test]
    public function transfer_completed_event_is_dispatched()
    {
        Event::fake();

        $transfer = Transfer::factory()->create();

        event(new TransferCompleted($transfer));

        Event::assertDispatched(TransferCompleted::class, function ($event) use ($transfer) {
            return $event->transfer->id === $transfer->id;
        });
    }

    #[Test]
    public function event_is_dispatched_after_database_commit()
    {
        Event::fake();

        $transfer = Transfer::factory()->create();

        DB::transaction(function () use ($transfer) {
            event(new TransferCompleted($transfer));
        });

        Event::assertDispatched(TransferCompleted::class);
    }
}
