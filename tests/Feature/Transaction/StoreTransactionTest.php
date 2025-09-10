<?php

namespace Tests\Feature\Transaction;

use App\Events\Transaction\TransactionCompleted;
use App\Listeners\Transaction\SendTransactionSuccessNotification;
use App\Models\Wallet;
use App\Services\Transaction\AuthorizationGatewayInterface;
use App\Services\Notifications\NotificationGatewayInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreTransactionTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mock(AuthorizationGatewayInterface::class, function ($mock) {
            $mock->shouldReceive('authorize')
                 ->andReturn(true)
                 ->byDefault();
        });

        $this->mock(NotificationGatewayInterface::class, function ($mock) {
            $mock->shouldReceive('sendNotification')
                 ->andReturnNull()
                 ->byDefault();
        });
    }

    #[Test]
    public function it_should_store_Transaction(): void
    {
        $payer = Wallet::factory()->user()->create(['balance' => 1000]);
        $payee = Wallet::factory()->create(['balance' => 500]);

        $value = $this->faker->randomFloat(2, 10, 1000);

        $response = $this->postJson(route('transactions.store'), [
            'value' => $value,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ]);
    }

    #[Test]
    public function it_should_not_allow_Transaction_when_payer_has_insufficient_balance(): void
    {
        $payer = Wallet::factory()->user()->create(['balance' => 100]);
        $payee = Wallet::factory()->create(['balance' => 500]);

        $value = 200;

        $response = $this->postJson(route('transactions.store'), [
            'value' => $value,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $this->assertEquals([
            'error' => true,
            'code' => 409,
            'message' => 'Insufficient balance to complete this transaction.',
        ], $response->json());

        $this->assertDatabaseMissing('transactions', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ]);

        $this->assertEquals(100, $payer->balance);
        $this->assertEquals(500, $payee->balance);
    }

    #[Test]
    public function it_should_not_allow_Transaction_when_payer_is_merchant(): void
    {
        $payer = Wallet::factory()->merchant()->create(['balance' => 1000]);
        $payee = Wallet::factory()->create(['balance' => 500]);

        $value = 200;

        $response = $this->postJson(route('transactions.store'), [
            'value' => $value,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $this->assertEquals([
            'error' => true,
            'code' => 403,
            'message' => 'Merchant accounts cannot initiate transactions.',
        ], $response->json());

        $this->assertDatabaseMissing('transactions', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ]);

        $this->assertEquals(1000, $payer->balance);
        $this->assertEquals(500, $payee->balance);
    }

    #[Test]
    public function it_should_not_allow_Transaction_when_authorization_service_fails(): void
    {
        $this->mock(AuthorizationGatewayInterface::class, function ($mock) {
            $mock->shouldReceive('authorize')
                 ->once()
                 ->andReturn(false);
        });

        $payer = Wallet::factory()->user()->create(['balance' => 1000]);
        $payee = Wallet::factory()->create(['balance' => 500]);

        $value = 200;

        $response = $this->postJson(route('transactions.store'), [
            'value' => $value,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $this->assertEquals([
            'error' => true,
            'code' => 401,
            'message' => 'Transaction not authorized by payment service.',
        ], $response->json());

        $this->assertDatabaseMissing('transactions', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ]);

        $this->assertEquals(1000, $payer->balance);
        $this->assertEquals(500, $payee->balance);
    }

    #[Test]
    public function it_should_send_notification_on_successful_Transaction(): void
    {
        Event::fake();

        $payer = Wallet::factory()->user()->create(['balance' => 1000]);
        $payee = Wallet::factory()->create(['balance' => 500]);

        $value = 150;

        $response = $this->postJson(route('transactions.store'), [
            'value' => $value,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ]);

        Event::assertDispatched(TransactionCompleted::class);
        
        Event::assertListening(
            TransactionCompleted::class,
            SendTransactionSuccessNotification::class
        );
    }

}