<?php

namespace App\Services;

use Illuminate\Container\Attributes\Bind;

#[Bind(HttpAuthorizationGatewayService::class)] //This bind can be removed if defined in AppService provider
interface AuthorizationGatewayInterface
{
    public function authorize(): bool;
}
