<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Services\AdminLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminPermissionController extends Controller
{
    public function __construct(private AdminLogService $logger) {}

    public function index(): View
    {
        $permissions = Permission::with('roles')->orderBy('group')->orderBy('name')->get()->groupBy('group');
        return view('admin.permissions.index', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:80',
            'slug'        => 'nullable|string|max:80|unique:permissions,slug',
            'description' => 'nullable|string|max:255',
            'group'       => 'nullable|string|max:60',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name'], '_');
        $permission = Permission::create($validated);

        $this->logger->log(auth()->user(), 'permission_created', Permission::class, $permission->id,
            [], ['slug' => $permission->slug]);

        return back()->with('success', "Permission '{$permission->name}' created.");
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:80',
            'description' => 'nullable|string|max:255',
            'group'       => 'nullable|string|max:60',
        ]);

        $old = $permission->only(['name', 'description', 'group']);
        $permission->update($validated);

        $this->logger->log(auth()->user(), 'permission_updated', Permission::class, $permission->id,
            $old, $permission->fresh()->only(['name', 'description', 'group']));

        return back()->with('success', "Permission updated.");
    }
}
