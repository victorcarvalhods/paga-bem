<?php

namespace Tests\Feature\Transfer;

use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreTransferTest extends TestCase
{
    #[Test]
    public function it_should_store_transfer(): void
    {
        $payer = Wallet::factory()->create(['balance' => 1000]);
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

        $payer->refresh();
        $payee->refresh();

        $this->assertEquals(1000 - $value, $payer->balance);
        $this->assertEquals(500 + $value, $payee->balance);
    }

    #[Test]
    public function it_should_not_allow_transfer_when_payer_has_insufficient_balance(): void
    {
        $payer = Wallet::factory()->create(['balance' => 100]);
        $payee = Wallet::factory()->create(['balance' => 500]);

        $value = 200;

        $response = $this->postJson('api/transfer', [
            'value' => $value,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $this->assertEquals([
            'error' => 'Insufficient balance to complete this transfer.',
            'code' => 422,
        ], $response->json());

        $this->assertDatabaseMissing('transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ]);

        $payer->refresh();
        $payee->refresh();

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
            'error' => 'Merchant accounts cannot initiate transfers.',
            'code' => 422,
        ], $response->json());

        $this->assertDatabaseMissing('transfers', [
            'payer_id' => $payer->id,
            'payee_id' => $payee->id,
        ]);

        $payer->refresh();
        $payee->refresh();

        $this->assertEquals(1000, $payer->balance);
        $this->assertEquals(500, $payee->balance);
    }

}