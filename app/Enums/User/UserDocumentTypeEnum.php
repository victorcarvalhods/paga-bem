<?php

namespace App\Enums\User;

enum UserDocumentTypeEnum: string
{
    case CPF = 'cpf';
    case CNPJ = 'cnpj';
}
