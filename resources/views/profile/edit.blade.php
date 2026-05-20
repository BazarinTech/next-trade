@extends('layouts.trading')
@section('title', 'Profile — Next Trade')
@section('page-title', 'Profile')
@section('page-subtitle', 'Manage your account details')

@section('content')
<div class="max-w-2xl space-y-4">

    <!-- Profile Info Card -->
    <div class="rounded-2xl border p-6" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <h3 class="text-sm font-semibold mb-5" :class="isDark ? 'text-white' : 'text-gray-900'">Profile Information</h3>

        <!-- Avatar -->
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-cyan-500 to-cyan-700 flex items-center justify-center">
                <span class="text-2xl font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </div>
            <div>
                <p class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500">@{{ auth()->user()->username }}</p>
                <button class="mt-1.5 text-xs text-cyan-400 hover:text-cyan-300 transition-colors">Change avatar</button>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('patch')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium mb-1.5" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                           class="w-full px-3 py-2.5 rounded-xl text-sm border transition-all"
                           :class="isDark ? 'bg-gray-800/60 border-gray-700 text-white focus:border-cyan-500' : 'bg-gray-50 border-gray-200 text-gray-900 focus:border-cyan-400'">
                    @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1.5" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Username</label>
                    <input type="text" name="username" value="{{ old('username', auth()->user()->username) }}"
                           class="w-full px-3 py-2.5 rounded-xl text-sm border transition-all"
                           :class="isDark ? 'bg-gray-800/60 border-gray-700 text-white focus:border-cyan-500' : 'bg-gray-50 border-gray-200 text-gray-900 focus:border-cyan-400'">
                    @error('username')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium mb-1.5" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Email Address</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                       class="w-full px-3 py-2.5 rounded-xl text-sm border transition-all"
                       :class="isDark ? 'bg-gray-800/60 border-gray-700 text-white focus:border-cyan-500' : 'bg-gray-50 border-gray-200 text-gray-900 focus:border-cyan-400'">
                @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium mb-1.5" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                           class="w-full px-3 py-2.5 rounded-xl text-sm border transition-all"
                           :class="isDark ? 'bg-gray-800/60 border-gray-700 text-white focus:border-cyan-500' : 'bg-gray-50 border-gray-200 text-gray-900 focus:border-cyan-400'">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1.5" :class="isDark ? 'text-gray-400' : 'text-gray-600'">Country</label>
                    <select name="country"
                            class="w-full px-3 py-2.5 rounded-xl text-sm border transition-all appearance-none"
                            :class="isDark ? 'bg-gray-800/60 border-gray-700 text-white focus:border-cyan-500' : 'bg-gray-50 border-gray-200 text-gray-900 focus:border-cyan-400'">
                        @foreach(['US'=>'United States','GB'=>'United Kingdom','NG'=>'Nigeria','GH'=>'Ghana','KE'=>'Kenya','ZA'=>'South Africa','IN'=>'India','CA'=>'Canada','AU'=>'Australia','DE'=>'Germany','FR'=>'France','JP'=>'Japan','SG'=>'Singapore','AE'=>'UAE','BR'=>'Brazil'] as $code => $name)
                        <option value="{{ $code }}" :class="isDark ? 'bg-gray-900' : ''" {{ old('country', auth()->user()->country) === $code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(session('status') === 'profile-updated')
            <p class="text-xs text-emerald-400">Profile updated successfully.</p>
            @endif

            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all duration-200"
                    style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                Save Changes
            </button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="rounded-2xl border p-6" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <h3 class="text-sm font-semibold mb-5" :class="isDark ? 'text-white' : 'text-gray-900'">Change Password</h3>
        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf @method('put')
            @foreach([['current_password','Current Password'],['password','New Password'],['password_confirmation','Confirm New Password']] as [$name, $label])
            <div>
                <label class="block text-xs font-medium mb-1.5" :class="isDark ? 'text-gray-400' : 'text-gray-600'">{{ $label }}</label>
                <input type="password" name="{{ $name }}"
                       class="w-full px-3 py-2.5 rounded-xl text-sm border transition-all"
                       :class="isDark ? 'bg-gray-800/60 border-gray-700 text-white focus:border-cyan-500' : 'bg-gray-50 border-gray-200 text-gray-900 focus:border-cyan-400'">
                @error($name, 'updatePassword')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
            </div>
            @endforeach
            @if(session('status') === 'password-updated')
            <p class="text-xs text-emerald-400">Password updated successfully.</p>
            @endif
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all duration-200"
                    style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                Update Password
            </button>
        </form>
    </div>

</div>
@endsection
