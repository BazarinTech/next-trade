<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Services\CurrencyService;
use App\Services\NotificationService;
use App\Services\WalletService;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class WithdrawalController extends Controller
{
    public function __construct(
        private WithdrawalService   $withdrawalService,
        private WalletService       $walletService,
        private CurrencyService     $currency,
        private NotificationService $notifier
    ) {}

    public function index(): View
    {
        $user        = auth()->user();
        $liveWallet  = $this->walletService->getUserWallet($user, 'live');
        $isKenya     = $user->isKenya();
        $exchangeRate = $this->currency->getUsdKesRate();

        $recentWithdrawals = Withdrawal::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $pendingWithdrawals = Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'processing'])
            ->orderByDesc('created_at')
            ->get();

        return view('wallet.withdraw', compact(
            'liveWallet', 'isKenya', 'exchangeRate',
            'recentWithdrawals', 'pendingWithdrawals'
        ));
    }

    public function requestMpesa(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->isKenya()) {
            return back()->with('error', 'M-Pesa withdrawals are only available for Kenya accounts.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:10000',
            'phone'  => ['required', 'string', 'regex:/^(\+?254|0)(7|1)\d{8}$/'],
        ], [
            'phone.regex' => 'Please enter a valid Kenyan phone number (e.g. 0712345678).',
            'amount.min'  => 'Minimum withdrawal is $1.00.',
        ]);

        try {
            $withdrawal = $this->withdrawalService->createMpesaWithdrawal(
                $user,
                (float) $validated['amount'],
                $validated['phone']
            );

            $this->notifier->send($user, 'withdrawal_requested', 'Withdrawal Requested',
                'Your M-Pesa withdrawal of $' . number_format($withdrawal->usd_amount, 2) . ' has been submitted and is pending admin review.',
                ['withdrawal_id' => $withdrawal->id]);

            return redirect()
                ->route('withdrawals.show', $withdrawal)
                ->with('success', 'M-Pesa withdrawal request submitted. Admin will process it shortly.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function requestUsdt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:5|max:100000',
            'crypto_address' => ['required', 'string', 'min:20', 'max:120'],
        ], [
            'amount.min'            => 'Minimum USDT withdrawal is $5.00.',
            'crypto_address.min'    => 'Please enter a valid TRC20 wallet address.',
        ]);

        try {
            $withdrawal = $this->withdrawalService->createUsdtWithdrawal(
                auth()->user(),
                (float) $validated['amount'],
                $validated['crypto_address']
            );

            $user = auth()->user();
            $this->notifier->send($user, 'withdrawal_requested', 'Withdrawal Requested',
                'Your USDT withdrawal of $' . number_format($withdrawal->usd_amount, 2) . ' has been submitted and is pending admin review.',
                ['withdrawal_id' => $withdrawal->id]);

            return redirect()
                ->route('withdrawals.show', $withdrawal)
                ->with('success', 'USDT withdrawal request submitted. Admin will process it shortly.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(Withdrawal $withdrawal): View
    {
        if ($withdrawal->user_id !== auth()->id()) {
            abort(403);
        }

        $withdrawal->load(['wallet', 'reviewer']);

        return view('withdrawals.show', compact('withdrawal'));
    }
}
