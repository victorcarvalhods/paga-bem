<?php

namespace App\Services\Transfers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpAuthorizationGatewayService implements AuthorizationGatewayInterface
{
    private string $serviceUrl;

    public function __construct()
    {
        $this->serviceUrl = config('services.transaction_authorization.url');
    }

    /**
     * Authorize a transaction by calling an external service.
     *
     * @return boolean
     */
    public function authorize(): bool
    {
        try {
            return $this->callExternalService();
        } catch (\Exception $e) {
            Log::error('Http Authorization service error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Call the external authorization service.
     *
     * @return boolean
     */
    private function callExternalService(): bool
    {
        $response = Http::withHeaders([
            'Accept'        => 'application/json',
            'Content-Type' => 'application/json',
        ])
        ->withOptions([
                'verify' => false,
        ])
        ->get($this->serviceUrl);

        return $response->successful() && 
               $response->json('status') === 'success' && 
               $response->json('data.authorization') === true;
    }
}
