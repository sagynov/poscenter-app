<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Wappi
{
    private string $baseUrl = 'https://wappi.pro/api/async';
    private string $token;
    private string $profileId;

    public function __construct(string $token, string $profileId)
    {
        $this->token     = $token;
        $this->profileId = $profileId;
    }

    /**
     * Send a message to a recipient via Wappi.
     *
     * @param  string  $recipient  Phone number, e.g. "79995579399"
     * @param  string  $body       Message text
     * @return array{status: string, timestamp: int, time: string, detail: string, task_id: string}
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function sendMessage(string $recipient, string $body): array
    {
        $response = $this->request('POST', '/message/send', [
            'recipient' => $recipient,
            'body'      => $body,
        ]);

        return $response->json();
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Execute an authenticated HTTP request.
     *
     * @param  string  $method   HTTP verb (GET, POST, …)
     * @param  string  $path     API path starting with "/"
     * @param  array   $payload  Request body (JSON)
     */
    private function request(string $method, string $path, array $payload = []): Response
    {
        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])
            ->withQueryParameters([
                'profile_id' => $this->profileId,
            ])
            ->send($method, $this->baseUrl . $path, ['json' => $payload]);

        $response->throw(); // throws RequestException on 4xx / 5xx

        return $response;
    }
}