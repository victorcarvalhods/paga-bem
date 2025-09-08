<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class HttpAuthorizationGatewayService implements AuthorizationGatewayInterface
{
    private string $serviceUrl;

    public function __construct() {
        $this->serviceUrl = config('services.transaction_authorization.url');
    }
    public function authorize(): bool
    {
        try {
            return $this->callExternalService();
        } catch (\Exception $e) {
            Log::error('Authorization service error: ' . $e->getMessage());
            return false;
        }
    }

    private function callExternalService(): bool
    {
        $client = $this->getHttpClient();
        $response = $client->get($this->serviceUrl);
        return $response->getStatusCode() === 200;
    }

    /**
     * Get the HTTP client instance.
     *
     * @return Client
     */
    private function getHttpClient(): Client
    {
        return new Client();
    }
}
