<?php

namespace Tests\Unit\Events;

use App\Events\Transaction\TransactionCompleted;
use App\Models\Transaction;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TransactionCompletedTest extends TestCase
{
    #[Test]
    public function it_should_assert_event_implements_correct_interfaces_and_traits(): void
    {
        $transaction = new Transaction();
        $event = new TransactionCompleted($transaction);

        // Verifica se implementa ShouldDispatchAfterCommit
        $this->assertInstanceOf(ShouldDispatchAfterCommit::class, $event);

        // Verifica se usa as traits corretas
        $traits = class_uses(TransactionCompleted::class);
        $this->assertContains(Dispatchable::class, $traits);
        $this->assertContains(SerializesModels::class, $traits);
    }

    #[Test]
    public function it_should_test_event_stores_Transaction_correctly(): void
    {
        $transaction = new Transaction();
        $transaction->id = 1;
        $transaction->amount = 100.00;

        $event = new TransactionCompleted($transaction);

        $this->assertSame($transaction, $event->transaction);
        $this->assertEquals(1, $event->transaction->id);
        $this->assertEquals(100.00, $event->transaction->amount);
    }

    #[Test]
    public function it_should_test_Transaction_property_is_readonly(): void
    {
        $transaction = new Transaction();
        $event = new TransactionCompleted($transaction);

        $reflection = new \ReflectionClass($event);
        $property = $reflection->getProperty('transaction');

        $this->assertTrue($property->isReadOnly());
    }
}
