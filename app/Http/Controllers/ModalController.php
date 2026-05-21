<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\PaymentDeposit;
use App\Models\ReferralCommission;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Services\BotInvestmentService;
use App\Services\CurrencyService;
use App\Services\NotificationService;
use App\Services\ReferralService;
use App\Services\WalletService;

class ModalController extends Controller
{
    public function deposit(WalletService $wallets, CurrencyService $currency)
    {
        $user            = auth()->user();
        $demoWallet      = $wallets->getUserWallet($user, 'demo');
        $liveWallet      = $wallets->getUserWallet($user, 'live');
        $isKenya         = $user->isKenya();
        $exchangeRate    = $currency->getUsdKesRate();
        $walletMode      = session('wallet_mode', 'demo');
        $pendingDeposits = PaymentDeposit::where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')->limit(3)->get();
        return view('modals.deposit', compact(
            'demoWallet', 'liveWallet', 'isKenya', 'exchangeRate', 'walletMode', 'pendingDeposits'
        ));
    }

    public function withdraw(WalletService $wallets, CurrencyService $currency)
    {
        $user               = auth()->user();
        $liveWallet         = $wallets->getUserWallet($user, 'live');
        $isKenya            = $user->isKenya();
        $exchangeRate       = $currency->getUsdKesRate();
        $pendingWithdrawals = Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'processing'])
            ->orderByDesc('created_at')->get();
        $recentWithdrawals  = Withdrawal::where('user_id', $user->id)
            ->orderByDesc('created_at')->limit(5)->get();
        return view('modals.withdraw', compact(
            'liveWallet', 'isKenya', 'exchangeRate', 'pendingWithdrawals', 'recentWithdrawals'
        ));
    }

    public function wallet(WalletService $wallets)
    {
        $user        = auth()->user();
        $demoWallet  = $wallets->getUserWallet($user, 'demo');
        $liveWallet  = $wallets->getUserWallet($user, 'live');
        $walletMode  = session('wallet_mode', 'demo');
        $recent      = Transaction::with('wallet')
            ->where('user_id', $user->id)->latest()->limit(8)->get();
        return view('modals.wallet', compact('demoWallet', 'liveWallet', 'walletMode', 'recent'));
    }

    public function history()
    {
        $transactions = Transaction::with('wallet')
            ->where('user_id', (int) auth()->id())
            ->latest()->limit(50)->get();
        return view('modals.history', compact('transactions'));
    }

    public function bots(BotInvestmentService $botService, WalletService $wallets)
    {
        $user       = auth()->user();
        $walletMode = (string) session('wallet_mode', 'demo');
        $wallet     = $wallets->getUserWallet($user, $walletMode);
        $plans      = $botService->getActivePlans();
        $portfolio  = $botService->getUserPortfolio($user);
        return view('modals.bots', array_merge(compact('plans', 'wallet', 'walletMode'), $portfolio));
    }

    public function notifications(NotificationService $notificationService)
    {
        $user          = auth()->user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
        $unreadCount   = $notificationService->unreadCount($user);
        return view('modals.notifications', compact('notifications', 'unreadCount'));
    }

    public function profile()
    {
        return view('modals.profile');
    }

    public function settings()
    {
        return view('modals.settings', [
            'currentTheme' => auth()->user()->theme_preference ?? 'dark',
        ]);
    }

    public function referral(ReferralService $referralService)
    {
        $user        = auth()->user();
        $commissions = ReferralCommission::where('referrer_id', $user->id)
            ->with('referred:id,name,email')
            ->latest()
            ->limit(10)
            ->get();
        $totalEarned  = $referralService->totalEarned($user);
        $totalInvited = $user->referrals()->count();
        $activeCount  = $referralService->activeReferralCount($user);
        $referralUrl  = url('/register?ref=' . $user->referral_code);

        return view('modals.referral', compact(
            'commissions', 'totalEarned', 'totalInvited', 'activeCount', 'referralUrl'
        ));
    }

    public function logout()
    {
        return view('modals.logout');
    }
}
