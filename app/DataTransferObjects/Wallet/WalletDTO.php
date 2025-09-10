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
     * @param array<string, string|float|int|null> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            userId: (int) $data['userId'],
            balance: floatval($data['balance']),
            walletType: (string) $data['walletType'],
        );
    }

    /**
     * Convert the DTO to an array.
     *
     * @return array<string, string|float|int|null>
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