<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotInvestment;
use App\Models\PaymentDeposit;
use App\Models\Trade;
use App\Models\TradingAsset;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\Withdrawal;
use App\Services\AdminLogService;
use App\Services\SimulationConfigService;

class AdminController extends Controller
{
    public function __construct(
        private SimulationConfigService $simConfig,
        private AdminLogService         $logger
    ) {}

    public function dashboard()
    {
        $totalUsers      = User::count();
        $recentUsers     = User::latest()->limit(5)->get();
        $activeConfig    = $this->simConfig->getActiveConfig();
        $activeAssets    = TradingAsset::where('is_active', true)->count();
        $openTrades      = Trade::where('status', 'open')->count();
        $settledToday    = Trade::whereIn('status', ['won', 'lost'])
                               ->whereDate('closed_at', today())
                               ->count();
        $recentActivity  = $this->logger->recent(10);

        $depositsPending      = PaymentDeposit::where('status', 'pending')->count();
        $depositsSuccessToday = PaymentDeposit::where('status', 'successful')->whereDate('credited_at', today())->count();
        $depositsFailedToday  = PaymentDeposit::where('status', 'failed')->whereDate('updated_at', today())->count();
        $depositKesToday      = PaymentDeposit::where('status', 'successful')->whereDate('credited_at', today())->sum('local_amount');
        $depositUsdToday      = PaymentDeposit::where('status', 'successful')->whereDate('credited_at', today())->sum('usd_amount');

        $withdrawalsPending       = Withdrawal::where('status', 'pending')->count();
        $withdrawalsProcessing    = Withdrawal::where('status', 'processing')->count();
        $withdrawalsSuccessToday  = Withdrawal::where('status', 'successful')->whereDate('completed_at', today())->count();
        $withdrawalsRejectedToday = Withdrawal::whereIn('status', ['rejected', 'failed'])->whereDate('reviewed_at', today())->count();
        $withdrawalsUsdToday      = Withdrawal::where('status', 'successful')->whereDate('completed_at', today())->sum('usd_amount');
        $withdrawalsLocked        = Withdrawal::whereIn('status', ['pending', 'approved', 'processing'])->sum('usd_amount');

        $activeBotsCount   = BotInvestment::where('status', 'active')->count();
        $bannedUsers       = User::where('is_banned', true)->count();
        $activeUsers       = User::where('is_banned', false)->count();
        $recentUserActivity = UserActivityLog::with('user')->latest()->limit(8)->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'recentUsers', 'activeConfig',
            'activeAssets', 'openTrades', 'settledToday', 'recentActivity',
            'depositsPending', 'depositsSuccessToday', 'depositsFailedToday',
            'depositKesToday', 'depositUsdToday',
            'withdrawalsPending', 'withdrawalsProcessing', 'withdrawalsSuccessToday',
            'withdrawalsRejectedToday', 'withdrawalsUsdToday', 'withdrawalsLocked',
            'activeBotsCount', 'bannedUsers', 'activeUsers', 'recentUserActivity'
        ));
    }

    public function users()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function toggleBan(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot ban yourself.');
        }

        $user->update(['is_banned' => !$user->is_banned]);
        $status = $user->is_banned ? 'banned' : 'unbanned';

        return back()->with('success', "User {$user->name} has been {$status}.");
    }

    public function payments()   { return view('admin.payments'); }
    public function permissions(){ return view('admin.permissions'); }
    public function settings()   { return view('admin.settings'); }
}
