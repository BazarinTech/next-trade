<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

class PermissionService
{
    public function userHasPermission(User $user, string $permission): bool
    {
        if (!$user->is_admin) {
            return false;
        }
        if ($this->userHasRole($user, 'super-admin')) {
            return true;
        }
        return $user->adminRoles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($r) => $r->permissions)
            ->pluck('slug')
            ->contains($permission);
    }

    public function userHasAnyPermission(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->userHasPermission($user, $permission)) {
                return true;
            }
        }
        return false;
    }

    public function userHasRole(User $user, string $roleSlug): bool
    {
        return $user->adminRoles()->where('slug', $roleSlug)->exists();
    }

    public function getUserPermissions(User $user): Collection
    {
        if ($this->userHasRole($user, 'super-admin')) {
            return Permission::all();
        }
        return $user->adminRoles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($r) => $r->permissions)
            ->unique('id');
    }

    public function assignRole(User $user, Role $role, ?User $assignedBy = null): void
    {
        if (!$user->adminRoles()->where('role_id', $role->id)->exists()) {
            $user->adminRoles()->attach($role->id, [
                'assigned_by' => $assignedBy?->id,
            ]);
        }
    }

    public function removeRole(User $user, Role $role): void
    {
        $user->adminRoles()->detach($role->id);
    }

    public function syncRolePermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }

    public function createDefaultRolesAndPermissions(): void
    {
        $permissionsData = [
            // User Management
            ['name' => 'View Users',           'slug' => 'view_users',            'group' => 'Users'],
            ['name' => 'Manage Users',         'slug' => 'manage_users',          'group' => 'Users'],
            ['name' => 'Ban Users',            'slug' => 'ban_users',             'group' => 'Users'],
            // Wallet Management
            ['name' => 'View Wallets',         'slug' => 'view_wallets',          'group' => 'Wallets'],
            ['name' => 'Manage Wallets',       'slug' => 'manage_wallets',        'group' => 'Wallets'],
            ['name' => 'Freeze Wallets',       'slug' => 'freeze_wallets',        'group' => 'Wallets'],
            ['name' => 'Adjust Wallets',       'slug' => 'adjust_wallets',        'group' => 'Wallets'],
            // Payments
            ['name' => 'View Deposits',        'slug' => 'view_deposits',         'group' => 'Payments'],
            ['name' => 'Manage Deposits',      'slug' => 'manage_deposits',       'group' => 'Payments'],
            ['name' => 'View Withdrawals',     'slug' => 'view_withdrawals',      'group' => 'Payments'],
            ['name' => 'Manage Withdrawals',   'slug' => 'manage_withdrawals',    'group' => 'Payments'],
            // Trading
            ['name' => 'View Trading Engine',  'slug' => 'view_trading_engine',   'group' => 'Trading'],
            ['name' => 'Manage Trading Engine','slug' => 'manage_trading_engine', 'group' => 'Trading'],
            ['name' => 'Manage Assets',        'slug' => 'manage_assets',         'group' => 'Trading'],
            // Bots
            ['name' => 'View Bots',            'slug' => 'view_bots',             'group' => 'Bots'],
            ['name' => 'Manage Bots',          'slug' => 'manage_bots',           'group' => 'Bots'],
            // Admins
            ['name' => 'View Admins',          'slug' => 'view_admins',           'group' => 'Admins'],
            ['name' => 'Manage Admins',        'slug' => 'manage_admins',         'group' => 'Admins'],
            ['name' => 'Manage Roles',         'slug' => 'manage_roles',          'group' => 'Admins'],
            ['name' => 'Manage Permissions',   'slug' => 'manage_permissions',    'group' => 'Admins'],
            // System
            ['name' => 'View Audit Logs',      'slug' => 'view_audit_logs',       'group' => 'System'],
            ['name' => 'Manage System Settings','slug' => 'manage_system_settings','group' => 'System'],
            ['name' => 'Maintenance Mode',     'slug' => 'maintenance_mode',      'group' => 'System'],
        ];

        foreach ($permissionsData as $data) {
            Permission::firstOrCreate(['slug' => $data['slug']], $data);
        }

        $allPermissionIds = Permission::pluck('id')->toArray();

        $rolesData = [
            [
                'name'        => 'Super Admin',
                'slug'        => 'super-admin',
                'description' => 'Full platform access',
                'is_system'   => true,
                'permissions' => $allPermissionIds,
            ],
            [
                'name'        => 'Finance Admin',
                'slug'        => 'finance-admin',
                'description' => 'Manages deposits and withdrawals',
                'is_system'   => true,
                'permissions' => Permission::whereIn('slug', [
                    'view_users', 'view_wallets',
                    'view_deposits', 'manage_deposits',
                    'view_withdrawals', 'manage_withdrawals',
                ])->pluck('id')->toArray(),
            ],
            [
                'name'        => 'Trading Admin',
                'slug'        => 'trading-admin',
                'description' => 'Manages trading engine and bots',
                'is_system'   => true,
                'permissions' => Permission::whereIn('slug', [
                    'view_trading_engine', 'manage_trading_engine',
                    'manage_assets', 'view_bots', 'manage_bots',
                ])->pluck('id')->toArray(),
            ],
            [
                'name'        => 'Support Admin',
                'slug'        => 'support-admin',
                'description' => 'User support and account management',
                'is_system'   => true,
                'permissions' => Permission::whereIn('slug', [
                    'view_users', 'manage_users',
                    'view_wallets',
                    'view_deposits', 'view_withdrawals',
                ])->pluck('id')->toArray(),
            ],
            [
                'name'        => 'Moderator',
                'slug'        => 'moderator',
                'description' => 'Can view users, ban/unban, view logs',
                'is_system'   => true,
                'permissions' => Permission::whereIn('slug', [
                    'view_users', 'ban_users', 'view_audit_logs',
                ])->pluck('id')->toArray(),
            ],
        ];

        foreach ($rolesData as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);
            $role = Role::firstOrCreate(['slug' => $roleData['slug']], $roleData);
            $role->permissions()->sync($permissions);
        }
    }
}
