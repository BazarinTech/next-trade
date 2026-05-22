@extends('layouts.trading')
@section('title', 'Withdraw | Next Trade')
@section('page-title', 'Withdraw Funds')
@section('page-subtitle', 'Withdraw from your live wallet')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

<div class="max-w-2xl mx-auto space-y-6">

    {{-- Pending Withdrawals Alert --}}
    @foreach($pendingWithdrawals as $pw)
    <div class="flex items-center justify-between px-4 py-3 rounded-xl bg-amber-500/10 border border-amber-500/20">
        <div class="flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
            <div>
                <p class="text-xs font-semibold text-amber-400">
                    Pending {{ $pw->method === 'mpesa' ? 'M-Pesa' : 'USDT' }} Withdrawal
                    <span class="ml-1 capitalize text-amber-400/70">({{ $pw->status }})</span>
                </p>
                <p class="text-xs text-amber-400/70">
                    ${{ number_format($pw->usd_amount, 2) }} · {{ $pw->created_at->diffForHumans() }}
                </p>
            </div>
        </div>
        <a href="{{ route('withdrawals.show', $pw) }}" class="text-xs text-amber-400 hover:text-amber-300 transition-colors font-medium">View →</a>
    </div>
    @endforeach

    {{-- Live Wallet Balance Card --}}
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-3">Live Wallet Balance</p>
        <div class="flex items-end justify-between gap-4 flex-wrap">
            <div>
                <p class="text-2xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">
                    ${{ number_format($liveWallet->available_balance, 2) }}
                    <span class="text-sm font-normal text-gray-500">available</span>
                </p>
                @if($liveWallet->locked_balance > 0)
                <p class="text-xs text-amber-400 mt-1">
                    ${{ number_format($liveWallet->locked_balance, 2) }} locked in pending withdrawals
                </p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">Total Balance</p>
                <p class="text-sm font-semibold" :class="isDark ? 'text-gray-300' : 'text-gray-700'">${{ number_format($liveWallet->balance, 2) }}</p>
            </div>
        </div>
        @if($liveWallet->isFrozen())
        <div class="mt-3 px-3 py-2 rounded-lg bg-red-500/10 border border-red-500/20">
            <p class="text-xs text-red-400">Your live wallet is frozen. Contact support to withdraw.</p>
        </div>
        @endif
    </div>

    {{-- M-Pesa Withdrawal (Kenya only) --}}
    @if($isKenya)
    <div x-data="mpesaWithdrawForm()" class="rounded-2xl border overflow-hidden"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">

        <div class="flex items-center gap-3 p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">Withdraw via M-Pesa</p>
                <p class="text-xs text-gray-500">Funds sent directly to your Safaricom number</p>
            </div>
        </div>

        <div class="p-5 space-y-4">
            @if($errors->any())
            <div class="px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs space-y-1">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('withdrawals.mpesa') }}" @submit.prevent="handleSubmit($event.target)">
                @csrf

                {{-- Amount --}}
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-500">Amount (USD)</label>
                    <input type="number" name="amount" min="1" max="10000" step="0.01"
                           x-model.number="amount" @input="calcKes()"
                           value="{{ old('amount') }}"
                           class="w-full px-4 py-3 rounded-xl border text-sm font-mono focus:outline-none focus:ring-2 focus:ring-cyan-500/40 transition-colors"
                           :class="isDark ? 'bg-gray-800/60 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'"
                           placeholder="0.00" required>
                    <div class="flex gap-2 flex-wrap">
                        @foreach([10, 25, 50, 100, 200] as $amt)
                        <button type="button" @click="setAmount({{ $amt }})"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
                                :class="amount === {{ $amt }}
                                    ? 'bg-cyan-500/20 border-cyan-500/40 text-cyan-400'
                                    : (isDark ? 'bg-gray-800 border-gray-700 text-gray-400 hover:border-cyan-500/40 hover:text-cyan-400' : 'bg-gray-50 border-gray-200 text-gray-600 hover:border-cyan-400 hover:text-cyan-600')">
                            ${{ $amt }}
                        </button>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500" x-show="amount > 0">
                        ≈ KES <span x-text="kesPreview.toLocaleString('en-KE', {minimumFractionDigits:2, maximumFractionDigits:2})"></span>
                        <span class="text-gray-600">(rate: {{ number_format($exchangeRate, 2) }})</span>
                    </p>
                </div>

                {{-- Phone --}}
                <div class="space-y-2 mt-4">
                    <label class="block text-xs font-medium text-gray-500">M-Pesa Phone Number</label>
                    <input type="tel" name="phone"
                           value="{{ old('phone') }}"
                           class="w-full px-4 py-3 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40 transition-colors"
                           :class="isDark ? 'bg-gray-800/60 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'"
                           placeholder="0712 345 678" required>
                    <p class="text-xs text-gray-500">Kenyan number: 07XXXXXXXX or 01XXXXXXXX</p>
                </div>

                <div class="mt-5 p-4 rounded-xl border" :class="isDark ? 'bg-gray-800/40 border-gray-700/40' : 'bg-gray-50 border-gray-200'">
                    <p class="text-xs text-gray-500">Funds are sent to M-Pesa within 1–24 hours after admin approval. Minimum: $1.00</p>
                </div>

                <button type="submit" :disabled="submitting || amount <= 0"
                        class="mt-5 w-full flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 disabled:opacity-50 disabled:cursor-not-allowed text-black text-sm font-bold transition-colors">
                    <span x-show="!submitting">Request M-Pesa Withdrawal</span>
                    <span x-show="submitting" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                        Submitting…
                    </span>
                </button>
            </form>
        </div>
    </div>
    @else
    {{-- Non-Kenya M-Pesa Notice --}}
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gray-500/10 border border-gray-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium" :class="isDark ? 'text-gray-400' : 'text-gray-600'">M-Pesa Withdrawal</p>
                <p class="text-xs text-gray-500">Available for Kenya accounts only</p>
            </div>
        </div>
    </div>
    @endif

    {{-- USDT TRC20 Withdrawal --}}
    <div x-data="usdtWithdrawForm()" class="rounded-2xl border overflow-hidden"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">

        <div class="flex items-center gap-3 p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/15 border border-cyan-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">Withdraw USDT (TRC20)</p>
                <p class="text-xs text-gray-500">Withdraw to any TRON network wallet</p>
            </div>
        </div>

        <div class="p-5 space-y-4">
            <form method="POST" action="{{ route('withdrawals.usdt') }}" @submit.prevent="handleSubmit($event.target)">
                @csrf

                {{-- Amount --}}
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-gray-500">Amount (USD)</label>
                    <input type="number" name="amount" min="5" max="100000" step="0.01"
                           x-model.number="amount"
                           value="{{ old('amount') }}"
                           class="w-full px-4 py-3 rounded-xl border text-sm font-mono focus:outline-none focus:ring-2 focus:ring-cyan-500/40 transition-colors"
                           :class="isDark ? 'bg-gray-800/60 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'"
                           placeholder="0.00" required>
                    <div class="flex gap-2 flex-wrap">
                        @foreach([25, 50, 100, 250, 500] as $amt)
                        <button type="button" @click="setAmount({{ $amt }})"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
                                :class="amount === {{ $amt }}
                                    ? 'bg-cyan-500/20 border-cyan-500/40 text-cyan-400'
                                    : (isDark ? 'bg-gray-800 border-gray-700 text-gray-400 hover:border-cyan-500/40 hover:text-cyan-400' : 'bg-gray-50 border-gray-200 text-gray-600 hover:border-cyan-400 hover:text-cyan-600')">
                            ${{ $amt }}
                        </button>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500">≈ <span x-text="amount > 0 ? amount.toFixed(2) : '0.00'"></span> USDT · Minimum: $5.00</p>
                </div>

                {{-- Crypto Address --}}
                <div class="space-y-2 mt-4">
                    <label class="block text-xs font-medium text-gray-500">TRC20 Wallet Address</label>
                    <input type="text" name="crypto_address" minlength="20" maxlength="120"
                           value="{{ old('crypto_address') }}"
                           class="w-full px-4 py-3 rounded-xl border text-sm font-mono focus:outline-none focus:ring-2 focus:ring-cyan-500/40 transition-colors"
                           :class="isDark ? 'bg-gray-800/60 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'"
                           placeholder="TRC20 address (e.g. T...)" required>
                    <p class="text-xs text-amber-400">⚠ Only send to a TRON (TRC20) network address. Wrong network = lost funds.</p>
                </div>

                <div class="mt-5 p-4 rounded-xl border" :class="isDark ? 'bg-gray-800/40 border-gray-700/40' : 'bg-gray-50 border-gray-200'">
                    <p class="text-xs text-gray-500">Withdrawal is processed within 1–48 hours after admin approval. Double-check your wallet address before submitting.</p>
                </div>

                <button type="submit" :disabled="submitting || amount <= 0"
                        class="mt-5 w-full flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-cyan-500 hover:bg-cyan-400 disabled:opacity-50 disabled:cursor-not-allowed text-black text-sm font-bold transition-colors">
                    <span x-show="!submitting">Request USDT Withdrawal</span>
                    <span x-show="submitting" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                        Submitting…
                    </span>
                </button>
            </form>
        </div>
    </div>

    {{-- Recent Withdrawals --}}
    @if($recentWithdrawals->isNotEmpty())
    <div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <p class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">Recent Withdrawals</p>
        </div>
        <div class="divide-y" :class="isDark ? 'divide-gray-800/60' : 'divide-gray-100'">
            @foreach($recentWithdrawals as $wd)
            <a href="{{ route('withdrawals.show', $wd) }}"
               class="flex items-center justify-between px-5 py-3 transition-colors hover:bg-gray-500/5 group">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-2 h-2 rounded-full flex-shrink-0 bg-{{ $wd->status_color }}-400 {{ in_array($wd->status, ['pending','processing','approved']) ? 'animate-pulse' : '' }}"></span>
                    <div class="min-w-0">
                        <p class="text-xs font-medium truncate" :class="isDark ? 'text-white' : 'text-gray-900'">
                            {{ $wd->method_label }}
                        </p>
                        <p class="text-xs text-gray-500">{{ $wd->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0 ml-4">
                    <p class="text-xs font-mono font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">${{ number_format($wd->usd_amount, 2) }}</p>
                    <p class="text-xs capitalize text-{{ $wd->status_color }}-400">{{ $wd->status }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
function mpesaWithdrawForm() {
    return {
        amount: {{ old('amount', 0) }},
        kesPreview: 0,
        rate: {{ $exchangeRate }},
        submitting: false,
        init() { this.calcKes(); },
        calcKes() {
            this.kesPreview = this.amount > 0 ? Math.round(this.amount * this.rate * 100) / 100 : 0;
        },
        setAmount(val) { this.amount = val; this.calcKes(); },
        handleSubmit(form) { this.submitting = true; form.submit(); },
    };
}
function usdtWithdrawForm() {
    return {
        amount: {{ old('amount', 0) }},
        submitting: false,
        setAmount(val) { this.amount = val; },
        handleSubmit(form) { this.submitting = true; form.submit(); },
    };
}
</script>
@endpush

<x-sim-disclaimer />
@endsection
