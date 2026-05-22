<?php

namespace App\Http\Controllers;

use App\Services\DepositService;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PalPlussWebhookController extends Controller
{
    public function __construct(
        private DepositService    $depositService,
        private WithdrawalService $withdrawalService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('PalPluss STK webhook received', [
            'event'  => $payload['event'] ?? null,
            'tx_id'  => $payload['transaction']['id'] ?? null,
            'status' => $payload['transaction']['status'] ?? null,
        ]);

        if (empty($payload['transaction']) || !is_array($payload['transaction'])) {
            Log::warning('PalPluss STK webhook: invalid payload structure');
            return response()->json(['success' => true]);
        }

        try {
            $this->depositService->handlePalplussCallback($payload);
        } catch (\Throwable $e) {
            Log::error('PalPluss STK webhook processing error', [
                'message' => $e->getMessage(),
                'tx_id'   => $payload['transaction']['id'] ?? null,
            ]);
            // Always return 200 to prevent PalPluss from retrying endlessly
        }

        return response()->json(['success' => true]);
    }

    public function handleB2c(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('PalPluss B2C webhook received', [
            'event'  => $payload['event'] ?? null,
            'tx_id'  => $payload['transaction']['id'] ?? null,
            'status' => $payload['transaction']['status'] ?? null,
        ]);

        if (empty($payload['transaction']) || !is_array($payload['transaction'])) {
            Log::warning('PalPluss B2C webhook: invalid payload structure');
            return response()->json(['success' => true]);
        }

        try {
            $this->withdrawalService->processB2cCallback($payload);
        } catch (\Throwable $e) {
            Log::error('PalPluss B2C webhook processing error', [
                'message' => $e->getMessage(),
                'tx_id'   => $payload['transaction']['id'] ?? null,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
