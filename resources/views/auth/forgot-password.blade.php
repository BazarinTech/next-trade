<x-guest-layout>
    @section('title', 'Reset Password | Next Trade')

    <div class="glassmorphism rounded-2xl border p-8"
         style="background: rgba(17,24,39,0.8); border-color: rgba(255,255,255,0.06);">

        <div class="mb-6">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white">Forgot password?</h2>
            <p class="mt-1 text-sm text-gray-500">Enter your email and we'll send you a reset link.</p>
        </div>

        @if(session('status'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs">
            {{ session('status') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4"
              x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="input-field w-full px-3 py-2.5 rounded-xl text-sm text-white placeholder-gray-600"
                       placeholder="john@example.com">
            </div>

            <button type="submit" :disabled="loading"
                    class="w-full py-3 rounded-xl text-sm font-semibold text-white transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed"
                    style="background: linear-gradient(135deg, #06b6d4, #0891b2); box-shadow: 0 4px 15px rgba(6,182,212,0.3);"
                    x-on:mouseover="!loading && (this.style.transform='translateY(-1px)')"
                    x-on:mouseout="this.style.transform='translateY(0)'">
                <span x-show="!loading">Send Reset Link</span>
                <span x-show="loading" class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                        <path fill="currentColor" d="M4 12a8 8 0 018-8v8z" class="opacity-75"/>
                    </svg>
                    Sending…
                </span>
            </button>
        </form>

        <p class="mt-5 text-center text-xs text-gray-600">
            <a href="{{ route('login') }}" class="text-cyan-400 hover:text-cyan-300 font-medium transition-colors flex items-center justify-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to sign in
            </a>
        </p>
    </div>
</x-guest-layout>
