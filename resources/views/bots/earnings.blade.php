@extends('layouts.trading')
@section('title', 'Bot Earnings | Next Trade')
@section('page-title', 'Bot Earnings')
@section('page-subtitle', 'Your daily simulated earnings history')

@section('content')

{{-- Summary --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Total Credited</p>
        <p class="text-2xl font-bold text-emerald-400">${{ number_format($totalCredited, 2) }}</p>
        <p class="text-xs mt-1 text-gray-600">{{ ucfirst($walletMode) }} wallet</p>
    </div>
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Total Records</p>
        <p class="text-2xl font-bold text-cyan-400">{{ $earnings->total() }}</p>
        <p class="text-xs mt-1 text-gray-600">Earnings entries</p>
    </div>
    <div class="flex items-center justify-center rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <a href="{{ route('bots.index') }}" class="flex items-center gap-2 text-sm font-semibold text-cyan-400 hover:text-cyan-300 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Bots
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="mb-5">
    <form method="GET" action="{{ route('bots.earnings') }}" class="flex items-center gap-3">
        <select name="status"
                class="text-sm px-3 py-2 rounded-xl border appearance-none"
                :class="isDark ? 'bg-gray-900 border-gray-700 text-white' : 'bg-white border-gray-200 text-gray-900'"
                onchange="this.form.submit()">
            <option value="" :class="isDark ? 'bg-gray-900':''">All Statuses</option>
            @foreach(['credited','pending','failed'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }} :class="isDark ? 'bg-gray-900':''">{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        @if(request()->filled('status'))
        <a href="{{ route('bots.earnings') }}" class="text-xs text-red-400 hover:text-red-300 transition-colors">Clear</a>
        @endif
    </form>
</div>

{{-- Earnings Table --}}
<div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    @if($earnings->isEmpty())
    <div class="py-16 text-center">
        <p class="text-sm text-gray-500 mb-2">No earnings found.</p>
        <a href="{{ route('bots.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">Start investing →</a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b text-xs font-semibold text-gray-500" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
                    <th class="px-5 py-3 text-left">Bot Plan</th>
                    <th class="px-5 py-3 text-right">Amount</th>
                    <th class="px-5 py-3 text-center">ROI %</th>
                    <th class="px-5 py-3 text-center">Date</th>
                    <th class="px-5 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($earnings as $earning)
                <tr class="border-b last:border-0 transition-colors" :class="isDark ? 'border-gray-800/40 hover:bg-gray-800/20' : 'border-gray-50 hover:bg-gray-50'">
                    <td class="px-5 py-3">
                        <p class="text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $earning->botPlan->name }}</p>
                        <p class="text-[10px] text-gray-600">Investment #{{ $earning->bot_investment_id }}</p>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <span class="text-sm font-bold text-emerald-400">+${{ number_format($earning->amount, 4) }}</span>
                    </td>
                    <td class="px-5 py-3 text-center text-xs text-gray-400">{{ $earning->roi_percent }}%</td>
                    <td class="px-5 py-3 text-center text-xs text-gray-400">{{ $earning->earning_date->format('M d, Y') }}</td>
                    <td class="px-5 py-3 text-center">
                        @php $colors = ['credited'=>'emerald','pending'=>'amber','failed'=>'red']; $c = $colors[$earning->status] ?? 'gray'; @endphp
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-{{ $c }}-500/10 text-{{ $c }}-400 border border-{{ $c }}-500/20">
                            {{ ucfirst($earning->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($earnings->hasPages())
    <div class="px-5 py-4 border-t" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        {{ $earnings->links() }}
    </div>
    @endif
    @endif
</div>

@endsection
