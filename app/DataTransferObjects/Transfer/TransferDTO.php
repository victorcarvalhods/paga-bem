<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Transfer;

final class TransferDTO
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
            id: $data['id'] ?? null,
            payer: $data['payer_id'],
            payee: $data['payee_id'],
            value: $data['value'],
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
            'payer_id' => $this->payer,
            'payee_id' => $this->payee,
            'value' => $this->value,
        ];
    }
}
