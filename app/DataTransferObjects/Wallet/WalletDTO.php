<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Wallet;

final class WalletDTO
{
    public function __construct(
        public int $id,
        public int $user_id,
        public float $balance,
        public string $wallet_type,
    ) {}

    /**
     * Create a DTO from an array.
     *
     * @param array<string, int|string|float> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            user_id: $data['user_id'],
            balance: floatval($data['balance']),
            wallet_type: $data['wallet_type'],
        );
    }

    /**
     * Convert the DTO to an array.
     *
     * @return array<string, int|string|float>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'balance' => $this->balance,
            'wallet_type' => $this->wallet_type,
        ];
    }
}