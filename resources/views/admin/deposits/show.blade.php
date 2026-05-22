@extends('layouts.app')
@section('title', 'Deposit Detail | Admin')
@section('page-title', 'Deposit Detail')
@section('page-subtitle', $deposit->account_reference)

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

<div class="max-w-3xl mx-auto space-y-5"
     x-data="{ showApprove: false, showReject: false }">

    <a href="{{ route('admin.deposits') }}"
       class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-300 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Deposits
    </a>

    {{-- Status header --}}
    <div class="rounded-2xl border overflow-hidden"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">

        <div class="flex items-center gap-4 p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <div class="flex-1">
                <h2 class="text-base font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">
                    {{ $deposit->method_label }} Deposit
                </h2>
                <p class="text-xs text-gray-500 font-mono">{{ $deposit->account_reference }}</p>
            </div>
            <span class="text-xs px-3 py-1 rounded-full font-semibold
                @if($deposit->isSuccessful()) bg-emerald-500/10 border border-emerald-500/20 text-emerald-400
                @elseif($deposit->isFailed()) bg-red-500/10 border border-red-500/20 text-red-400
                @elseif($deposit->isPending()) bg-amber-500/10 border border-amber-500/20 text-amber-400
                @else bg-gray-500/10 border border-gray-700 text-gray-400
                @endif">
                {{ ucfirst($deposit->status) }}
            </span>
        </div>

        {{-- Core amounts --}}
        <div class="grid grid-cols-3 divide-x" :class="isDark ? 'divide-gray-800/60' : 'divide-gray-100'">
            <div class="p-5">
                <p class="text-[10px] text-gray-500 mb-1">{{ $deposit->isUsdtDeposit() ? 'USDT Amount' : 'KES Amount' }}</p>
                <p class="text-xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">
                    {{ $deposit->isUsdtDeposit() ? number_format($deposit->local_amount, 2).' USDT' : 'KES '.number_format($deposit->local_amount, 2) }}
                </p>
            </div>
            <div class="p-5">
                <p class="text-[10px] text-gray-500 mb-1">USD Amount</p>
                <p class="text-xl font-bold {{ $deposit->isSuccessful() ? 'text-emerald-400' : '' }}"
                   :class="!{{ $deposit->isSuccessful() ? 'true' : 'false' }} ? (isDark ? 'text-white' : 'text-gray-900') : ''">
                    ${{ number_format($deposit->usd_amount, 2) }}
                </p>
            </div>
            <div class="p-5">
                <p class="text-[10px] text-gray-500 mb-1">Exchange Rate</p>
                <p class="text-sm font-semibold" :class="isDark ? 'text-gray-300' : 'text-gray-700'">
                    @if($deposit->isUsdtDeposit())
                        1 USDT = ${{ number_format($deposit->exchange_rate, 2) }}
                    @else
                        1 USD = {{ number_format($deposit->exchange_rate, 2) }} KES
                    @endif
                </p>
            </div>
        </div>

        {{-- Admin actions for pending USDT deposits --}}
        @if($deposit->isUsdtDeposit() && $deposit->isPending())
        <div class="p-4 border-t flex items-center gap-3" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <button @click="showApprove = true"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all"
                    style="background: linear-gradient(135deg,#10b981,#059669); box-shadow: 0 4px 12px rgba(16,185,129,0.2)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Approve Deposit
            </button>
            <button @click="showReject = true"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border transition-all"
                    :class="isDark ? 'border-red-500/40 text-red-400 hover:bg-red-500/10' : 'border-red-300 text-red-600 hover:bg-red-50'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Reject Deposit
            </button>
            <span class="text-xs text-gray-500 ml-auto">Wallet: {{ $deposit->wallet?->available_balance ? '$'.number_format($deposit->wallet->available_balance, 2) : '—' }}</span>
        </div>
        @endif

        @if($deposit->isCredited())
        <div class="p-4 border-t" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <div class="flex items-center gap-2 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <p class="text-xs text-emerald-400">Wallet credited | double-approval prevented. credited_at locked.</p>
            </div>
        </div>
        @endif
    </div>

    <div class="grid md:grid-cols-2 gap-5">

        {{-- User & Wallet --}}
        <div class="rounded-2xl border p-5"
             :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
            <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-4">User & Wallet</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-start">
                    <span class="text-xs text-gray-500">Name</span>
                    <span class="text-xs font-medium text-right" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->user?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-start">
                    <span class="text-xs text-gray-500">Email</span>
                    <span class="text-xs font-medium text-right" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->user?->email ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Wallet Type</span>
                    <span class="text-xs capitalize font-medium px-2 py-0.5 rounded-full {{ $deposit->wallet_type === 'live' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">{{ $deposit->wallet_type }}</span>
                </div>
                @if($deposit->wallet)
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Current Balance</span>
                    <span class="text-xs font-semibold text-emerald-400">${{ number_format($deposit->wallet->available_balance, 2) }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Provider / Crypto Details --}}
        <div class="rounded-2xl border p-5"
             :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
            <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-4">
                {{ $deposit->isUsdtDeposit() ? 'Crypto Details' : 'Provider Details' }}
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Provider</span>
                    <span class="text-xs font-medium capitalize" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->provider }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Method</span>
                    <span class="text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->method_label }}</span>
                </div>
                @if($deposit->crypto_network)
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Network</span>
                    <span class="text-xs font-semibold text-cyan-400">{{ $deposit->crypto_network }}</span>
                </div>
                @endif
                @if($deposit->crypto_address)
                <div class="flex justify-between items-start gap-4">
                    <span class="text-xs text-gray-500 flex-shrink-0">Deposit Address</span>
                    <span class="text-xs font-mono text-right break-all" :class="isDark ? 'text-gray-400' : 'text-gray-600'">{{ $deposit->crypto_address }}</span>
                </div>
                @endif
                @if($deposit->txid)
                <div class="flex justify-between items-start gap-4">
                    <span class="text-xs text-gray-500 flex-shrink-0">TXID</span>
                    <span class="text-xs font-mono text-right break-all" :class="isDark ? 'text-gray-400' : 'text-gray-600'">{{ $deposit->txid }}</span>
                </div>
                @endif
                @if($deposit->phone)
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">Phone</span>
                    <span class="text-xs font-medium font-mono" :class="isDark ? 'text-gray-300' : 'text-gray-700'">+{{ $deposit->phone }}</span>
                </div>
                @endif
                @if($deposit->mpesa_receipt)
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500">M-Pesa Receipt</span>
                    <span class="text-xs font-semibold font-mono text-emerald-400">{{ $deposit->mpesa_receipt }}</span>
                </div>
                @endif
                @if($deposit->result_description)
                <div class="flex justify-between items-start gap-4">
                    <span class="text-xs text-gray-500 flex-shrink-0">Result</span>
                    <span class="text-xs text-right {{ $deposit->isFailed() ? 'text-red-400' : 'text-gray-400' }}">{{ $deposit->result_description }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Proof screenshot (USDT) --}}
    @if($deposit->isUsdtDeposit() && $deposit->proof_url)
    <div class="rounded-2xl border overflow-hidden"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="p-4 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500">Proof Screenshot</h3>
        </div>
        <div class="p-4">
            <a href="{{ $deposit->proof_url }}" target="_blank" rel="noopener">
                <img src="{{ $deposit->proof_url }}"
                     alt="Deposit proof screenshot"
                     class="w-full max-h-96 object-contain rounded-xl border"
                     :class="isDark ? 'border-gray-700' : 'border-gray-200'">
            </a>
            <p class="text-[10px] text-center text-gray-500 mt-2">
                Click to view full size ·
                <a href="{{ $deposit->proof_url }}" target="_blank" rel="noopener" class="text-cyan-400 hover:text-cyan-300">Open in new tab</a>
            </p>
        </div>
    </div>
    @endif

    {{-- Review info (post-review) --}}
    @if($deposit->reviewed_at || $deposit->rejection_reason || $deposit->admin_notes)
    <div class="rounded-2xl border p-5"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-4">Review Information</h3>
        <div class="space-y-3">
            @if($deposit->reviewer)
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Reviewed By</span>
                <span class="text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->reviewer->name }}</span>
            </div>
            @endif
            @if($deposit->reviewed_at)
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Reviewed At</span>
                <span class="text-xs" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->reviewed_at->format('d M Y, H:i:s') }}</span>
            </div>
            @endif
            @if($deposit->rejection_reason)
            <div class="flex justify-between items-start gap-4">
                <span class="text-xs text-gray-500 flex-shrink-0">Rejection Reason</span>
                <span class="text-xs text-right text-red-400">{{ $deposit->rejection_reason }}</span>
            </div>
            @endif
            @if($deposit->admin_notes)
            <div class="flex justify-between items-start gap-4">
                <span class="text-xs text-gray-500 flex-shrink-0">Admin Notes</span>
                <span class="text-xs text-right" :class="isDark ? 'text-gray-400' : 'text-gray-600'">{{ $deposit->admin_notes }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Timeline --}}
    <div class="rounded-2xl border p-5"
         :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <h3 class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-4">Timeline</h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Submitted</span>
                <span class="text-xs" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->created_at->format('d M Y, H:i:s T') }}</span>
            </div>
            @if(!$deposit->isUsdtDeposit() && $deposit->last_status_checked_at)
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Last Status Check</span>
                <span class="text-xs" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->last_status_checked_at->format('d M Y, H:i:s T') }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Status Checks</span>
                <span class="text-xs" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $deposit->status_check_count }}</span>
            </div>
            @endif
            @if($deposit->credited_at)
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Credited At</span>
                <span class="text-xs font-semibold text-emerald-400">{{ $deposit->credited_at->format('d M Y, H:i:s T') }}</span>
            </div>
            @endif
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Double-Credit Safe</span>
                <span class="text-xs {{ $deposit->isCredited() ? 'text-emerald-400' : 'text-amber-400' }}">
                    {{ $deposit->isCredited() ? 'Yes | credited_at locked' : 'Not yet credited' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Raw responses (M-Pesa only) --}}
    @if(!$deposit->isUsdtDeposit())
        @foreach(['raw_initiation_response' => 'Raw Initiation Response', 'raw_callback_response' => 'Raw Callback Response', 'raw_status_response' => 'Raw Status Check Response'] as $field => $label)
        @if($deposit->$field)
        <div class="rounded-2xl border overflow-hidden"
             :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'"
             x-data="{ open: false }">
            <div class="flex items-center gap-3 p-4 border-b cursor-pointer"
                 :class="isDark ? 'border-gray-800/60' : 'border-gray-100'"
                 @click="open = !open">
                <span class="text-xs font-bold uppercase tracking-wide text-gray-500 flex-1">{{ $label }}</span>
                <svg class="w-4 h-4 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div x-show="open" class="p-4">
                <pre class="text-[10px] text-gray-400 overflow-x-auto whitespace-pre-wrap break-all font-mono">{{ json_encode($deposit->$field, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif
        @endforeach
    @endif

    {{-- Approve Modal --}}
    @if($deposit->isUsdtDeposit() && $deposit->isPending())
    <div x-show="showApprove" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @click.self="showApprove = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-md rounded-2xl border p-6 z-10"
             :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200 shadow-xl'">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">Approve USDT Deposit</p>
                    <p class="text-xs text-gray-500">This will credit ${{ number_format($deposit->usd_amount, 2) }} to the {{ $deposit->wallet_type }} wallet.</p>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-amber-500/8 border border-amber-500/20 mb-4">
                <p class="text-xs text-amber-400">Confirm TXID: <span class="font-mono font-semibold">{{ Str::limit($deposit->txid, 40) }}</span></p>
            </div>

            <form method="POST" action="{{ route('admin.deposits.approve-usdt', $deposit) }}"
                  @submit="$el.querySelector('button[type=submit]').disabled = true">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Admin Notes (optional)</label>
                    <textarea name="admin_notes" rows="2" placeholder="e.g. TXID verified on TronScan"
                              class="w-full px-3 py-2 text-xs rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-emerald-500/50 resize-none"
                              :class="isDark ? 'border-gray-700 text-white placeholder-gray-600' : 'border-gray-300 text-gray-900'"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showApprove = false"
                            class="flex-1 py-2.5 rounded-xl text-sm font-medium border transition-colors"
                            :class="isDark ? 'border-gray-700 text-gray-400 hover:border-gray-600' : 'border-gray-200 text-gray-600'">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-all"
                            style="background: linear-gradient(135deg,#10b981,#059669)">
                        Confirm Approval
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-show="showReject" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @click.self="showReject = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-md rounded-2xl border p-6 z-10"
             :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200 shadow-xl'">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-red-500/15 border border-red-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">Reject USDT Deposit</p>
                    <p class="text-xs text-gray-500">The user will see your rejection reason.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.deposits.reject-usdt', $deposit) }}"
                  @submit="$el.querySelector('button[type=submit]').disabled = true">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Rejection Reason <span class="text-red-400">*</span></label>
                    <textarea name="rejection_reason" rows="3" required
                              placeholder="e.g. TXID not found on blockchain, incorrect network used…"
                              class="w-full px-3 py-2 text-xs rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-red-500/50 resize-none"
                              :class="isDark ? 'border-gray-700 text-white placeholder-gray-600' : 'border-gray-300 text-gray-900'"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Admin Notes (optional)</label>
                    <textarea name="admin_notes" rows="2" placeholder="Internal notes, not shown to user"
                              class="w-full px-3 py-2 text-xs rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-red-500/50 resize-none"
                              :class="isDark ? 'border-gray-700 text-white placeholder-gray-600' : 'border-gray-300 text-gray-900'"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showReject = false"
                            class="flex-1 py-2.5 rounded-xl text-sm font-medium border transition-colors"
                            :class="isDark ? 'border-gray-700 text-gray-400 hover:border-gray-600' : 'border-gray-200 text-gray-600'">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition-colors">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

@endsection
