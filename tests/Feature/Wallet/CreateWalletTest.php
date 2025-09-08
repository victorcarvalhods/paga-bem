<?php

namespace Tests\Feature\Wallet;

use App\Models\Wallet;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateWalletTest extends TestCase
{
    #[Test]
    public function it_should_create_a_wallet()
    {
        $wallet = Wallet::factory()->raw();

        $response = $this->postJson('/api/wallets', $wallet);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'balance',
                'wallet_type',
            ]);

        $response->assertJsonFragment([
            'user_id' => $wallet['user_id'],
            'balance' => $wallet['balance'],
            'wallet_type' => $wallet['wallet_type'],
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $wallet['user_id'],
            'balance' => $wallet['balance'],
            'wallet_type' => $wallet['wallet_type'],
        ]);
    }

    #[Test]
    public function it_validates_required_fields()
    {
        $response = $this->postJson('/api/wallets', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'balance', 'wallet_type']);
    }

    #[Test]
    public function it_should_validate_wallet_type()
    {
        $wallet = Wallet::factory()->raw(['wallet_type' => 'invalid_type']);

        $response = $this->postJson('/api/wallets', $wallet);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['wallet_type']);
    }

    #[Test]
    public function it_should_validate_balance_non_negative()
    {
        $wallet = Wallet::factory()->raw(['balance' => -100]);

        $response = $this->postJson('/api/wallets', $wallet);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['balance']);
    }

    #[Test]
    public function it_should_validate_user_id_exists()
    {
        $wallet = Wallet::factory()->raw(['user_id' => 999999]);

        $response = $this->postJson('/api/wallets', $wallet);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }
}
