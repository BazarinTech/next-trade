<x-guest-layout>
    @section('title', 'Create Account | Next Trade')

    <div class="glassmorphism rounded-2xl border p-8"
         style="background: rgba(17,24,39,0.8); border-color: rgba(255,255,255,0.06);">

        <div class="mb-6">
            <h2 class="text-xl font-bold text-white">Create your account</h2>
            <p class="mt-1 text-sm text-gray-500">Start trading in minutes</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4"
              x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <input type="hidden" name="ref_code" value="{{ $refCode ?? old('ref_code') }}">

            @if(!empty($refCode ?? old('ref_code')))
            <div class="flex items-center gap-2 px-3 py-2.5 rounded-xl border"
                 style="background:rgba(6,182,212,0.06);border-color:rgba(6,182,212,0.25);">
                <svg class="w-4 h-4 flex-shrink-0" style="color:#22d3ee;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p class="text-xs" style="color:#22d3ee;">
                    You were referred with code <strong>{{ strtoupper($refCode ?? old('ref_code')) }}</strong>
                </p>
            </div>
            @endif

            <!-- Full Name + Username -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                           class="input-field w-full px-3 py-2.5 rounded-xl text-sm text-white placeholder-gray-600"
                           placeholder="John Doe">
                    @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required
                           class="input-field w-full px-3 py-2.5 rounded-xl text-sm text-white placeholder-gray-600"
                           placeholder="johndoe">
                    @error('username')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="input-field w-full px-3 py-2.5 rounded-xl text-sm text-white placeholder-gray-600"
                       placeholder="john@example.com">
                @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <!-- Phone + Country -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                           class="input-field w-full px-3 py-2.5 rounded-xl text-sm text-white placeholder-gray-600"
                           placeholder="+1 234 567 8900">
                    @error('phone')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Country</label>
                    <select name="country"
                            class="input-field w-full px-3 py-2.5 rounded-xl text-sm text-white appearance-none cursor-pointer"
                            style="background: rgba(255,255,255,0.03);">
                        <option value="" class="bg-gray-900">Select country</option>
                        @foreach([
                            'US'=>'United States','GB'=>'United Kingdom','NG'=>'Nigeria',
                            'GH'=>'Ghana','KE'=>'Kenya','ZA'=>'South Africa',
                            'IN'=>'India','CA'=>'Canada','AU'=>'Australia',
                            'DE'=>'Germany','FR'=>'France','JP'=>'Japan',
                            'SG'=>'Singapore','AE'=>'UAE','BR'=>'Brazil',
                        ] as $code => $name)
                        <option value="{{ $code }}" class="bg-gray-900" {{ old('country') === $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                        @endforeach
                    </select>
                    @error('country')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Password + Confirm -->
            <div class="grid grid-cols-2 gap-3">
                <div x-data="{ show: false }">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Password</label>
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
                    @error('password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div x-data="{ show: false }">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password_confirmation" required
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
            </div>

            <button type="submit" :disabled="loading"
                    class="w-full py-3 rounded-xl text-sm font-semibold text-white transition-all duration-200 mt-2 disabled:opacity-70 disabled:cursor-not-allowed"
                    style="background: linear-gradient(135deg, #06b6d4, #0891b2); box-shadow: 0 4px 15px rgba(6,182,212,0.3);"
                    x-on:mouseover="!loading && (this.style.transform='translateY(-1px)', this.style.boxShadow='0 6px 20px rgba(6,182,212,0.4)')"
                    x-on:mouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(6,182,212,0.3)'">
                <span x-show="!loading">Create Account</span>
                <span x-show="loading" class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                        <path fill="currentColor" d="M4 12a8 8 0 018-8v8z" class="opacity-75"/>
                    </svg>
                    Creating account…
                </span>
            </button>
        </form>

        <p class="mt-5 text-center text-xs text-gray-600">
            Already have an account?
            <a href="{{ route('login') }}" class="text-cyan-400 hover:text-cyan-300 font-medium transition-colors">Sign in</a>
        </p>
    </div>
</x-guest-layout>
