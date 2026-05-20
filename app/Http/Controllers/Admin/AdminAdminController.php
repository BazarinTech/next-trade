<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminLogService;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAdminController extends Controller
{
    public function __construct(
        private PermissionService $permissions,
        private AdminLogService   $logger
    ) {}

    public function index(): View
    {
        $admins = User::where('is_admin', true)
            ->with(['adminRoles.permissions'])
            ->latest()
            ->paginate(20);

        $allRoles   = Role::orderBy('name')->get();
        $allUsers   = User::where('is_admin', false)->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.admins.index', compact('admins', 'allRoles', 'allUsers'));
    }

    public function assignRole(Request $request, User $user): RedirectResponse
    {
        $request->validate(['role_id' => 'required|integer|exists:roles,id']);

        $role = Role::findOrFail($request->role_id);
        $this->permissions->assignRole($user, $role, auth()->user());

        $this->logger->log(auth()->user(), 'admin_role_assigned', User::class, $user->id,
            [], ['role' => $role->slug, 'target_user' => $user->email]);

        return back()->with('success', "Role '{$role->name}' assigned to {$user->name}.");
    }

    public function removeRole(User $user, Role $role): RedirectResponse
    {
        if ($role->slug === 'super-admin' && $user->id === auth()->id()) {
            return back()->with('error', 'You cannot remove your own Super Admin role.');
        }

        if ($role->slug === 'super-admin') {
            $superAdminCount = User::where('is_admin', true)
                ->whereHas('adminRoles', fn($q) => $q->where('slug', 'super-admin'))
                ->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'Cannot remove the only Super Admin.');
            }
        }

        $this->permissions->removeRole($user, $role);

        $this->logger->log(auth()->user(), 'admin_role_removed', User::class, $user->id,
            ['role' => $role->slug], []);

        return back()->with('success', "Role '{$role->name}' removed from {$user->name}.");
    }

    public function promote(Request $request, User $user): RedirectResponse
    {
        if ($user->is_admin) {
            return back()->with('error', "{$user->name} is already an admin.");
        }

        $request->validate(['role_id' => 'nullable|integer|exists:roles,id']);

        $user->update(['is_admin' => true]);

        $roleId = $request->input('role_id');
        if ($roleId) {
            $role = Role::find($roleId);
            if ($role) {
                $this->permissions->assignRole($user, $role, auth()->user());
            }
        }

        $this->logger->log(auth()->user(), 'user_promoted_to_admin', User::class, $user->id,
            ['is_admin' => false], ['is_admin' => true]);

        return back()->with('success', "{$user->name} promoted to admin.");
    }

    public function demote(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot demote yourself.');
        }

        if ($user->isSuperAdmin()) {
            $count = User::where('is_admin', true)
                ->whereHas('adminRoles', fn($q) => $q->where('slug', 'super-admin'))
                ->count();
            if ($count <= 1) {
                return back()->with('error', 'Cannot demote the only Super Admin.');
            }
        }

        $user->adminRoles()->detach();
        $user->update(['is_admin' => false]);

        $this->logger->log(auth()->user(), 'user_demoted_from_admin', User::class, $user->id,
            ['is_admin' => true], ['is_admin' => false]);

        return back()->with('success', "{$user->name} has been demoted.");
    }
}
