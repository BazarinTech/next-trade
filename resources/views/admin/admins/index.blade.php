@extends('layouts.app')
@section('title', 'Admins | Admin')
@section('page-title', 'Admins')
@section('page-subtitle', 'Manage admin accounts and roles')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

<div x-data="{
    showAssign: false,
    showRemove: false,
    showPromote: false,
    showDemote: false,
    targetUser: {},
    targetRole: {},
    openAssign(user) { this.targetUser = user; this.showAssign = true; },
    openRemove(user, role) { this.targetUser = user; this.targetRole = role; this.showRemove = true; },
    openPromote(user) { this.targetUser = user; this.showPromote = true; },
    openDemote(user) { this.targetUser = user; this.showDemote = true; }
}">

{{-- Table --}}
<div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
                    @foreach(['Admin','Roles','Joined','Actions'] as $h)
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y" :class="isDark ? 'divide-gray-800/60' : 'divide-gray-100'">
                @forelse($admins as $admin)
                <tr class="hover:bg-gray-500/5 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-violet-700 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-white">{{ strtoupper(substr($admin->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $admin->name }}</p>
                                <p class="text-xs text-gray-500">{{ $admin->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach($admin->adminRoles as $role)
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-violet-500/15 text-violet-400 border border-violet-500/20 group">
                                {{ $role->name }}
                                @if(!$role->is_system || !($admin->adminRoles->count() === 1))
                                <button type="button"
                                    @click="openRemove({{ json_encode(['id' => $admin->id, 'name' => $admin->name]) }}, {{ json_encode(['id' => $role->id, 'name' => $role->name]) }})"
                                    class="ml-0.5 text-violet-400 hover:text-red-400 transition-colors leading-none">×</button>
                                @endif
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $admin->created_at->format('M j, Y') }}</td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <button @click="openAssign({{ json_encode(['id' => $admin->id, 'name' => $admin->name]) }})"
                                class="text-xs font-medium text-cyan-400 hover:text-cyan-300 transition-colors">+ Role</button>
                            @if($admin->isSuperAdmin())
                            <button @click="openDemote({{ json_encode(['id' => $admin->id, 'name' => $admin->name]) }})"
                                class="text-xs font-medium text-amber-400 hover:text-amber-300 transition-colors">Demote</button>
                            @else
                            <button @click="openPromote({{ json_encode(['id' => $admin->id, 'name' => $admin->name]) }})"
                                class="text-xs font-medium text-emerald-400 hover:text-emerald-300 transition-colors">Promote</button>
                            @endif
                            <a href="{{ route('admin.users.show', $admin) }}" class="text-xs font-medium text-gray-400 hover:text-gray-300 transition-colors">View</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No admins found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($admins->hasPages())
    <div class="px-5 py-4 border-t" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
        {{ $admins->links() }}
    </div>
    @endif
</div>

{{-- Assign Role Modal --}}
<div x-show="showAssign" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showAssign = false"></div>
    <div class="relative w-full max-w-sm rounded-2xl border p-6" :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'">
        <h3 class="text-sm font-semibold mb-1" :class="isDark ? 'text-white' : 'text-gray-900'">Assign Role</h3>
        <p class="text-xs text-gray-500 mb-4">Assigning role to <span class="font-medium" x-text="targetUser.name"></span></p>
        <form method="POST" :action="`/admin/admins/${targetUser.id}/roles`" class="space-y-3">
            @csrf
            <select name="role_id" required class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
                <option value="">Select role…</option>
                @foreach($allRoles as $role)
                <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-sm font-semibold transition-colors">Assign</button>
                <button type="button" @click="showAssign = false" class="px-4 py-2 rounded-xl border text-sm transition-colors"
                    :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Remove Role Modal --}}
<div x-show="showRemove" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showRemove = false"></div>
    <div class="relative w-full max-w-sm rounded-2xl border p-6" :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'">
        <h3 class="text-sm font-semibold mb-2" :class="isDark ? 'text-white' : 'text-gray-900'">Remove Role</h3>
        <p class="text-xs text-gray-500 mb-4">
            Remove <span class="font-medium text-violet-400" x-text="targetRole.name"></span> from <span class="font-medium" x-text="targetUser.name"></span>?
        </p>
        <form method="POST" :action="`/admin/admins/${targetUser.id}/roles/${targetRole.id}`" class="flex gap-2">
            @csrf
            @method('DELETE')
            <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-red-500 hover:bg-red-400 text-white text-sm font-semibold transition-colors">Remove</button>
            <button type="button" @click="showRemove = false" class="px-4 py-2 rounded-xl border text-sm transition-colors"
                :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Cancel</button>
        </form>
    </div>
</div>

{{-- Promote Modal --}}
<div x-show="showPromote" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showPromote = false"></div>
    <div class="relative w-full max-w-sm rounded-2xl border p-6" :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'">
        <h3 class="text-sm font-semibold mb-2" :class="isDark ? 'text-white' : 'text-gray-900'">Promote to Super Admin</h3>
        <p class="text-xs text-gray-500 mb-4">Grant <span class="font-medium" x-text="targetUser.name"></span> full Super Admin privileges?</p>
        <form method="POST" :action="`/admin/admins/${targetUser.id}/promote`" class="flex gap-2">
            @csrf
            <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-violet-500 hover:bg-violet-400 text-white text-sm font-semibold transition-colors">Promote</button>
            <button type="button" @click="showPromote = false" class="px-4 py-2 rounded-xl border text-sm transition-colors"
                :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Cancel</button>
        </form>
    </div>
</div>

{{-- Demote Modal --}}
<div x-show="showDemote" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDemote = false"></div>
    <div class="relative w-full max-w-sm rounded-2xl border p-6" :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'">
        <h3 class="text-sm font-semibold mb-2" :class="isDark ? 'text-white' : 'text-gray-900'">Demote Super Admin</h3>
        <p class="text-xs text-gray-500 mb-1">Remove Super Admin status from <span class="font-medium" x-text="targetUser.name"></span>?</p>
        <p class="text-xs text-red-400 mb-4">This cannot be undone if they are the only Super Admin.</p>
        <form method="POST" :action="`/admin/admins/${targetUser.id}/demote`" class="flex gap-2">
            @csrf
            <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-red-500 hover:bg-red-400 text-white text-sm font-semibold transition-colors">Demote</button>
            <button type="button" @click="showDemote = false" class="px-4 py-2 rounded-xl border text-sm transition-colors"
                :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Cancel</button>
        </form>
    </div>
</div>

</div>
@endsection
