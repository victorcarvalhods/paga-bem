<?php

namespace Tests\Unit\Events;

use App\Events\Transfer\TransferCompleted;
use App\Models\Transfer;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TransferCompletedTest extends TestCase
{
    #[Test]
    public function it_should_assert_event_implements_correct_interfaces_and_traits(): void
    {
        $transfer = new Transfer();
        $event = new TransferCompleted($transfer);

        // Verifica se implementa ShouldDispatchAfterCommit
        $this->assertInstanceOf(ShouldDispatchAfterCommit::class, $event);

        // Verifica se usa as traits corretas
        $traits = class_uses(TransferCompleted::class);
        $this->assertContains(Dispatchable::class, $traits);
        $this->assertContains(SerializesModels::class, $traits);
    }

    #[Test]
    public function it_should_test_event_stores_transfer_correctly(): void
    {
        $transfer = new Transfer();
        $transfer->id = 1;
        $transfer->amount = 100.00;

        $event = new TransferCompleted($transfer);

        $this->assertSame($transfer, $event->transfer);
        $this->assertEquals(1, $event->transfer->id);
        $this->assertEquals(100.00, $event->transfer->amount);
    }

    #[Test]
    public function it_should_test_transfer_property_is_readonly(): void
    {
        $transfer = new Transfer();
        $event = new TransferCompleted($transfer);

        $reflection = new \ReflectionClass($event);
        $property = $reflection->getProperty('transfer');

        $this->assertTrue($property->isReadOnly());
    }
}
