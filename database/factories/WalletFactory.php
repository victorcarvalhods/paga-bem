<?php

namespace Database\Factories;

use App\Models\User;
use App\Wallet\WalletTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'balance' => $this->faker->randomFloat(2, 0, 10000),
            'wallet_type' => $this->faker->randomElement(WalletTypeEnum::cases()),
        ];
    }

    /**
     * Indicate that the wallet is of type MERCHANT.
     *
     * @return $this
     */
    public function merchant(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'wallet_type' => WalletTypeEnum::MERCHANT,
            ];
        });
    }

    /**
     * Indicate that the wallet is of type USER.
     *
     * @return $this
     */
    public function user(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'wallet_type' => WalletTypeEnum::USER,
            ];
        });
    }
}
