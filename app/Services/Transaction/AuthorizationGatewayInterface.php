<?php

namespace App\Services\Transaction;

use Illuminate\Container\Attributes\Bind;

#[Bind(HttpAuthorizationGatewayService::class)] //This bind can be removed if defined in AppService provider
interface AuthorizationGatewayInterface
{
    /**
     * Authorize a transaction request.
     *
     * @return boolean
     */
    public function authorize(): bool;
}
