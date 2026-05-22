@extends('layouts.app')
@section('title', 'Referrals | Admin')
@section('page-title', 'Referral Programme')
@section('page-subtitle', 'Commission tracking and referral statistics')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total Paid Out',   '$'.number_format($totalPaid, 2),   'text-emerald-400', 'bg-emerald-500/10 border-emerald-500/20', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Commissions Paid',  number_format($totalCommissions),  'text-cyan-400',    'bg-cyan-500/10 border-cyan-500/20',       'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Active Referrers',  number_format($totalReferrers),    'text-violet-400',  'bg-violet-500/10 border-violet-500/20',   'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ['Failed',            number_format($totalFailed),       'text-red-400',     'bg-red-500/10 border-red-500/20',         'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ] as [$label, $val, $textColor, $bgBorder, $icon])
    <div class="rounded-2xl border p-4 {{ $bgBorder }}" :class="isDark ? '' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 {{ $textColor }} flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $icon }}"/></svg>
            <div>
                <p class="text-lg font-bold {{ $textColor }}">{{ $val }}</p>
                <p class="text-xs text-gray-500">{{ $label }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Commission log --}}
    <div class="xl:col-span-2 rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="px-5 py-4 border-b flex items-center justify-between gap-3" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <p class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Commission Log</p>
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="referrer" value="{{ request('referrer') }}" placeholder="Search referrer…"
                       class="px-3 py-1.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500/40 w-44"
                       :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-600' : 'bg-white border-gray-300 text-gray-900'">
                <select name="status" class="px-3 py-1.5 rounded-xl border text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                        :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
                    <option value="">All statuses</option>
                    <option value="paid"   {{ request('status') === 'paid'   ? 'selected' : '' }}>Paid</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-xs font-semibold transition-colors">Filter</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-xs text-gray-500" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
                        <th class="px-5 py-3 text-left font-medium">Referrer</th>
                        <th class="px-5 py-3 text-left font-medium">Referred</th>
                        <th class="px-5 py-3 text-right font-medium">Deposit</th>
                        <th class="px-5 py-3 text-right font-medium">Rate</th>
                        <th class="px-5 py-3 text-right font-medium">Commission</th>
                        <th class="px-5 py-3 text-center font-medium">Status</th>
                        <th class="px-5 py-3 text-right font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :class="isDark ? 'divide-gray-800/40' : 'divide-gray-100'">
                    @forelse($commissions as $c)
                    <tr>
                        <td class="px-5 py-3">
                            <p class="text-xs font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $c->referrer->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $c->referrer->email ?? '' }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <p class="text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $c->referred->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $c->referred->email ?? '' }}</p>
                        </td>
                        <td class="px-5 py-3 text-right text-xs font-mono" :class="isDark ? 'text-gray-300' : 'text-gray-700'">${{ number_format($c->deposit_amount_usd, 2) }}</td>
                        <td class="px-5 py-3 text-right text-xs text-gray-500">{{ $c->rate }}%</td>
                        <td class="px-5 py-3 text-right text-xs font-bold text-emerald-400">+${{ number_format($c->commission_amount_usd, 2) }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $c->status === 'paid' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}">
                                {{ ucfirst($c->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right text-xs text-gray-500 whitespace-nowrap">{{ $c->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500">No commissions yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($commissions->hasPages())
        <div class="px-5 py-4 border-t" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            {{ $commissions->links() }}
        </div>
        @endif
    </div>

    {{-- Top referrers --}}
    <div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="px-5 py-4 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <p class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Top Referrers</p>
        </div>
        <div class="divide-y" :class="isDark ? 'divide-gray-800/40' : 'divide-gray-100'">
            @forelse($topReferrers as $i => $referrer)
            <div class="px-5 py-3 flex items-center gap-3">
                <span class="text-xs font-bold w-5 text-center" :class="isDark ? 'text-gray-500' : 'text-gray-400'">{{ $i + 1 }}</span>
                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-cyan-500 to-cyan-700 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-white">{{ strtoupper(substr($referrer->name, 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold truncate" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $referrer->name }}</p>
                    <p class="text-xs text-gray-500">{{ $referrer->total_referrals }} referral{{ $referrer->total_referrals != 1 ? 's' : '' }}</p>
                </div>
                <p class="text-xs font-bold text-emerald-400 flex-shrink-0">${{ number_format($referrer->total_earned ?? 0, 2) }}</p>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-sm text-gray-500">No referrers yet.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection
