<?php

declare(strict_types=1);

namespace App\Actions\Wallet;

use App\DataTransferObjects\Wallet\WalletDTO;
use App\Models\Wallet;
use App\Repositories\Wallet\WalletRepository;

class StoreWalletAction
{
    public function __construct(private WalletRepository $walletRepository) {}

    /**
     * Store a newly created wallet in storage.
     *
     * @param WalletDTO $dto
     * @return  Wallet
     */
    public function handle(WalletDTO $dto): Wallet
    {
        $wallet = $this->walletRepository->create($dto);

        return $wallet;
    }
}
