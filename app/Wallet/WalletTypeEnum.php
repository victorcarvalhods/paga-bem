<?php

namespace App\Wallet;

enum WalletTypeEnum: string
{
    case USER = 'user';
    case MERCHANT = 'merchant';
}
