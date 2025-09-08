<?php

declare(strict_types=1);

namespace App\Actions\Wallet;

use App\DataTransferObjects\Wallet\StoreWalletDTO;
use App\DataTransferObjects\Wallet\WalletDTO;
use App\Models\Wallet;

class StoreWalletAction
{
    /**
     * Store a newly created wallet in storage.
     *
     * @param StoreWalletDTO $dto
     * @return  WalletDTO
     */
    public function handle(StoreWalletDTO $dto): WalletDTO
    {
        $wallet = Wallet::query()->create($dto->toArray());

        return WalletDTO::fromArray($wallet->toArray());
    }
}
