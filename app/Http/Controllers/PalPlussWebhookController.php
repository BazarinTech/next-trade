<?php

namespace App\Http\Controllers;

use App\Services\DepositService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PalPlussWebhookController extends Controller
{
    public function __construct(private DepositService $depositService) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('PalPluss webhook payload received', [
            'event'  => $payload['event'] ?? null,
            'tx_id'  => $payload['transaction']['id'] ?? null,
            'status' => $payload['transaction']['status'] ?? null,
        ]);

        // Validate minimal structure
        if (empty($payload['transaction']) || !is_array($payload['transaction'])) {
            Log::warning('PalPluss webhook: invalid payload structure');
            return response()->json(['success' => true]);
        }

        try {
            $this->depositService->handlePalplussCallback($payload);
        } catch (\Throwable $e) {
            Log::error('PalPluss webhook processing error', [
                'message' => $e->getMessage(),
                'tx_id'   => $payload['transaction']['id'] ?? null,
            ]);
            // Always return 200 to prevent PalPluss from retrying endlessly
        }

        return response()->json(['success' => true]);
    }
}
