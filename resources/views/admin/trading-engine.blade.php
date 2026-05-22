@extends('layouts.app')
@section('title', 'Trading Engine | Admin')
@section('page-title', 'Trading Engine')
@section('page-subtitle', 'Simulation difficulty and market parameters')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
    {{ session('error') }}
</div>
@endif

<!-- Difficulty Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8" x-data="{ editId: null }">

    @foreach($configs as $config)
    @php $color = $config->difficulty_color; @endphp
    <div class="rounded-2xl border transition-all duration-200 {{ $config->is_active ? 'border-'.$color.'-500/50 ring-1 ring-'.$color.'-500/30' : 'border-gray-800/60' }}"
         :class="isDark ? 'bg-gray-900/60' : 'bg-white shadow-sm'">

        <!-- Card Header -->
        <div class="p-5 border-b border-gray-800/40">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-{{ $color }}-500/20 flex items-center justify-center">
                        <svg class="w-4 h-4 text-{{ $color }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-{{ $color }}-400">{{ $config->difficulty_label }}</p>
                        @if($config->is_active)
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-{{ $color }}-500/20 text-{{ $color }}-400">ACTIVE</span>
                        @endif
                    </div>
                </div>
                <button @click="editId = editId === {{ $config->id }} ? null : {{ $config->id }}"
                        class="text-xs text-gray-500 hover:text-gray-300 transition-colors p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </button>
            </div>

            <!-- Stats -->
            <div class="space-y-2">
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Win rate</span>
                    <span class="font-semibold text-{{ $color }}-400">{{ $config->win_probability }}%</span>
                </div>
                <div class="w-full bg-gray-800 rounded-full h-1">
                    <div class="h-1 rounded-full bg-{{ $color }}-500" style="width:{{ $config->win_probability }}%"></div>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Payout range</span>
                    <span class="font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $config->payout_range }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Candle speed</span>
                    <span class="font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $config->candle_speed_seconds }}s</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-gray-500">Volatility ×</span>
                    <span class="font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $config->volatility_multiplier }}</span>
                </div>
            </div>
        </div>

        <!-- Activate Button -->
        @if(!$config->is_active)
        <div class="p-3">
            <form method="POST" action="{{ route('admin.trading-engine.activate', $config) }}">
                @csrf
                <button type="submit" class="w-full text-xs py-2 rounded-lg bg-{{ $color }}-500/10 text-{{ $color }}-400 border border-{{ $color }}-500/20 hover:bg-{{ $color }}-500/20 transition-colors font-medium">
                    Activate
                </button>
            </form>
        </div>
        @else
        <div class="p-3">
            <div class="w-full text-xs py-2 rounded-lg bg-{{ $color }}-500/20 text-{{ $color }}-400 border border-{{ $color }}-500/30 text-center font-medium">
                Currently Active
            </div>
        </div>
        @endif

        <!-- Edit Form (inline expand) -->
        <div x-show="editId === {{ $config->id }}" x-transition class="border-t border-gray-800/40">
            <form method="POST" action="{{ route('admin.trading-engine.settings') }}" class="p-4 space-y-3">
                @csrf
                <input type="hidden" name="id" value="{{ $config->id }}">
                <p class="text-xs font-semibold text-gray-400 mb-2">Edit {{ $config->difficulty_label }} settings</p>

                <div>
                    <label class="block text-[10px] text-gray-500 mb-1">Base Win % (no sentiment)</label>
                    <input type="number" name="win_probability" value="{{ $config->win_probability }}"
                           min="0" max="100" step="0.01"
                           class="w-full text-xs px-3 py-2 rounded-lg border bg-transparent focus:outline-none focus:ring-1 focus:ring-{{ $color }}-500/50"
                           :class="isDark ? 'border-gray-700 text-gray-200' : 'border-gray-300 text-gray-800'">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] text-gray-500 mb-1">Min Profit</label>
                        <input type="number" name="min_profit_multiplier" value="{{ $config->min_profit_multiplier }}"
                               min="0.01" max="1" step="0.01"
                               class="w-full text-xs px-3 py-2 rounded-lg border bg-transparent focus:outline-none focus:ring-1 focus:ring-{{ $color }}-500/50"
                               :class="isDark ? 'border-gray-700 text-gray-200' : 'border-gray-300 text-gray-800'">
                    </div>
                    <div>
                        <label class="block text-[10px] text-gray-500 mb-1">Max Profit</label>
                        <input type="number" name="max_profit_multiplier" value="{{ $config->max_profit_multiplier }}"
                               min="0.01" max="1" step="0.01"
                               class="w-full text-xs px-3 py-2 rounded-lg border bg-transparent focus:outline-none focus:ring-1 focus:ring-{{ $color }}-500/50"
                               :class="isDark ? 'border-gray-700 text-gray-200' : 'border-gray-300 text-gray-800'">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] text-gray-500 mb-1">Volatility ×</label>
                        <input type="number" name="volatility_multiplier" value="{{ $config->volatility_multiplier }}"
                               min="0.01" max="10" step="0.01"
                               class="w-full text-xs px-3 py-2 rounded-lg border bg-transparent focus:outline-none focus:ring-1 focus:ring-{{ $color }}-500/50"
                               :class="isDark ? 'border-gray-700 text-gray-200' : 'border-gray-300 text-gray-800'">
                    </div>
                    <div>
                        <label class="block text-[10px] text-gray-500 mb-1">Sentiment Sensitivity</label>
                        <input type="number" name="trend_strength" value="{{ $config->trend_strength }}"
                               min="0" max="2" step="0.01"
                               class="w-full text-xs px-3 py-2 rounded-lg border bg-transparent focus:outline-none focus:ring-1 focus:ring-{{ $color }}-500/50"
                               :class="isDark ? 'border-gray-700 text-gray-200' : 'border-gray-300 text-gray-800'">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] text-gray-500 mb-1">Max Loss ×</label>
                        <input type="number" name="max_loss_multiplier" value="{{ $config->max_loss_multiplier }}"
                               min="0.01" max="2" step="0.01"
                               class="w-full text-xs px-3 py-2 rounded-lg border bg-transparent focus:outline-none focus:ring-1 focus:ring-{{ $color }}-500/50"
                               :class="isDark ? 'border-gray-700 text-gray-200' : 'border-gray-300 text-gray-800'">
                    </div>
                    <div>
                        <label class="block text-[10px] text-gray-500 mb-1">Candle Speed (s)</label>
                        <input type="number" name="candle_speed_seconds" value="{{ $config->candle_speed_seconds }}"
                               min="1" max="60" step="1"
                               class="w-full text-xs px-3 py-2 rounded-lg border bg-transparent focus:outline-none focus:ring-1 focus:ring-{{ $color }}-500/50"
                               :class="isDark ? 'border-gray-700 text-gray-200' : 'border-gray-300 text-gray-800'">
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="submit" class="flex-1 text-xs py-2 rounded-lg bg-{{ $color }}-500 text-white hover:bg-{{ $color }}-600 transition-colors font-medium">
                        Save
                    </button>
                    <button type="button" @click="editId = null" class="flex-1 text-xs py-2 rounded-lg border border-gray-700 text-gray-400 hover:text-gray-200 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>

<!-- Reset + Activity Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Reset Defaults -->
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <h3 class="text-sm font-semibold mb-1" :class="isDark ? 'text-white' : 'text-gray-900'">Factory Reset</h3>
        <p class="text-xs text-gray-500 mb-4">Restore all four difficulty presets to their factory defaults. The active difficulty will be set to Normal.</p>
        <form method="POST" action="{{ route('admin.trading-engine.reset') }}"
              onsubmit="return confirm('Reset all simulation settings to factory defaults?')">
            @csrf
            <button type="submit" class="w-full text-xs py-2.5 rounded-lg bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 transition-colors font-medium">
                Reset to Defaults
            </button>
        </form>
    </div>

    <!-- Admin Activity Log -->
    <div class="lg:col-span-2 rounded-2xl border" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <div class="flex items-center justify-between p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
            <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Recent Activity</h3>
            <span class="text-xs text-gray-500">Last {{ count($recentLogs) }} actions</span>
        </div>
        <div class="overflow-y-auto max-h-80">
            @forelse($recentLogs as $log)
            <div class="flex items-start gap-3 px-5 py-3 border-b" :class="isDark ? 'border-gray-800/30' : 'border-gray-50'">
                <div class="w-7 h-7 rounded-full bg-gray-800 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-[10px] font-bold text-gray-400">{{ strtoupper(substr($log->admin?->name ?? 'S', 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline gap-2">
                        <p class="text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $log->action_label }}</p>
                        <span class="text-[10px] text-gray-600">by {{ $log->admin?->name ?? 'System' }}</span>
                    </div>
                    @if(!empty($log->new_values))
                    <p class="text-[10px] text-gray-600 mt-0.5 truncate font-mono">
                        {{ collect($log->new_values)->map(fn($v,$k)=>"{$k}: {$v}")->implode(', ') }}
                    </p>
                    @endif
                    <p class="text-[10px] text-gray-600 mt-0.5">{{ $log->created_at->format('M d, H:i') }}</p>
                </div>
            </div>
            @empty
            <p class="px-5 py-8 text-center text-sm text-gray-500">No admin activity logged yet.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
