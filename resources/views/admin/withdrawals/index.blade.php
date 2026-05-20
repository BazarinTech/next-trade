@extends('layouts.app')
@section('title', 'Withdrawals — Admin')
@section('page-title', 'Withdrawals')
@section('page-subtitle', 'Review and process withdrawal requests')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

{{-- Stats Row --}}
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
    @foreach([
        ['label' => 'Pending',       'value' => $pendingCount,    'color' => 'amber'],
        ['label' => 'Processing',    'value' => $processingCount, 'color' => 'blue'],
        ['label' => 'Approved',      'value' => $approvedCount,   'color' => 'cyan'],
        ['label' => 'Done Today',    'value' => $successfulToday, 'color' => 'emerald'],
        ['label' => 'Rejected Today','value' => $rejectedToday,   'color' => 'red'],
        ['label' => 'USD Today',     'value' => '$'.number_format($totalUsdToday,2), 'color' => 'violet'],
        ['label' => 'Total Locked',  'value' => '$'.number_format($totalLocked,2),  'color' => 'gray'],
    ] as $stat)
    <div class="rounded-2xl border p-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">{{ $stat['label'] }}</p>
        <p class="text-lg font-bold text-{{ $stat['color'] }}-400">{{ $stat['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" class="rounded-2xl border p-4 mb-6" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <select name="status" class="px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
            <option value="">All Statuses</option>
            @foreach(['pending','approved','processing','successful','failed','rejected','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="method" class="px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
            <option value="">All Methods</option>
            <option value="mpesa" {{ request('method') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
            <option value="usdt_trc20" {{ request('method') === 'usdt_trc20' ? 'selected' : '' }}>USDT TRC20</option>
        </select>
        <input type="text" name="email" value="{{ request('email') }}" placeholder="User email…"
               class="px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
               :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'">
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
               :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
               :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-sm font-semibold transition-colors">Filter</button>
            <a href="{{ route('admin.withdrawals') }}" class="px-4 py-2 rounded-xl border text-sm font-medium transition-colors"
               :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600 hover:text-gray-900'">Clear</a>
        </div>
    </div>
</form>

{{-- Table --}}
<div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
                    @foreach(['User', 'Method', 'Amount', 'Status', 'Reference', 'Requested', 'Action'] as $h)
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y" :class="isDark ? 'divide-gray-800/60' : 'divide-gray-100'">
                @forelse($withdrawals as $wd)
                <tr class="hover:bg-gray-500/5 transition-colors">
                    <td class="px-5 py-3">
                        <p class="text-xs font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $wd->user->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ $wd->user->email ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <span class="text-xs" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $wd->method_label }}</span>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <p class="text-xs font-mono font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">${{ number_format($wd->usd_amount, 2) }}</p>
                        @if($wd->method === 'mpesa')
                        <p class="text-xs text-gray-500">KES {{ number_format($wd->local_amount, 2) }}</p>
                        @else
                        <p class="text-xs text-gray-500">{{ number_format($wd->net_amount, 2) }} USDT</p>
                        @endif
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                            bg-{{ $wd->status_color }}-500/15 border border-{{ $wd->status_color }}-500/25 text-{{ $wd->status_color }}-400">
                            @if(in_array($wd->status, ['pending','processing','approved']))
                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $wd->status_color }}-400 animate-pulse"></span>
                            @endif
                            {{ ucfirst($wd->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-xs font-mono text-gray-500 truncate max-w-[140px]">{{ $wd->account_reference }}</p>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <p class="text-xs text-gray-500">{{ $wd->created_at->format('M j, Y') }}</p>
                        <p class="text-xs text-gray-600">{{ $wd->created_at->format('H:i') }}</p>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <a href="{{ route('admin.withdrawals.show', $wd) }}"
                           class="text-xs font-medium transition-colors
                            {{ in_array($wd->status, ['pending','approved']) ? 'text-cyan-400 hover:text-cyan-300' : 'text-gray-500 hover:text-gray-300' }}">
                            {{ in_array($wd->status, ['pending','approved']) ? 'Review →' : 'View →' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500">No withdrawals found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($withdrawals->hasPages())
    <div class="px-5 py-4 border-t" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
        {{ $withdrawals->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
