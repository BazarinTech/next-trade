@extends('layouts.app')
@section('title', 'Roles | Admin')
@section('page-title', 'Roles')
@section('page-subtitle', 'Manage admin roles and permissions')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

<div x-data="{
    showCreate: false,
    showEdit: false,
    showDelete: false,
    showPermissions: false,
    editRole: {},
    deleteRole: {},
    permRole: {},
    checkedPerms: [],
    openEdit(role) {
        this.editRole = role;
        this.showEdit = true;
    },
    openDelete(role) {
        this.deleteRole = role;
        this.showDelete = true;
    },
    openPermissions(role, perms) {
        this.permRole = role;
        this.checkedPerms = perms;
        this.showPermissions = true;
    }
}">

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div></div>
    <button @click="showCreate = true"
        class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-sm font-semibold transition-colors">
        + New Role
    </button>
</div>

{{-- Roles Table --}}
<div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
                    @foreach(['Role','Slug','Permissions','Users','',''] as $h)
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y" :class="isDark ? 'divide-gray-800/60' : 'divide-gray-100'">
                @forelse($roles as $role)
                <tr class="hover:bg-gray-500/5 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <p class="text-xs font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $role->name }}</p>
                            @if($role->is_system)
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-500/15 text-amber-400 border border-amber-500/20">system</span>
                            @endif
                        </div>
                        @if($role->description)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $role->description }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs font-mono text-gray-500">{{ $role->slug }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $role->permissions_count }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $role->users_count }}</td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <button @click="openPermissions({{ json_encode(['id' => $role->id, 'name' => $role->name]) }}, {{ json_encode($role->permissions->pluck('id')->toArray()) }})"
                            class="text-xs font-medium text-violet-400 hover:text-violet-300 transition-colors">Permissions</button>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            @unless($role->is_system)
                            <button @click="openEdit({{ json_encode(['id' => $role->id, 'name' => $role->name, 'description' => $role->description ?? '']) }})"
                                class="text-xs font-medium text-cyan-400 hover:text-cyan-300 transition-colors">Edit</button>
                            <button @click="openDelete({{ json_encode(['id' => $role->id, 'name' => $role->name]) }})"
                                class="text-xs font-medium text-red-400 hover:text-red-300 transition-colors">Delete</button>
                            @endunless
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">No roles found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Create Modal --}}
<div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showCreate = false"></div>
    <div class="relative w-full max-w-md rounded-2xl border p-6" :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'">
        <h3 class="text-sm font-semibold mb-4" :class="isDark ? 'text-white' : 'text-gray-900'">Create Role</h3>
        <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1">Name</label>
                <input type="text" name="name" required class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                    :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Description</label>
                <input type="text" name="description" class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                    :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
            </div>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-sm font-semibold transition-colors">Create</button>
                <button type="button" @click="showCreate = false" class="px-4 py-2 rounded-xl border text-sm transition-colors"
                    :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showEdit = false"></div>
    <div class="relative w-full max-w-md rounded-2xl border p-6" :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'">
        <h3 class="text-sm font-semibold mb-4" :class="isDark ? 'text-white' : 'text-gray-900'">Edit Role</h3>
        <form method="POST" :action="`/admin/roles/${editRole.id}`" class="space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs text-gray-500 mb-1">Name</label>
                <input type="text" name="name" :value="editRole.name" required class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                    :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Description</label>
                <input type="text" name="description" :value="editRole.description" class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                    :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
            </div>
            <div class="flex gap-2 pt-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-sm font-semibold transition-colors">Save</button>
                <button type="button" @click="showEdit = false" class="px-4 py-2 rounded-xl border text-sm transition-colors"
                    :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Modal --}}
<div x-show="showDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDelete = false"></div>
    <div class="relative w-full max-w-sm rounded-2xl border p-6" :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'">
        <h3 class="text-sm font-semibold mb-2" :class="isDark ? 'text-white' : 'text-gray-900'">Delete Role</h3>
        <p class="text-xs text-gray-500 mb-4">Delete <span class="font-semibold" x-text="deleteRole.name"></span>? This cannot be undone.</p>
        <form method="POST" :action="`/admin/roles/${deleteRole.id}`" class="flex gap-2">
            @csrf
            @method('DELETE')
            <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-red-500 hover:bg-red-400 text-white text-sm font-semibold transition-colors">Delete</button>
            <button type="button" @click="showDelete = false" class="px-4 py-2 rounded-xl border text-sm transition-colors"
                :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Cancel</button>
        </form>
    </div>
</div>

{{-- Permissions Modal --}}
<div x-show="showPermissions" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showPermissions = false"></div>
    <div class="relative w-full max-w-lg rounded-2xl border p-6 max-h-[80vh] overflow-y-auto" :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'">
        <h3 class="text-sm font-semibold mb-4" :class="isDark ? 'text-white' : 'text-gray-900'">
            Permissions for <span x-text="permRole.name"></span>
        </h3>
        <form method="POST" :action="`/admin/roles/${permRole.id}/permissions`">
            @csrf
            <div class="space-y-4">
                @foreach($permissions as $group => $perms)
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ $group ?? 'General' }}</p>
                    <div class="space-y-1.5">
                        @foreach($perms as $perm)
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                :checked="checkedPerms.includes({{ $perm->id }})"
                                class="rounded border-gray-600 bg-gray-800 text-cyan-500 focus:ring-cyan-500/40">
                            <span>
                                <span class="text-xs" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $perm->name }}</span>
                                @if($perm->description)
                                <span class="text-xs text-gray-500 ml-1">— {{ $perm->description }}</span>
                                @endif
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            <div class="flex gap-2 mt-6">
                <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-sm font-semibold transition-colors">Save Permissions</button>
                <button type="button" @click="showPermissions = false" class="px-4 py-2 rounded-xl border text-sm transition-colors"
                    :class="isDark ? 'border-gray-700 text-gray-400 hover:text-white' : 'border-gray-300 text-gray-600'">Cancel</button>
            </div>
        </form>
    </div>
</div>

</div>
@endsection
