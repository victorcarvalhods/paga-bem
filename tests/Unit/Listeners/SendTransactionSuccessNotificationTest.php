<?php

namespace Tests\Unit\Listeners;

use App\Events\Transaction\TransactionCompleted;
use App\Listeners\Transaction\SendTransactionSuccessNotification;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Notifications\NotificationGatewayInterface;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Exception;
use RuntimeException;
use InvalidArgumentException;

class SendTransactionSuccessNotificationTest extends TestCase
{
    private MockInterface&NotificationGatewayInterface $notificationService;
    private SendTransactionSuccessNotification $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = Mockery::mock(NotificationGatewayInterface::class);
        $this->listener = new SendTransactionSuccessNotification($this->notificationService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_implements_correct_interfaces_and_traits(): void
    {
        $this->assertInstanceOf(ShouldQueueAfterCommit::class, $this->listener);
        $traits = class_uses(SendTransactionSuccessNotification::class);
        $this->assertContains(InteractsWithQueue::class, $traits);
    }

    #[Test]
    public function it_has_correct_properties(): void
    {
        $reflection = new ReflectionClass($this->listener);
        $triesProperty = $reflection->getProperty('tries');
        $this->assertEquals(3, $triesProperty->getValue($this->listener));
        $backoffProperty = $reflection->getProperty('backoff');
        $this->assertEquals(3, $backoffProperty->getValue($this->listener));
    }

    #[Test]
    public function it_sends_notification_successfully_when_handling_Transaction_completed_event(): void
    {
        $payeeUser = Mockery::mock(User::class);
        $payeeUser->shouldReceive('getAttribute')->with('email')->andReturn('payee@example.com');

        $payerWallet = Mockery::mock(Wallet::class);
        $payerWallet->shouldReceive('getAttribute')->with('id')->andReturn(1);

        $payeeWallet = Mockery::mock(Wallet::class);
        $payeeWallet->shouldReceive('getAttribute')->with('id')->andReturn(2);
        $payeeWallet->shouldReceive('getAttribute')->with('user')->andReturn($payeeUser);

        $transaction = Mockery::mock(Transaction::class);
        $transaction->shouldReceive('getAttribute')->with('value')->andReturn(100.50);
        $transaction->shouldReceive('getAttribute')->with('payer_id')->andReturn($payerWallet->id);
        $transaction->shouldReceive('getAttribute')->with('payee_id')->andReturn($payeeWallet->id);
        $transaction->shouldReceive('getAttribute')->with('payee')->andReturn($payeeWallet);

        $event = new TransactionCompleted($transaction);

        $expectedMessage = "Transaction of 100.5 from wallet 1 to wallet 2 was successful.";
        $expectedRecipient = 'payee@example.com';

        $this->notificationService
            ->shouldReceive('sendNotification')
            ->once()
            ->with($expectedRecipient, $expectedMessage)
            ->andReturnNull();

        $this->listener->handle($event);
        $this->assertTrue(true);
    }

    #[Test]
    public function it_handles_different_Transaction_amounts_correctly(): void
    {
        $payeeUser = Mockery::mock(User::class);
        $payeeUser->shouldReceive('getAttribute')->with('email')->andReturn('recipient@test.com');

        $payerWallet = Mockery::mock(Wallet::class);
        $payerWallet->shouldReceive('getAttribute')->with('id')->andReturn(10);

        $payeeWallet = Mockery::mock(Wallet::class);
        $payeeWallet->shouldReceive('getAttribute')->with('id')->andReturn(20);
        $payeeWallet->shouldReceive('getAttribute')->with('user')->andReturn($payeeUser);

        $testCases = [
            ['value' => 50.00, 'expectedAmount' => '50'],
            ['value' => 100.99, 'expectedAmount' => '100.99'],
            ['value' => 1000.00, 'expectedAmount' => '1000'],
        ];

        foreach ($testCases as $case) {
            $transaction = Mockery::mock(Transaction::class);
            $transaction->shouldReceive('getAttribute')->with('payer_id')->andReturn($payerWallet->id);
            $transaction->shouldReceive('getAttribute')->with('payee_id')->andReturn($payeeWallet->id);
            $transaction->shouldReceive('getAttribute')->with('value')->andReturn($case['value']);
            $transaction->shouldReceive('getAttribute')->with('payee')->andReturn($payeeWallet);

            $event = new TransactionCompleted($transaction);
            $expectedMessage = "Transaction of {$case['expectedAmount']} from wallet 10 to wallet 20 was successful.";

            $this->notificationService
                ->shouldReceive('sendNotification')
                ->once()
                ->with('recipient@test.com', $expectedMessage);

            $this->listener->handle($event);
        }
        $this->assertTrue(true);
    }

    #[Test]
    public function it_logs_error_correctly_when_failed_method_is_called(): void
    {
        Log::shouldReceive('error')
            ->once()
            ->with(
                'Failed to send Transaction success notification',
                ['Transaction_id' => 123, 'error' => 'Test exception message']
            );

        $transaction = Mockery::mock(Transaction::class);
        $transaction->shouldReceive('getAttribute')->with('id')->andReturn(123);

        $event = new TransactionCompleted($transaction);
        $exception = new Exception('Test exception message');

        $this->listener->failed($event, $exception);
        $this->assertTrue(true);
    }

    #[Test]
    public function it_handles_different_exception_types_in_failed_method(): void
    {
        $transaction = Mockery::mock(Transaction::class);
        $transaction->shouldReceive('getAttribute')->with('id')->andReturn(456);

        $event = new TransactionCompleted($transaction);

        $exceptions = [
            new RuntimeException('Runtime error'),
            new InvalidArgumentException('Invalid argument'),
            new Exception('Generic exception'),
        ];

        foreach ($exceptions as $exception) {
            Log::shouldReceive('error')
                ->once()
                ->with(
                    'Failed to send Transaction success notification',
                    ['Transaction_id' => 456, 'error' => $exception->getMessage()]
                );

            $this->listener->failed($event, $exception);
        }

        $this->assertTrue(true);
    }
}
