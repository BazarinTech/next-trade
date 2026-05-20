@extends('layouts.trading')
@section('title', 'Bot Investments — Next Trade')
@section('page-title', 'Bot Investments')
@section('page-subtitle', 'Automated simulated investment portfolios')

@section('content')

<div x-data="botApp()" x-init="init()">

{{-- Simulation Warning --}}
<div class="mb-6 px-4 py-3 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-start gap-3">
    <svg class="w-4 h-4 text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <div>
        <p class="text-xs font-semibold text-amber-400">Educational Simulation Only</p>
        <p class="text-xs text-amber-400/70 mt-0.5">All bot earnings are simulated. No real money is involved. This platform is for educational purposes only.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

{{-- Portfolio Summary --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $summaryCards = [
            ['label'=>'Total Invested',     'value'=>'$'.number_format($totalInvested,2),  'color'=>'cyan',    'sub'=>ucfirst($walletMode).' wallet'],
            ['label'=>'Total Earned',       'value'=>'$'.number_format($totalEarned,2),    'color'=>'emerald', 'sub'=>'All time'],
            ['label'=>'Today\'s Earnings',  'value'=>'$'.number_format($todayEarnings,2),  'color'=>'purple',  'sub'=>'Credited today'],
            ['label'=>'Active Bots',        'value'=>$active->count(),                      'color'=>'amber',   'sub'=>'Running investments'],
        ];
    @endphp
    @foreach($summaryCards as $card)
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">{{ $card['label'] }}</p>
        <p class="text-2xl font-bold text-{{ $card['color'] }}-400">{{ $card['value'] }}</p>
        <p class="text-xs mt-1 text-gray-600">{{ $card['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- Active Wallet Badge --}}
<div class="flex items-center justify-between mb-4">
    <h2 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Available Bot Plans</h2>
    <span class="text-xs px-3 py-1 rounded-full font-medium {{ $walletMode === 'demo' ? 'bg-amber-500/10 border border-amber-500/20 text-amber-400' : 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' }}">
        {{ ucfirst($walletMode) }} Wallet · ${{ number_format($wallet->available_balance, 2) }} available
    </span>
</div>

{{-- Bot Plan Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-10">
    @forelse($plans as $plan)
    @php $rc = $plan->risk_color; @endphp
    <div class="rounded-2xl border transition-all duration-200 hover:border-{{ $rc }}-500/40 flex flex-col"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">

        {{-- Header --}}
        <div class="p-5 border-b border-gray-800/40">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-{{ $rc }}-500/15 border border-{{ $rc }}-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-{{ $rc }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H7a2 2 0 00-2 2v2M9 3h6M9 3v2m6-2h2a2 2 0 012 2v2m0 0V7m0 4h2m-2 0v6m0 0h2m-2 0a2 2 0 01-2 2h-2m0 0V19m0 2H9m0 0H7a2 2 0 01-2-2v-2m0 0H3m2 0V9m0 0H3m2 0a2 2 0 012-2h2m0 0V5"/>
                    </svg>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $rc }}-500/10 text-{{ $rc }}-400 border border-{{ $rc }}-500/20 font-medium capitalize">
                    {{ $plan->risk_label }} Risk
                </span>
            </div>
            <h3 class="text-base font-bold mb-1" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $plan->name }}</h3>
            <p class="text-xs text-gray-500 leading-relaxed">{{ $plan->description }}</p>
        </div>

        {{-- Stats --}}
        <div class="p-5 flex-1">
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="p-3 rounded-xl" :class="isDark ? 'bg-gray-800/60' : 'bg-gray-50'">
                    <p class="text-[10px] text-gray-500 mb-1">Daily ROI</p>
                    <p class="text-lg font-bold text-{{ $rc }}-400">{{ $plan->daily_roi_percent }}%</p>
                </div>
                <div class="p-3 rounded-xl" :class="isDark ? 'bg-gray-800/60' : 'bg-gray-50'">
                    <p class="text-[10px] text-gray-500 mb-1">Duration</p>
                    <p class="text-lg font-bold" :class="isDark ? 'text-white' : 'text-gray-800'">{{ $plan->duration_label }}</p>
                </div>
                <div class="p-3 rounded-xl" :class="isDark ? 'bg-gray-800/60' : 'bg-gray-50'">
                    <p class="text-[10px] text-gray-500 mb-1">Min Invest</p>
                    <p class="text-sm font-bold" :class="isDark ? 'text-gray-300' : 'text-gray-700'">${{ number_format($plan->min_investment, 2) }}</p>
                </div>
                <div class="p-3 rounded-xl" :class="isDark ? 'bg-gray-800/60' : 'bg-gray-50'">
                    <p class="text-[10px] text-gray-500 mb-1">Max Invest</p>
                    <p class="text-sm font-bold" :class="isDark ? 'text-gray-300' : 'text-gray-700'">
                        {{ $plan->max_investment ? '$'.number_format($plan->max_investment, 2) : 'Unlimited' }}
                    </p>
                </div>
            </div>
            @if($plan->total_earning)
            <div class="text-xs text-center py-2 rounded-lg bg-{{ $rc }}-500/5 border border-{{ $rc }}-500/10 text-{{ $rc }}-400 font-medium">
                Estimated: {{ $plan->total_earning }} over {{ $plan->duration_days }} days
            </div>
            @endif
        </div>

        {{-- Action --}}
        <div class="p-4 pt-0">
            <button @click="openInvest({{ json_encode([
                'id'               => $plan->id,
                'name'             => $plan->name,
                'daily_roi'        => (float)$plan->daily_roi_percent,
                'min'              => (float)$plan->min_investment,
                'max'              => $plan->max_investment ? (float)$plan->max_investment : null,
                'duration'         => $plan->duration_days,
                'risk_color'       => $plan->risk_color,
            ]) }})"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold text-white transition-all bg-{{ $rc }}-500 hover:bg-{{ $rc }}-600 shadow-lg shadow-{{ $rc }}-500/20">
                Invest Now
            </button>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-12 text-sm text-gray-500">No active bot plans available.</div>
    @endforelse
</div>

{{-- Active Investments --}}
@if($active->count())
<div class="mb-8">
    <h2 class="text-sm font-semibold mb-4" :class="isDark ? 'text-white' : 'text-gray-900'">Active Investments</h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach($active as $inv)
        @php $sc = $inv->status_color; @endphp
        <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $inv->botPlan->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Started {{ $inv->started_at->format('M d, Y') }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $sc }}-500/10 text-{{ $sc }}-400 border border-{{ $sc }}-500/20 font-medium">{{ ucfirst($inv->status) }}</span>
            </div>

            <div class="grid grid-cols-3 gap-3 mb-4">
                <div>
                    <p class="text-[10px] text-gray-500 mb-0.5">Principal</p>
                    <p class="text-sm font-bold text-cyan-400">${{ number_format($inv->principal_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 mb-0.5">Daily ROI</p>
                    <p class="text-sm font-bold text-emerald-400">{{ $inv->daily_roi_percent }}%</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 mb-0.5">Total Earned</p>
                    <p class="text-sm font-bold text-emerald-400">${{ number_format($inv->total_earned, 2) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4 text-xs">
                <div>
                    <p class="text-gray-500">Daily Earning</p>
                    <p class="font-semibold text-gray-300">${{ number_format($inv->daily_earning_amount, 4) }}</p>
                </div>
                @if($inv->ends_at)
                <div>
                    <p class="text-gray-500">Days Remaining</p>
                    <p class="font-semibold text-gray-300">{{ $inv->days_remaining }} days</p>
                </div>
                @endif
            </div>

            @if($inv->ends_at)
            <div class="mb-4">
                <div class="flex justify-between text-[10px] text-gray-500 mb-1">
                    <span>Progress</span>
                    <span>{{ $inv->progress_percent }}%</span>
                </div>
                <div class="w-full bg-gray-800 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full bg-{{ $sc }}-500 transition-all" style="width:{{ $inv->progress_percent }}%"></div>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('bots.cancel', $inv) }}"
                  onsubmit="return confirm('Cancel this investment? This action cannot be undone.')">
                @csrf
                <button type="submit" class="text-xs text-red-400 hover:text-red-300 transition-colors">Cancel Investment</button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Recent Earnings --}}
<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Recent Earnings</h2>
        <a href="{{ route('bots.earnings') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">View all</a>
    </div>
    <div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        @forelse($recentEarnings as $earning)
        <div class="flex items-center gap-4 px-5 py-3 border-b last:border-0" :class="isDark ? 'border-gray-800/40' : 'border-gray-50'">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium truncate" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $earning->botPlan->name }}</p>
                <p class="text-[10px] text-gray-500">{{ $earning->earning_date->format('M d, Y') }} · {{ $earning->roi_percent }}% ROI</p>
            </div>
            <span class="text-sm font-bold text-emerald-400">+${{ number_format($earning->amount, 4) }}</span>
            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $earning->status === 'credited' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                {{ ucfirst($earning->status) }}
            </span>
        </div>
        @empty
        <div class="px-5 py-10 text-center text-sm text-gray-500">No earnings yet. Earnings are credited daily at 00:05.</div>
        @endforelse
    </div>
</div>

{{-- Completed Investments --}}
@if($completed->count())
<div>
    <h2 class="text-sm font-semibold mb-4" :class="isDark ? 'text-white' : 'text-gray-900'">Completed &amp; Cancelled</h2>
    <div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b text-xs font-semibold text-gray-500" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
                        <th class="px-5 py-3 text-left">Plan</th>
                        <th class="px-5 py-3 text-right">Principal</th>
                        <th class="px-5 py-3 text-right">Total Earned</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-left">Period</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($completed as $inv)
                    @php $sc = $inv->status_color; @endphp
                    <tr class="border-b last:border-0 transition-colors" :class="isDark ? 'border-gray-800/40 hover:bg-gray-800/20' : 'border-gray-50 hover:bg-gray-50'">
                        <td class="px-5 py-3 text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $inv->botPlan->name }}</td>
                        <td class="px-5 py-3 text-xs text-right font-mono text-gray-400">${{ number_format($inv->principal_amount,2) }}</td>
                        <td class="px-5 py-3 text-xs text-right font-bold text-emerald-400">${{ number_format($inv->total_earned,2) }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-{{ $sc }}-500/10 text-{{ $sc }}-400 border border-{{ $sc }}-500/20">{{ ucfirst($inv->status) }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">
                            {{ $inv->started_at->format('M d') }} — {{ $inv->updated_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Invest Modal --}}
<div x-show="showModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="showModal = false">
    <div class="w-full max-w-sm rounded-2xl border shadow-2xl" :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'"
         @click.stop x-transition.scale>

        <div class="flex items-center justify-between p-6 border-b" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
            <h2 class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-gray-900'" x-text="'Invest in ' + selectedPlan?.name"></h2>
            <button @click="showModal = false" class="p-1 rounded-lg text-gray-500 hover:text-gray-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('bots.invest') }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="bot_plan_id" :value="selectedPlan?.id">

            {{-- Wallet Info --}}
            <div class="flex items-center justify-between p-3 rounded-xl border"
                 :class="isDark ? 'border-gray-800 bg-gray-800/50' : 'border-gray-100 bg-gray-50'">
                <span class="text-xs text-gray-500">{{ ucfirst($walletMode) }} Wallet Balance</span>
                <span class="text-sm font-bold text-cyan-400">${{ number_format($wallet->available_balance, 2) }}</span>
            </div>

            {{-- Amount Input --}}
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Investment Amount (USD)</label>
                <input type="number" name="amount" x-model.number="amount"
                       :min="selectedPlan?.min" :max="selectedPlan?.max ?? undefined"
                       step="0.01" required
                       @input="calcEstimate()"
                       class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                       :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                <div class="flex justify-between mt-1 text-[10px] text-gray-500">
                    <span>Min: $<span x-text="selectedPlan?.min?.toFixed(2) ?? '—'"></span></span>
                    <span x-show="selectedPlan?.max">Max: $<span x-text="selectedPlan?.max?.toFixed(2) ?? ''"></span></span>
                </div>
            </div>

            {{-- Quick Amounts --}}
            <div class="grid grid-cols-4 gap-1.5">
                @foreach([10, 25, 50, 100] as $quick)
                <button type="button" @click="setAmount({{ $quick }})"
                        class="text-xs py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:text-white hover:border-cyan-500/50 hover:bg-cyan-500/5 transition-colors">
                    ${{ $quick }}
                </button>
                @endforeach
            </div>

            {{-- Estimates --}}
            <div class="p-3 rounded-xl border" :class="isDark ? 'border-gray-800 bg-gray-800/30' : 'border-gray-100 bg-gray-50'">
                <div class="flex justify-between text-xs mb-2">
                    <span class="text-gray-500">Daily ROI</span>
                    <span class="font-semibold text-emerald-400" x-text="selectedPlan ? selectedPlan.daily_roi + '%' : '—'"></span>
                </div>
                <div class="flex justify-between text-xs mb-2">
                    <span class="text-gray-500">Est. Daily Earning</span>
                    <span class="font-bold text-emerald-400">+$<span x-text="estDaily.toFixed(4)"></span></span>
                </div>
                <template x-if="selectedPlan?.duration">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500">Est. Total (<span x-text="selectedPlan.duration"></span> days)</span>
                        <span class="font-bold text-cyan-400">+$<span x-text="estTotal.toFixed(2)"></span></span>
                    </div>
                </template>
            </div>

            <button type="submit"
                    :disabled="!amount || amount <= 0"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold text-white transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                    style="background: linear-gradient(135deg,#06b6d4,#0891b2); box-shadow: 0 4px 12px rgba(6,182,212,0.25)">
                Confirm Investment
            </button>
        </form>
    </div>
</div>

</div>

@push('scripts')
<script>
function botApp() {
    return {
        showModal:    false,
        selectedPlan: null,
        amount:       0,
        estDaily:     0,
        estTotal:     0,

        init() {},

        openInvest(plan) {
            this.selectedPlan = plan;
            this.amount       = plan.min ?? 0;
            this.calcEstimate();
            this.showModal = true;
        },

        setAmount(val) {
            this.amount = val;
            this.calcEstimate();
        },

        calcEstimate() {
            if (!this.selectedPlan || !this.amount) {
                this.estDaily = 0;
                this.estTotal = 0;
                return;
            }
            this.estDaily = this.amount * this.selectedPlan.daily_roi / 100;
            this.estTotal = this.selectedPlan.duration
                ? this.estDaily * this.selectedPlan.duration
                : 0;
        },
    };
}
</script>
@endpush

<x-sim-disclaimer />
@endsection
