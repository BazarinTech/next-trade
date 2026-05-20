<x-guest-layout>
    @section('title', 'Sign In — Next Trade')

    <div class="glassmorphism rounded-2xl border p-8"
         style="background: rgba(17,24,39,0.8); border-color: rgba(255,255,255,0.06);">

        <div class="mb-6">
            <h2 class="text-xl font-bold text-white">Welcome back</h2>
            <p class="mt-1 text-sm text-gray-500">Sign in to your trading account</p>
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

        <form method="POST" action="{{ route('login') }}" class="space-y-4"
              x-data="{ loading: false }" @submit="loading = true">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="input-field w-full px-3 py-2.5 rounded-xl text-sm text-white placeholder-gray-600"
                       placeholder="john@example.com">
            </div>

            <div x-data="{ show: false }">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-medium text-gray-400">Password</label>
                    @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">
                        Forgot password?
                    </a>
                    @endif
                </div>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" name="password" required
                           class="input-field w-full px-3 py-2.5 pr-10 rounded-xl text-sm text-white placeholder-gray-600"
                           placeholder="••••••••">
                    <button type="button" @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors focus:outline-none"
                            :title="show ? 'Hide password' : 'Show password'">
                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember"
                       class="w-4 h-4 rounded border-gray-700 bg-gray-800 text-cyan-500 cursor-pointer">
                <label for="remember" class="text-xs text-gray-500 cursor-pointer">Remember me</label>
            </div>

            <button type="submit" :disabled="loading"
                    class="w-full py-3 rounded-xl text-sm font-semibold text-white transition-all duration-200 mt-2 disabled:opacity-70 disabled:cursor-not-allowed"
                    style="background: linear-gradient(135deg, #06b6d4, #0891b2); box-shadow: 0 4px 15px rgba(6,182,212,0.3);"
                    x-on:mouseover="!loading && (this.style.transform='translateY(-1px)', this.style.boxShadow='0 6px 20px rgba(6,182,212,0.4)')"
                    x-on:mouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(6,182,212,0.3)'">
                <span x-show="!loading">Sign In</span>
                <span x-show="loading" class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                        <path fill="currentColor" d="M4 12a8 8 0 018-8v8z" class="opacity-75"/>
                    </svg>
                    Signing in…
                </span>
            </button>
        </form>

        <p class="mt-5 text-center text-xs text-gray-600">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-cyan-400 hover:text-cyan-300 font-medium transition-colors">Create one</a>
        </p>
    </div>
</x-guest-layout>
