@extends('layouts.trading')
@section('title', 'Trading Bots | Next Trade')
@section('page-title', 'Trading Bots')
@section('page-subtitle', 'Automate your trading strategy')

@section('content')
<div class="flex flex-col items-center justify-center min-h-96 text-center">
    <div class="w-16 h-16 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H7a2 2 0 00-2 2v2M9 3h6M9 3v2m6-2h2a2 2 0 012 2v2"/>
            <rect x="9" y="9" width="6" height="6" rx="1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
        </svg>
    </div>
    <h2 class="text-lg font-bold mb-2" :class="isDark ? 'text-white' : 'text-gray-900'">Trading Bots</h2>
    <p class="text-sm text-gray-500 max-w-sm">Automated trading bots with customizable strategies coming in Phase 2.</p>
</div>
@endsection
