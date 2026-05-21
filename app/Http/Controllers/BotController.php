<?php

namespace App\Http\Controllers;

use App\Models\BotEarning;
use App\Models\BotInvestment;
use App\Models\BotPlan;
use App\Services\BotInvestmentService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BotController extends Controller
{
    public function __construct(
        private BotInvestmentService $botService,
        private WalletService        $walletService
    ) {}

    public function index(): View
    {
        $user       = auth()->user();
        $walletMode = session('wallet_mode', 'demo');
        $wallet     = $this->walletService->getUserWallet($user, $walletMode);

        $plans     = $this->botService->getActivePlans();
        $portfolio = $this->botService->getUserPortfolio($user);

        $recentEarnings = BotEarning::where('user_id', $user->id)
            ->where('wallet_id', $wallet->id)
            ->with('botPlan')
            ->orderByDesc('earning_date')
            ->limit(10)
            ->get();

        return view('bots.index', array_merge(compact('plans', 'wallet', 'walletMode', 'recentEarnings'), $portfolio));
    }

    public function invest(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'bot_plan_id' => 'required|exists:bot_plans,id',
            'amount'      => 'required|numeric|gt:0',
        ]);

        $user       = auth()->user();
        $walletMode = session('wallet_mode', 'demo');
        $plan       = BotPlan::findOrFail($validated['bot_plan_id']);

        try {
            $investment = $this->botService->invest($user, $plan, (float) $validated['amount'], $walletMode);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully invested \${$investment->principal_amount} in {$plan->name}. Daily earnings will be credited automatically.",
                ]);
            }

            return redirect()->route('trade.index')->with('success', "Successfully invested \${$investment->principal_amount} in {$plan->name}. Daily earnings will be credited automatically.");
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function cancel(BotInvestment $investment): RedirectResponse
    {
        if ($investment->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->botService->cancelInvestment($investment);
            return redirect()->route('trade.index')->with('success', 'Investment cancelled successfully.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function earnings(Request $request): View
    {
        $user       = auth()->user();
        $walletMode = session('wallet_mode', 'demo');
        $wallet     = $this->walletService->getUserWallet($user, $walletMode);

        $query = BotEarning::where('user_id', $user->id)
            ->where('wallet_id', $wallet->id)
            ->with(['botPlan', 'investment'])
            ->orderByDesc('earning_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $earnings = $query->paginate(20)->withQueryString();

        $totalCredited = BotEarning::where('user_id', $user->id)
            ->where('wallet_id', $wallet->id)
            ->where('status', 'credited')
            ->sum('amount');

        return view('bots.earnings', compact('earnings', 'totalCredited', 'walletMode', 'wallet'));
    }
}
