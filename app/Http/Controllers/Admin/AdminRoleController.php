<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AdminLogService;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class AdminRoleController extends Controller
{
    public function __construct(
        private PermissionService $permissions,
        private AdminLogService   $logger
    ) {}

    public function index(): View
    {
        $roles       = Role::with(['permissions', 'users'])->withCount(['permissions', 'users'])->orderBy('name')->get();
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:80',
            'slug'        => 'nullable|string|max:80|unique:roles,slug',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['slug']      = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_system'] = false;

        $role = Role::create($validated);

        $this->logger->log(auth()->user(), 'role_created', Role::class, $role->id,
            [], ['name' => $role->name, 'slug' => $role->slug]);

        return back()->with('success', "Role '{$role->name}' created.");
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:80',
            'slug'        => 'nullable|string|max:80|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string|max:255',
        ]);

        $old = $role->only(['name', 'slug', 'description']);

        if ($role->is_system) {
            // System roles: only allow updating name and description, not slug
            $role->update(['name' => $validated['name'], 'description' => $validated['description'] ?? $role->description]);
        } else {
            $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
            $role->update($validated);
        }

        $this->logger->log(auth()->user(), 'role_updated', Role::class, $role->id, $old, $role->fresh()->only(['name', 'slug', 'description']));

        return back()->with('success', "Role '{$role->name}' updated.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        $name = $role->name;
        $role->delete();

        $this->logger->log(auth()->user(), 'role_deleted', Role::class, $role->id,
            ['name' => $name], []);

        return back()->with('success', "Role '{$name}' deleted.");
    }

    public function syncPermissions(Request $request, Role $role): RedirectResponse
    {
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $permissionIds = $request->input('permissions', []);
        $this->permissions->syncRolePermissions($role, $permissionIds);

        $this->logger->log(auth()->user(), 'role_permissions_synced', Role::class, $role->id,
            [], ['permission_ids' => $permissionIds]);

        return back()->with('success', "Permissions updated for '{$role->name}'.");
    }
}
