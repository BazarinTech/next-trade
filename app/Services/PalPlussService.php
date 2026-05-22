<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PalPlussService
{
    private string $baseUrl;
    private string $basicAuth;
    private string $channelId;
    private string $callbackUrl;

    public function __construct()
    {
        $this->baseUrl     = rtrim(config('palpluss.base_url', 'https://api.palpluss.com'), '/');
        $this->basicAuth   = config('palpluss.basic_auth', '');
        $this->channelId   = config('palpluss.channel_id', '');
        $this->callbackUrl = config('palpluss.stk_callback_url', '');
    }

    public function initiateStk(array $payload): array
    {
        try {
            $response = Http::withHeaders($this->buildBasicAuthHeader())
                ->timeout(30)
                ->retry(2, 500, throw: false)
                ->post("{$this->baseUrl}/v1/payments/stk", array_merge($payload, [
                    'channelId'   => $this->channelId,
                    'callbackUrl' => $this->callbackUrl,
                ]));

            $body = $response->json() ?? [];

            if (!$response->successful() || !($body['success'] ?? false)) {
                $errorMsg = $body['error']['message'] ?? 'STK initiation failed';
                $errorCode = $body['error']['code'] ?? 'UNKNOWN';

                Log::warning('PalPluss STK initiation failed', [
                    'status'  => $response->status(),
                    'error'   => $errorMsg,
                    'code'    => $errorCode,
                    'payload' => $this->safeLogPayload($payload),
                ]);

                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'code'    => $errorCode,
                    'raw'     => $body,
                ];
            }

            return [
                'success' => true,
                'data'    => $body['data'] ?? [],
                'raw'     => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('PalPluss STK initiation exception', [
                'message' => $e->getMessage(),
                'payload' => $this->safeLogPayload($payload),
            ]);

            return [
                'success' => false,
                'message' => 'Unable to reach payment provider. Please try again.',
                'code'    => 'CONNECTION_ERROR',
                'raw'     => [],
            ];
        }
    }

    public function initiateB2c(array $payload): array
    {
        try {
            $response = Http::withHeaders($this->buildBasicAuthHeader())
                ->timeout(30)
                ->retry(2, 500, throw: false)
                ->post("{$this->baseUrl}/v1/b2c/payouts", array_merge($payload, [
                    'callbackUrl' => config('palpluss.b2c_callback_url', ''),
                ]));

            $body = $response->json() ?? [];

            if (!$response->successful() || !($body['success'] ?? false)) {
                $errorMsg  = $body['error']['message'] ?? 'B2C initiation failed';
                $errorCode = $body['error']['code']    ?? 'UNKNOWN';

                Log::warning('PalPluss B2C initiation failed', [
                    'status'  => $response->status(),
                    'error'   => $errorMsg,
                    'code'    => $errorCode,
                    'payload' => $this->safeLogPayload($payload),
                ]);

                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'code'    => $errorCode,
                    'raw'     => $body,
                ];
            }

            return [
                'success' => true,
                'data'    => $body['data'] ?? [],
                'raw'     => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('PalPluss B2C initiation exception', [
                'message' => $e->getMessage(),
                'payload' => $this->safeLogPayload($payload),
            ]);

            return [
                'success' => false,
                'message' => 'Unable to reach payment provider. Please try again.',
                'code'    => 'CONNECTION_ERROR',
                'raw'     => [],
            ];
        }
    }

    public function getTransactionStatus(string $transactionId): array
    {
        try {
            $response = Http::withHeaders($this->buildBasicAuthHeader())
                ->timeout(30)
                ->retry(2, 500, throw: false)
                ->get("{$this->baseUrl}/v1/transactions/{$transactionId}");

            $body = $response->json() ?? [];

            if (!$response->successful() || !($body['success'] ?? false)) {
                $errorMsg = $body['error']['message'] ?? 'Status check failed';

                Log::warning('PalPluss status check failed', [
                    'transaction_id' => $transactionId,
                    'status'         => $response->status(),
                    'error'          => $errorMsg,
                ]);

                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'raw'     => $body,
                ];
            }

            return [
                'success' => true,
                'data'    => $body['data'] ?? [],
                'raw'     => $body,
            ];
        } catch (\Throwable $e) {
            Log::error('PalPluss status check exception', [
                'transaction_id' => $transactionId,
                'message'        => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Unable to reach payment provider.',
                'code'    => 'CONNECTION_ERROR',
                'raw'     => [],
            ];
        }
    }

    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        $phone = ltrim($phone, '+');

        if (str_starts_with($phone, '254')) {
            return $phone;
        }

        if (str_starts_with($phone, '0')) {
            return '254' . substr($phone, 1);
        }

        return $phone;
    }

    public function buildBasicAuthHeader(): array
    {
        return [
            'Authorization' => 'Basic ' . $this->basicAuth,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    private function safeLogPayload(array $payload): array
    {
        // Never log sensitive fields
        return array_diff_key($payload, array_flip(['callbackUrl', 'channelId']));
    }
}
