@extends('layouts.app')
@section('title', 'Manage Users — Next Trade Admin')
@section('page-title', 'Users')
@section('page-subtitle', 'Manage platform users')

@section('content')
<div class="rounded-2xl border" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">All Users <span class="text-gray-500 font-normal">({{ $users->total() ?? 0 }})</span></h3>
        <div class="flex items-center gap-2">
            <input type="text" placeholder="Search users..." class="text-xs px-3 py-2 rounded-lg border"
                   :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-600' : 'bg-gray-50 border-gray-200 text-gray-900'">
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
                    @foreach(['User','Username','Phone','Country','Admin','Status','Joined','Actions'] as $col)
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 whitespace-nowrap">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($users ?? [] as $user)
                <tr class="border-b transition-colors" :class="isDark ? 'border-gray-800/40 hover:bg-gray-800/30' : 'border-gray-50 hover:bg-gray-50'">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-cyan-500 to-cyan-700 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="text-xs font-medium" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">@{{ $user->username }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $user->phone ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $user->country ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @if($user->is_admin)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">Admin</span>
                        @else
                        <span class="text-xs text-gray-600">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($user->is_banned)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 border border-red-500/20">Banned</span>
                        @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.users.toggle-ban', $user) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs px-2.5 py-1 rounded-lg border transition-colors"
                                        :class="isDark ? 'border-gray-700 text-gray-400 hover:border-red-500/50 hover:text-red-400' : 'border-gray-200 text-gray-500 hover:border-red-300 hover:text-red-500'">
                                    {{ $user->is_banned ? 'Unban' : 'Ban' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-sm text-gray-500">No users registered yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($users) && $users->hasPages())
    <div class="p-4 border-t" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
