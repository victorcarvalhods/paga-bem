<?php

declare(strict_types=1);

namespace App\Repositories\Transfer;

use App\DataTransferObjects\Transfer\TransferDTO;
use App\Models\Transfer;

class TransferRepository
{
    /**
     * Store a new transfer record.
     *
     * @param TransferDTO $data
     * @return Transfer
     */
    public function create(TransferDTO $data): Transfer
    {
        return Transfer::create($data->toArray());
    }
}