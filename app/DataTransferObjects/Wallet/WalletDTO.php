<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Wallet;

final class WalletDTO
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public float $balance,
        public string $walletType,
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
            id: $data['id'] ?? null,
            userId: $data['userId'],
            balance: floatval($data['balance']),
            walletType: $data['walletType'],
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
            'userId' => $this->userId,
            'balance' => $this->balance,
            'walletType' => $this->walletType,
        ];
    }
}