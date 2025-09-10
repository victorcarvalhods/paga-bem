<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Transaction;

final class TransactionDTO
{

    public function __construct(
        public int $payer,
        public int $payee,
        public float $value,
        public ?int $id = null,
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
            id: isset($data['id']) ? (int) $data['id'] : null,
            payer: (int) $data['payer_id'],
            payee: (int) $data['payee_id'],
            value: (float) $data['value'],
        );
    }

    /**
     * Convert the DTO to an array.
     *
     * @return array<string, float|int|null>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'payer_id' => $this->payer,
            'payee_id' => $this->payee,
            'value' => $this->value,
        ];
    }
}
