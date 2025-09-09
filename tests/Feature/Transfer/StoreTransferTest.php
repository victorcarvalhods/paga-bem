<?php

namespace Tests\Feature\Transfer;

use App\Events\Transfer\TransferCompleted;
use App\Listeners\Transfer\SendTransferSuccessNotification;
use App\Models\Wallet;
use App\Services\Transfers\AuthorizationGatewayInterface;
use App\Services\Notifications\NotificationGatewayInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreTransferTest extends TestCase
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
    public function it_should_store_transfer(): void
    {
        $payer = Wallet::factory()->user()->create(['balance' => 1000]);
        $payee = Wallet::factory()->create(['balance' => 500]);

        $value = $this->faker->randomFloat(2, 10, 1000);

        $response = $this->postJson('api/transfer', [
            'value' => $value,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ]);
    }

    #[Test]
    public function it_should_not_allow_transfer_when_payer_has_insufficient_balance(): void
    {
        $payer = Wallet::factory()->user()->create(['balance' => 100]);
        $payee = Wallet::factory()->create(['balance' => 500]);

        $value = 200;

        $response = $this->postJson('api/transfer', [
            'value' => $value,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $this->assertEquals([
            'error' => true,
            'code' => 409,
            'message' => 'Insufficient balance to complete this transfer.',
        ], $response->json());

        $this->assertDatabaseMissing('transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ]);

        $this->assertEquals(100, $payer->balance);
        $this->assertEquals(500, $payee->balance);
    }

    #[Test]
    public function it_should_not_allow_transfer_when_payer_is_merchant(): void
    {
        $payer = Wallet::factory()->merchant()->create(['balance' => 1000]);
        $payee = Wallet::factory()->create(['balance' => 500]);

        $value = 200;

        $response = $this->postJson('api/transfer', [
            'value' => $value,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $this->assertEquals([
            'error' => true,
            'code' => 403,
            'message' => 'Merchant accounts cannot initiate transfers.',
        ], $response->json());

        $this->assertDatabaseMissing('transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ]);

        $this->assertEquals(1000, $payer->balance);
        $this->assertEquals(500, $payee->balance);
    }

    #[Test]
    public function it_should_not_allow_transfer_when_authorization_service_fails(): void
    {
        $this->mock(AuthorizationGatewayInterface::class, function ($mock) {
            $mock->shouldReceive('authorize')
                 ->once()
                 ->andReturn(false);
        });

        $payer = Wallet::factory()->user()->create(['balance' => 1000]);
        $payee = Wallet::factory()->create(['balance' => 500]);

        $value = 200;

        $response = $this->postJson('api/transfer', [
            'value' => $value,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $this->assertEquals([
            'error' => true,
            'code' => 401,
            'message' => 'Transfer not authorized by payment service.',
        ], $response->json());

        $this->assertDatabaseMissing('transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ]);

        $this->assertEquals(1000, $payer->balance);
        $this->assertEquals(500, $payee->balance);
    }

    #[Test]
    public function it_should_send_notification_on_successful_transfer(): void
    {
        Event::fake();

        $payer = Wallet::factory()->user()->create(['balance' => 1000]);
        $payee = Wallet::factory()->create(['balance' => 500]);

        $value = 150;

        $response = $this->postJson('api/transfer', [
            'value' => $value,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ]);

        Event::assertDispatched(TransferCompleted::class);
        
        Event::assertListening(
            TransferCompleted::class,
            SendTransferSuccessNotification::class
        );
    }

}