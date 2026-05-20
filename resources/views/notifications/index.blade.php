@extends('layouts.trading')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Your recent activity and alerts')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2">
        @if($unreadCount > 0)
        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-cyan-500/15 text-cyan-400 border border-cyan-500/20">{{ $unreadCount }} unread</span>
        @endif
    </div>
    @if($unreadCount > 0)
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button type="submit" class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-xs font-semibold transition-colors">
            Mark all as read
        </button>
    </form>
    @endif
</div>

{{-- Filters --}}
<form method="GET" class="flex gap-3 mb-6">
    <select name="type" class="px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
            :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
        <option value="">All Types</option>
        @foreach(['deposit_successful','deposit_failed','withdrawal_requested','withdrawal_successful','withdrawal_rejected','trade_won','trade_lost','bot_earning','account_banned','wallet_frozen','system_notice'] as $t)
        <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $t)) }}</option>
        @endforeach
    </select>
    <label class="flex items-center gap-2 px-3 py-2 rounded-xl border cursor-pointer text-sm"
           :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
        <input type="checkbox" name="unread" value="1" {{ request('unread') ? 'checked' : '' }} class="rounded">
        Unread only
    </label>
    <button type="submit" class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-xs font-semibold transition-colors">Filter</button>
    <a href="{{ route('notifications.index') }}" class="px-4 py-2 rounded-xl border text-xs font-medium transition-colors"
       :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Clear</a>
</form>

{{-- Notifications list --}}
<div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    @forelse($notifications as $notif)
    <div class="flex items-start gap-4 px-5 py-4 border-b transition-colors {{ !$notif->isRead() ? '' : '' }} hover:bg-gray-500/5"
         :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        {{-- Icon dot --}}
        <div class="mt-1 flex-shrink-0">
            <span class="w-2 h-2 rounded-full block {{ !$notif->isRead() ? 'bg-cyan-400' : 'bg-gray-600' }}"></span>
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium {{ !$notif->isRead() ? '' : 'opacity-75' }}" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $notif->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $notif->message }}</p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span class="text-[11px] text-gray-600 whitespace-nowrap">{{ $notif->created_at->diffForHumans() }}</span>
                    @if(!$notif->isRead())
                    <form method="POST" action="{{ route('notifications.read', $notif) }}">
                        @csrf
                        <button type="submit" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors font-medium">Mark read</button>
                    </form>
                    @else
                    <span class="text-[10px] text-gray-600">Read</span>
                    @endif
                </div>
            </div>
            <span class="inline-block mt-1.5 px-2 py-0.5 rounded text-[10px] font-medium bg-gray-500/10 text-gray-500">{{ ucwords(str_replace('_', ' ', $notif->type)) }}</span>
        </div>
    </div>
    @empty
    <div class="px-5 py-16 text-center">
        <svg class="w-10 h-10 mx-auto mb-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <p class="text-sm text-gray-500">No notifications found.</p>
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
<div class="mt-4">{{ $notifications->links() }}</div>
@endif

@endsection
