@extends('layouts.app')
@section('title', 'Permissions | Admin')
@section('page-title', 'Permissions')
@section('page-subtitle', 'Platform permission registry')

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
    editPerm: {}
}">

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div></div>
    <button @click="showCreate = true"
        class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-black text-sm font-semibold transition-colors">
        + New Permission
    </button>
</div>

{{-- Permissions grouped --}}
@foreach($permissions as $group => $perms)
<div class="mb-6">
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-1">{{ $group ?? 'General' }}</p>
    <div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <table class="w-full text-sm">
            <tbody class="divide-y" :class="isDark ? 'divide-gray-800/60' : 'divide-gray-100'">
                @foreach($perms as $perm)
                <tr class="hover:bg-gray-500/5 transition-colors">
                    <td class="px-5 py-3 w-1/3">
                        <p class="text-xs font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $perm->name }}</p>
                        <p class="text-xs font-mono text-gray-500">{{ $perm->slug }}</p>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $perm->description ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach($perm->roles as $role)
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-violet-500/15 text-violet-400 border border-violet-500/20">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap text-right">
                        <button @click="editPerm = {{ json_encode(['id' => $perm->id, 'name' => $perm->name, 'description' => $perm->description ?? '']) }}; showEdit = true"
                            class="text-xs font-medium text-cyan-400 hover:text-cyan-300 transition-colors">Edit</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach

{{-- Create Modal --}}
<div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showCreate = false"></div>
    <div class="relative w-full max-w-md rounded-2xl border p-6" :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'">
        <h3 class="text-sm font-semibold mb-4" :class="isDark ? 'text-white' : 'text-gray-900'">New Permission</h3>
        <form method="POST" action="{{ route('admin.permissions.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1">Name</label>
                <input type="text" name="name" required class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                    :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Group</label>
                <input type="text" name="group" placeholder="e.g. users, finance…" class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                    :class="isDark ? 'bg-gray-800 border-gray-700 text-white placeholder-gray-600' : 'bg-white border-gray-300 text-gray-900'">
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
        <h3 class="text-sm font-semibold mb-4" :class="isDark ? 'text-white' : 'text-gray-900'">Edit Permission</h3>
        <form method="POST" :action="`/admin/permissions/${editPerm.id}`" class="space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs text-gray-500 mb-1">Name</label>
                <input type="text" name="name" :value="editPerm.name" required class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
                    :class="isDark ? 'bg-gray-800 border-gray-700 text-white' : 'bg-white border-gray-300 text-gray-900'">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Description</label>
                <input type="text" name="description" :value="editPerm.description" class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/40"
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

</div>
@endsection
