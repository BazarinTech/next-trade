<?php

namespace App\Http\Controllers;

use App\Models\BotEarning;
use App\Models\BotInvestment;
use App\Models\Trade;
use App\Models\Transaction;
use App\Services\WalletService;

class DashboardController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function index()
    {
        $user       = auth()->user();
        $walletMode = session('wallet_mode', 'demo');

        $demoWallet = $this->walletService->getUserWallet($user, 'demo');
        $liveWallet = $this->walletService->getUserWallet($user, 'live');
        $active     = $walletMode === 'live' ? $liveWallet : $demoWallet;

        // Today's P&L from transactions
        $todayProfit = Transaction::where('user_id', $user->id)
            ->where('wallet_id', $active->id)
            ->whereIn('type', ['trade_profit', 'bot_profit'])
            ->where('status', 'successful')
            ->whereDate('created_at', today())
            ->sum('amount');

        $todayLoss = Transaction::where('user_id', $user->id)
            ->where('wallet_id', $active->id)
            ->whereIn('type', ['trade_loss'])
            ->where('status', 'successful')
            ->whereDate('created_at', today())
            ->sum('amount');

        $todayPnl    = bcsub((string) $todayProfit, (string) $todayLoss, 2);
        $isPnlPositive = bccomp($todayPnl, '0', 2) >= 0;

        // Recent transactions
        $recentTransactions = Transaction::with('wallet')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        // Active trades count
        $activeTrades = Trade::where('user_id', $user->id)
            ->where('status', 'open')
            ->count();

        // Recent closed trades
        $recentTrades = Trade::where('user_id', $user->id)
            ->whereIn('status', ['won', 'lost', 'draw', 'cancelled'])
            ->with('tradingAsset')
            ->orderByDesc('closed_at')
            ->limit(5)
            ->get();

        // Bot portfolio summary
        $activeBotInvestments = BotInvestment::where('user_id', $user->id)
            ->where('wallet_id', $active->id)
            ->where('status', 'active')
            ->count();

        $todayBotEarnings = BotEarning::where('user_id', $user->id)
            ->where('wallet_id', $active->id)
            ->where('status', 'credited')
            ->whereDate('earning_date', today())
            ->sum('amount');

        $recentBotEarnings = BotEarning::where('user_id', $user->id)
            ->where('wallet_id', $active->id)
            ->with('botPlan')
            ->orderByDesc('earning_date')
            ->limit(3)
            ->get();

        return view('dashboard', compact(
            'demoWallet', 'liveWallet', 'active',
            'walletMode', 'todayPnl', 'isPnlPositive',
            'recentTransactions', 'activeTrades', 'recentTrades',
            'activeBotInvestments', 'todayBotEarnings', 'recentBotEarnings'
        ));
    }

    public function settings(){ return view('dashboard.settings'); }
    public function support() { return view('dashboard.support'); }
}
