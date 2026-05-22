@extends('layouts.app')
@section('title', 'Users | Admin')
@section('page-title', 'Users')
@section('page-subtitle', 'Manage platform users')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border p-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Total Users</p>
        <p class="text-2xl font-bold text-cyan-400">{{ $total }}</p>
    </div>
    <div class="rounded-2xl border p-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Admins</p>
        <p class="text-2xl font-bold text-violet-400">{{ $admins }}</p>
    </div>
    <div class="rounded-2xl border p-4" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">Banned</p>
        <p class="text-2xl font-bold text-red-400">{{ $banned }}</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="rounded-2xl border p-4 mb-6" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, phone…"
               class="col-span-2 px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
               :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900'">
        <select name="country" class="px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
            <option value="">All Countries</option>
            @foreach($countries as $c)
            <option value="{{ $c }}" {{ request('country') === $c ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
        </select>
        <select name="status" class="px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
            <option value="">All Statuses</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
            <option value="admin"  {{ request('status') === 'admin'  ? 'selected' : '' }}>Admin</option>
        </select>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-sm font-semibold transition-colors">Filter</button>
            <a href="{{ route('admin.users') }}" class="px-4 py-2 rounded-xl border text-sm font-medium transition-colors"
               :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Clear</a>
        </div>
    </div>
</form>

{{-- Table --}}
<div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
                    @foreach(['User','Country','Live Balance','Demo Balance','Roles','Status','Joined',''] as $h)
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y" :class="isDark ? 'divide-gray-800/60' : 'divide-gray-100'">
                @forelse($users as $u)
                <tr class="hover:bg-gray-500/5 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-cyan-700 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-white">{{ strtoupper(substr($u->name,0,1)) }}</span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $u->name }}</p>
                                <p class="text-xs text-gray-500">{{ $u->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $u->country ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs font-mono">
                        @php $live = $u->wallets->where('type','live')->first(); @endphp
                        <span :class="isDark ? 'text-gray-300' : 'text-gray-700'">${{ number_format($live?->balance ?? 0, 2) }}</span>
                        @if($live?->isFrozen())
                        <span class="ml-1 text-red-400 text-[10px]">frozen</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs font-mono text-gray-500">${{ number_format($u->wallets->where('type','demo')->first()?->balance ?? 0, 2) }}</td>
                    <td class="px-5 py-3">
                        @foreach($u->adminRoles as $role)
                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium bg-violet-500/15 text-violet-400 border border-violet-500/20 mr-1">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td class="px-5 py-3">
                        @if($u->is_banned)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/15 border border-red-500/25 text-red-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Banned
                        </span>
                        @elseif($u->is_admin)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-violet-500/15 border border-violet-500/25 text-violet-400">Admin</span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/15 border border-emerald-500/25 text-emerald-400">Active</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $u->created_at->format('M j, Y') }}</td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <a href="{{ route('admin.users.show', $u) }}" class="text-xs font-medium text-cyan-400 hover:text-cyan-300 transition-colors">View →</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-sm text-gray-500">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-5 py-4 border-t" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
        {{ $users->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
