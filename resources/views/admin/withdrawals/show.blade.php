@extends('layouts.app')
@section('title', 'Withdrawal #'.$withdrawal->id.' — Admin')
@section('page-title', 'Withdrawal Detail')
@section('page-subtitle', $withdrawal->account_reference)

@section('content')
<div x-data="{ showApprove: false, showReject: false, showProcessing: false, showSuccessful: false, showFailed: false }"
     class="max-w-3xl mx-auto space-y-6">

    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Status Header --}}
    <div class="rounded-2xl border p-6" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <p class="text-xs text-gray-500 mb-1">Withdrawal Amount</p>
                <p class="text-3xl font-bold" :class="isDark ? 'text-white' : 'text-gray-900'">${{ number_format($withdrawal->usd_amount, 2) }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $withdrawal->method_label }} · {{ $withdrawal->account_reference }}</p>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold
                    bg-{{ $withdrawal->status_color }}-500/15 border border-{{ $withdrawal->status_color }}-500/25 text-{{ $withdrawal->status_color }}-400">
                    @if(in_array($withdrawal->status, ['pending','processing','approved']))
                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $withdrawal->status_color }}-400 animate-pulse"></span>
                    @endif
                    {{ ucfirst($withdrawal->status) }}
                </span>
                <p class="text-xs text-gray-500 mt-2">{{ $withdrawal->created_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    @if(!$withdrawal->isTerminal())
    <div class="flex flex-wrap gap-3">
        @if($withdrawal->isPending())
        <button @click="showApprove = true"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/25 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Approve
        </button>
        <button @click="showReject = true"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-500/15 border border-red-500/30 text-red-400 hover:bg-red-500/25 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Reject
        </button>
        @endif
        @if(in_array($withdrawal->status, ['pending','approved']))
        <button @click="showProcessing = true"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-500/15 border border-blue-500/30 text-blue-400 hover:bg-blue-500/25 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Mark Processing
        </button>
        @endif
        @if(in_array($withdrawal->status, ['approved','processing']))
        <button @click="showSuccessful = true"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-400 hover:bg-cyan-500/25 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Mark Successful
        </button>
        <button @click="showFailed = true"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-500/15 border border-red-500/30 text-red-400 hover:bg-red-500/25 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Mark Failed
        </button>
        @endif
    </div>
    @endif

    {{-- Details --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Left: Withdrawal Info --}}
        <div class="rounded-2xl border divide-y" :class="isDark ? 'bg-gray-900/60 border-gray-800/60 divide-gray-800/60' : 'bg-white border-gray-200 shadow-sm divide-gray-100'">
            <div class="px-5 py-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Withdrawal Info</p>
            </div>
            <div class="px-5 py-3 grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-gray-500">Method</p>
                    <p class="text-sm font-medium mt-0.5" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $withdrawal->method_label }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">USD Amount</p>
                    <p class="text-sm font-mono font-bold mt-0.5" :class="isDark ? 'text-white' : 'text-gray-900'">${{ number_format($withdrawal->usd_amount, 2) }}</p>
                </div>
            </div>
            <div class="px-5 py-3 grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-gray-500">Fee</p>
                    <p class="text-sm font-mono mt-0.5" :class="isDark ? 'text-gray-300' : 'text-gray-700'">${{ number_format($withdrawal->fee_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Net Amount</p>
                    <p class="text-sm font-mono font-semibold mt-0.5 text-emerald-400">${{ number_format($withdrawal->net_amount, 2) }}</p>
                </div>
            </div>
            @if($withdrawal->method === 'mpesa')
            <div class="px-5 py-3 grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-gray-500">Phone</p>
                    <p class="text-sm font-mono mt-0.5" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $withdrawal->phone }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">KES Amount</p>
                    <p class="text-sm font-mono mt-0.5" :class="isDark ? 'text-gray-300' : 'text-gray-700'">KES {{ number_format($withdrawal->local_amount, 2) }}</p>
                </div>
            </div>
            @endif
            @if($withdrawal->method === 'usdt_trc20')
            <div class="px-5 py-3">
                <p class="text-xs text-gray-500">Destination Address</p>
                <p class="text-xs font-mono break-all mt-1 text-cyan-400">{{ $withdrawal->crypto_address }}</p>
            </div>
            @if($withdrawal->txid)
            <div class="px-5 py-3">
                <p class="text-xs text-gray-500">TXID</p>
                <p class="text-xs font-mono break-all mt-1" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $withdrawal->txid }}</p>
            </div>
            @endif
            @endif
            @if($withdrawal->provider_reference)
            <div class="px-5 py-3">
                <p class="text-xs text-gray-500">Provider Reference</p>
                <p class="text-sm font-mono mt-0.5" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $withdrawal->provider_reference }}</p>
            </div>
            @endif
            <div class="px-5 py-3">
                <p class="text-xs text-gray-500">Reference</p>
                <p class="text-xs font-mono mt-0.5" :class="isDark ? 'text-gray-400' : 'text-gray-600'">{{ $withdrawal->account_reference }}</p>
            </div>
        </div>

        {{-- Right: User / Review Info --}}
        <div class="space-y-4">
            <div class="rounded-2xl border divide-y" :class="isDark ? 'bg-gray-900/60 border-gray-800/60 divide-gray-800/60' : 'bg-white border-gray-200 shadow-sm divide-gray-100'">
                <div class="px-5 py-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">User</p>
                </div>
                <div class="px-5 py-3">
                    <p class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $withdrawal->user->name ?? '—' }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $withdrawal->user->email ?? '—' }}</p>
                    @if($withdrawal->wallet)
                    <p class="text-xs text-gray-500 mt-1">
                        Wallet Balance: <span class="font-mono">${{ number_format($withdrawal->wallet->balance, 2) }}</span>
                        · Locked: <span class="font-mono text-amber-400">${{ number_format($withdrawal->wallet->locked_balance, 2) }}</span>
                    </p>
                    @endif
                </div>
            </div>

            @if($withdrawal->rejection_reason)
            <div class="rounded-2xl border p-4 bg-red-500/5 border-red-500/20">
                <p class="text-xs font-semibold text-red-400 mb-1">
                    {{ $withdrawal->isFailed() ? 'Failure Reason' : 'Rejection Reason' }}
                </p>
                <p class="text-xs text-red-400/80">{{ $withdrawal->rejection_reason }}</p>
            </div>
            @endif

            @if($withdrawal->reviewer)
            <div class="rounded-2xl border divide-y" :class="isDark ? 'bg-gray-900/60 border-gray-800/60 divide-gray-800/60' : 'bg-white border-gray-200 shadow-sm divide-gray-100'">
                <div class="px-5 py-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Reviewed By</p>
                </div>
                <div class="px-5 py-3">
                    <p class="text-sm font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $withdrawal->reviewer->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $withdrawal->reviewed_at?->format('M j, Y H:i') }}</p>
                    @if($withdrawal->admin_notes)
                    <p class="text-xs text-gray-500 mt-2 italic">{{ $withdrawal->admin_notes }}</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Timeline --}}
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Timeline</p>
        <ol class="relative border-l ml-2" :class="isDark ? 'border-gray-700' : 'border-gray-200'">
            <li class="mb-4 ml-4">
                <div class="absolute w-2.5 h-2.5 rounded-full mt-1.5 -left-1.5 bg-cyan-400"></div>
                <p class="text-xs font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">Withdrawal Requested</p>
                <p class="text-xs text-gray-500">{{ $withdrawal->requested_at?->format('M j, Y H:i') ?? $withdrawal->created_at->format('M j, Y H:i') }}</p>
            </li>
            @if($withdrawal->reviewed_at)
            <li class="mb-4 ml-4">
                <div class="absolute w-2.5 h-2.5 rounded-full mt-1.5 -left-1.5
                    {{ $withdrawal->isRejected() ? 'bg-red-400' : 'bg-blue-400' }}"></div>
                <p class="text-xs font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">
                    {{ $withdrawal->isRejected() ? 'Rejected' : 'Reviewed / Approved' }}
                    @if($withdrawal->reviewer) <span class="text-gray-500">by {{ $withdrawal->reviewer->name }}</span> @endif
                </p>
                <p class="text-xs text-gray-500">{{ $withdrawal->reviewed_at->format('M j, Y H:i') }}</p>
            </li>
            @endif
            @if($withdrawal->processed_at)
            <li class="mb-4 ml-4">
                <div class="absolute w-2.5 h-2.5 rounded-full mt-1.5 -left-1.5 bg-amber-400"></div>
                <p class="text-xs font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">Marked Processing</p>
                <p class="text-xs text-gray-500">{{ $withdrawal->processed_at->format('M j, Y H:i') }}</p>
            </li>
            @endif
            @if($withdrawal->completed_at)
            <li class="ml-4">
                <div class="absolute w-2.5 h-2.5 rounded-full mt-1.5 -left-1.5
                    {{ $withdrawal->isSuccessful() ? 'bg-emerald-400' : 'bg-red-400' }}"></div>
                <p class="text-xs font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">
                    {{ $withdrawal->isSuccessful() ? 'Completed Successfully' : 'Failed' }}
                </p>
                <p class="text-xs text-gray-500">{{ $withdrawal->completed_at->format('M j, Y H:i') }}</p>
            </li>
            @endif
        </ol>
    </div>

    <div class="text-center">
        <a href="{{ route('admin.withdrawals') }}" class="text-xs text-gray-500 hover:text-cyan-400 transition-colors">← Back to Withdrawals</a>
    </div>

    {{-- ─── Modals ─────────────────────────────────────────────────────────── --}}

    {{-- Approve Modal --}}
    <div x-show="showApprove" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showApprove = false"></div>
        <div class="relative w-full max-w-md rounded-2xl border p-6 z-10"
             :class="isDark ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200 shadow-xl'">
            <h3 class="text-base font-bold mb-2" :class="isDark ? 'text-white' : 'text-gray-900'">Approve Withdrawal</h3>
            <p class="text-sm text-gray-500 mb-4">
                Approve ${{ number_format($withdrawal->usd_amount, 2) }} withdrawal for {{ $withdrawal->user->name ?? '—' }}?
                Funds remain locked until you mark as processing/successful.
            </p>
            <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Admin Notes (optional)</label>
                    <textarea name="admin_notes" rows="2" placeholder="Internal notes…"
                              class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                              :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showApprove = false"
                            class="flex-1 px-4 py-2.5 rounded-xl border text-sm font-medium transition-colors"
                            :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Cancel</button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-black text-sm font-bold transition-colors">Approve</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-show="showReject" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showReject = false"></div>
        <div class="relative w-full max-w-md rounded-2xl border p-6 z-10"
             :class="isDark ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200 shadow-xl'">
            <h3 class="text-base font-bold mb-2" :class="isDark ? 'text-white' : 'text-gray-900'">Reject Withdrawal</h3>
            <p class="text-sm text-gray-500 mb-4">Funds will be unlocked and returned to the user's available balance.</p>
            <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Rejection Reason <span class="text-red-400">*</span></label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Reason for rejection…"
                              class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-red-500/40"
                              :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Admin Notes (optional)</label>
                    <textarea name="admin_notes" rows="2" placeholder="Internal notes…"
                              class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-red-500/40"
                              :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showReject = false"
                            class="flex-1 px-4 py-2.5 rounded-xl border text-sm font-medium transition-colors"
                            :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Cancel</button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-400 text-white text-sm font-bold transition-colors">Reject & Unlock Funds</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Mark Processing Modal --}}
    <div x-show="showProcessing" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showProcessing = false"></div>
        <div class="relative w-full max-w-md rounded-2xl border p-6 z-10"
             :class="isDark ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200 shadow-xl'">
            <h3 class="text-base font-bold mb-2" :class="isDark ? 'text-white' : 'text-gray-900'">Mark as Processing</h3>
            <p class="text-sm text-gray-500 mb-4">Indicate that payment has been initiated externally.</p>
            <form method="POST" action="{{ route('admin.withdrawals.processing', $withdrawal) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Provider Reference (optional)</label>
                    <input type="text" name="provider_reference" placeholder="e.g. M-Pesa transaction code"
                           class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/40"
                           :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'">
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showProcessing = false"
                            class="flex-1 px-4 py-2.5 rounded-xl border text-sm font-medium transition-colors"
                            :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Cancel</button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-blue-500 hover:bg-blue-400 text-white text-sm font-bold transition-colors">Mark Processing</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Mark Successful Modal --}}
    <div x-show="showSuccessful" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showSuccessful = false"></div>
        <div class="relative w-full max-w-md rounded-2xl border p-6 z-10"
             :class="isDark ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200 shadow-xl'">
            <h3 class="text-base font-bold mb-2" :class="isDark ? 'text-white' : 'text-gray-900'">Mark as Successful</h3>
            <p class="text-sm text-gray-500 mb-1">
                This permanently deducts ${{ number_format($withdrawal->usd_amount, 2) }} from the user's wallet.
            </p>
            <p class="text-xs text-red-400 mb-4">⚠ This action cannot be undone. Confirm payment was sent before proceeding.</p>
            <form method="POST" action="{{ route('admin.withdrawals.successful', $withdrawal) }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Provider Reference (optional)</label>
                    <input type="text" name="provider_reference" value="{{ $withdrawal->provider_reference }}"
                           placeholder="e.g. M-Pesa confirmation code"
                           class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                           :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'">
                </div>
                @if($withdrawal->method === 'usdt_trc20')
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-500 mb-1">TXID (optional)</label>
                    <input type="text" name="txid" value="{{ $withdrawal->txid }}"
                           placeholder="Blockchain transaction ID"
                           class="w-full px-3 py-2 rounded-xl border text-sm font-mono focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                           :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'">
                </div>
                @endif
                <div class="flex gap-3">
                    <button type="button" @click="showSuccessful = false"
                            class="flex-1 px-4 py-2.5 rounded-xl border text-sm font-medium transition-colors"
                            :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Cancel</button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-sm font-bold transition-colors">Confirm & Deduct Funds</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Mark Failed Modal --}}
    <div x-show="showFailed" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showFailed = false"></div>
        <div class="relative w-full max-w-md rounded-2xl border p-6 z-10"
             :class="isDark ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200 shadow-xl'">
            <h3 class="text-base font-bold mb-2" :class="isDark ? 'text-white' : 'text-gray-900'">Mark as Failed</h3>
            <p class="text-sm text-gray-500 mb-4">Funds will be unlocked and returned to the user's available balance.</p>
            <form method="POST" action="{{ route('admin.withdrawals.failed', $withdrawal) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Reason for Failure <span class="text-red-400">*</span></label>
                    <textarea name="rejection_reason" rows="3" required placeholder="Why did the payment fail?"
                              class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-red-500/40"
                              :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showFailed = false"
                            class="flex-1 px-4 py-2.5 rounded-xl border text-sm font-medium transition-colors"
                            :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Cancel</button>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-400 text-white text-sm font-bold transition-colors">Mark Failed & Unlock Funds</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
