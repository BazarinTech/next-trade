<?php

namespace App\Http\Controllers;

use App\Models\PaymentDeposit;
use App\Models\Withdrawal;
use App\Services\CurrencyService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class WalletController extends Controller
{
    public function __construct(
        private WalletService   $walletService,
        private CurrencyService $currency
    ) {}

    public function index(): View
    {
        $user        = auth()->user();
        $demoWallet  = $this->walletService->getUserWallet($user, 'demo');
        $liveWallet  = $this->walletService->getUserWallet($user, 'live');
        $walletMode  = session('wallet_mode', 'demo');
        $activeWallet = $walletMode === 'live' ? $liveWallet : $demoWallet;

        $recentDeposits = PaymentDeposit::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $pendingWithdrawals = Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'processing'])
            ->orderByDesc('created_at')
            ->get();

        return view('wallet.index', compact(
            'demoWallet', 'liveWallet', 'walletMode', 'activeWallet',
            'recentDeposits', 'pendingWithdrawals'
        ));
    }

    public function deposit(): View
    {
        $user        = auth()->user();
        $walletMode  = session('wallet_mode', 'demo');
        $demoWallet  = $this->walletService->getUserWallet($user, 'demo');
        $liveWallet  = $this->walletService->getUserWallet($user, 'live');
        $isKenya     = $user->isKenya();
        $exchangeRate = $this->currency->getUsdKesRate();

        $pendingDeposits = PaymentDeposit::where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        return view('wallet.deposit', compact(
            'walletMode', 'demoWallet', 'liveWallet',
            'isKenya', 'exchangeRate', 'pendingDeposits'
        ));
    }

    public function withdraw(): View
    {
        $walletMode = session('wallet_mode', 'demo');
        $wallet     = $this->walletService->getUserWallet(auth()->user(), $walletMode);
        return view('wallet.withdraw', compact('wallet', 'walletMode'));
    }

    public function switchMode(Request $request): RedirectResponse
    {
        $mode = $request->input('mode');
        $mode = in_array($mode, ['demo', 'live']) ? $mode : 'demo';
        session(['wallet_mode' => $mode]);

        return back()->with('success', 'Switched to ' . ucfirst($mode) . ' wallet.');
    }

    public function resetDemo(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $this->walletService->resetDemoWallet(auth()->user());
            session(['wallet_mode' => 'demo']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Demo wallet has been reset to $10,000.00.',
                ]);
            }

            return back()->with('success', 'Demo wallet has been reset to $10,000.00.');
        } catch (RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
