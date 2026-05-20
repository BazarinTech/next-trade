@extends('layouts.app')
@section('title', 'Audit Logs — Admin')
@section('page-title', 'Audit Logs')
@section('page-subtitle', 'Admin action history')

@section('content')

{{-- Filters --}}
<form method="GET" class="rounded-2xl border p-4 mb-6" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
        <input type="text" name="action" value="{{ request('action') }}" placeholder="Action…"
               class="px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
               :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'">
        <input type="text" name="target_type" value="{{ request('target_type') }}" placeholder="Model type…"
               class="px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
               :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'">
        <select name="admin_id" class="px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
            <option value="">All Admins</option>
            @foreach($admins as $admin)
            <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ request('date') }}"
               class="px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
               :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-sm font-semibold transition-colors">Filter</button>
            <a href="{{ route('admin.audit-logs') }}" class="px-4 py-2 rounded-xl border text-sm font-medium transition-colors"
               :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Clear</a>
        </div>
    </div>
</form>

{{-- Log Table --}}
<div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
                    @foreach(['Admin','Action','Model','Changes','When'] as $h)
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y" :class="isDark ? 'divide-gray-800/60' : 'divide-gray-100'">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-500/5 transition-colors" x-data="{ expanded: false }">
                    <td class="px-5 py-3">
                        <p class="text-xs font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $log->admin?->name ?? 'System' }}</p>
                        <p class="text-xs text-gray-500">{{ $log->admin?->email ?? '' }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-medium font-mono
                            @php
                                $color = match(true) {
                                    str_contains($log->action, 'ban')    => 'bg-red-500/15 text-red-400 border border-red-500/20',
                                    str_contains($log->action, 'delete') => 'bg-red-500/15 text-red-400 border border-red-500/20',
                                    str_contains($log->action, 'create') => 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20',
                                    str_contains($log->action, 'update') => 'bg-cyan-500/15 text-cyan-400 border border-cyan-500/20',
                                    default => 'bg-gray-500/15 text-gray-400 border border-gray-500/20',
                                };
                            @endphp
                            {{ $color }}">{{ $log->action }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">
                        @if($log->target_type)
                        <span class="font-mono">{{ class_basename($log->target_type) }}</span>
                        @if($log->target_id)
                        <span class="text-gray-600"> #{{ $log->target_id }}</span>
                        @endif
                        @else
                        —
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs max-w-xs">
                        @if($log->old_values || $log->new_values)
                        <button @click="expanded = !expanded" class="text-cyan-400 hover:text-cyan-300 transition-colors">
                            <span x-text="expanded ? 'Hide' : 'Show diff'"></span>
                        </button>
                        <div x-show="expanded" x-cloak class="mt-2 space-y-1">
                            @if($log->old_values)
                            <div>
                                <p class="text-[10px] font-semibold text-red-400 mb-0.5">Before</p>
                                <pre class="text-[10px] text-gray-500 whitespace-pre-wrap break-all font-mono">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                            @endif
                            @if($log->new_values)
                            <div>
                                <p class="text-[10px] font-semibold text-emerald-400 mb-0.5">After</p>
                                <pre class="text-[10px] text-gray-500 whitespace-pre-wrap break-all font-mono">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                            @endif
                        </div>
                        @else
                        <span class="text-gray-600">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('M j, Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">No audit logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="px-5 py-4 border-t" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
