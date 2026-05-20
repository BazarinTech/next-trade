@extends('layouts.app')
@section('page-title', ucfirst('PAGENAME'))
@section('content')
<div class="flex flex-col items-center justify-center min-h-96 text-center">
    <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
    </div>
    <h2 class="text-lg font-bold mb-2" :class="isDark ? 'text-white' : 'text-gray-900'">Coming Soon</h2>
    <p class="text-sm text-gray-500">This section is under development.</p>
</div>
@endsection
