<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotEarning;
use App\Models\BotInvestment;
use App\Models\BotPlan;
use App\Services\AdminLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminBotController extends Controller
{
    public function __construct(private AdminLogService $logger) {}

    public function index(): View
    {
        $plans = BotPlan::withCount(['investments', 'investments as active_investments_count' => fn($q) => $q->where('status', 'active')])
            ->withSum('investments as total_principal', 'principal_amount')
            ->withSum('investments as total_earned_all', 'total_earned')
            ->orderBy('sort_order')
            ->get();

        $totalActiveInvestments = BotInvestment::where('status', 'active')->count();
        $totalPrincipal         = BotInvestment::where('status', 'active')->sum('principal_amount');
        $totalEarningsCredited  = BotEarning::where('status', 'credited')->sum('amount');
        $todayEarnings          = BotEarning::where('status', 'credited')->whereDate('earning_date', today())->sum('amount');

        $mostPopularPlan = BotPlan::withCount(['investments as active_investments_count' => fn($q) => $q->where('status', 'active')])
            ->orderByDesc('active_investments_count')
            ->first();

        $recentInvestments = BotInvestment::with(['user', 'botPlan'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.bots.index', compact(
            'plans', 'totalActiveInvestments', 'totalPrincipal',
            'totalEarningsCredited', 'todayEarnings',
            'mostPopularPlan', 'recentInvestments'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePlan($request);

        $plan = BotPlan::create($validated);

        $this->logger->log(
            auth()->user(),
            'bot_plan_created',
            BotPlan::class,
            $plan->id,
            [],
            $validated
        );

        return back()->with('success', "Bot plan \"{$plan->name}\" created successfully.");
    }

    public function update(Request $request, BotPlan $botPlan): RedirectResponse
    {
        $validated = $this->validatePlan($request, $botPlan->id);
        $old       = $botPlan->only(array_keys($validated));

        $botPlan->update($validated);

        $this->logger->log(
            auth()->user(),
            'bot_plan_updated',
            BotPlan::class,
            $botPlan->id,
            $old,
            $validated
        );

        return back()->with('success', "Bot plan \"{$botPlan->name}\" updated successfully.");
    }

    public function destroy(BotPlan $botPlan): RedirectResponse
    {
        if ($botPlan->investments()->where('status', 'active')->exists()) {
            return back()->with('error', "Cannot delete \"{$botPlan->name}\": it has active investments.");
        }

        $this->logger->log(
            auth()->user(),
            'bot_plan_deleted',
            BotPlan::class,
            $botPlan->id,
            $botPlan->toArray(),
            []
        );

        $botPlan->delete();

        return back()->with('success', "Bot plan \"{$botPlan->name}\" deleted.");
    }

    public function toggle(BotPlan $botPlan): RedirectResponse
    {
        $was = $botPlan->status;
        $new = $was === 'active' ? 'inactive' : 'active';

        $botPlan->update(['status' => $new]);

        $this->logger->log(
            auth()->user(),
            $new === 'active' ? 'bot_plan_activated' : 'bot_plan_deactivated',
            BotPlan::class,
            $botPlan->id,
            ['status' => $was],
            ['status' => $new]
        );

        return back()->with('success', "Bot plan \"{$botPlan->name}\" {$new}.");
    }

    private function validatePlan(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'required|string|max:100|unique:bot_plans,slug' . ($ignoreId ? ",{$ignoreId}" : '');

        return $request->validate([
            'name'               => 'required|string|max:100',
            'slug'               => $slugRule,
            'description'        => 'nullable|string|max:500',
            'daily_roi_percent'  => 'required|numeric|min:0|max:100',
            'min_investment'     => 'required|numeric|gt:0',
            'max_investment'     => 'nullable|numeric|gt:min_investment',
            'duration_days'      => 'nullable|integer|min:1',
            'risk_level'         => 'required|in:low,medium,high,extreme',
            'status'             => 'required|in:active,inactive',
            'sort_order'         => 'integer|min:0',
        ]);
    }
}
