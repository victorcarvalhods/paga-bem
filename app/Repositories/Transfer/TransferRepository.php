<?php

declare(strict_types=1);

namespace App\Repositories\Transfer;

use App\DataTransferObjects\Transfer\TransferDataDTO;
use App\Models\Transfer;

class TransferRepository
{
    /**
     * Store a new transfer record.
     *
     * @param TransferDataDTO $data
     * @return Transfer
     */
    public function create(TransferDataDTO $data): Transfer
    {
        return Transfer::create($data->toArray());
    }
}