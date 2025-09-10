<?php

namespace App\Enums\Transaction;

enum TransactionStatusEnum: string
{
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';
    case FAILED_NO_FUNDS = 'FAILED_NO_FUNDS';
    case FAILED_UNAUTHORIZED = 'FAILED_UNAUTHORIZED';
    case FAILED_INVALID_WALLET_TYPE = 'FAILED_INVALID_WALLET_TYPE';
    case FAILED_UNKNOWN_REASON = 'FAILED_UNKNOWN_REASON';
}
