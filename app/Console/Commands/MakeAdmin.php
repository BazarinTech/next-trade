<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature   = 'admin:make {email : The email address of the user to promote}';
    protected $description = 'Promote a user to Super Admin';

    public function handle(PermissionService $permissions): int
    {
        $email = $this->argument('email');
        $user  = User::where('email', $email)->first();

        if (!$user) {
            $this->error("No user found with email: {$email}");
            return self::FAILURE;
        }

        $superAdminRole = Role::where('slug', 'super-admin')->first();

        if (!$superAdminRole) {
            $this->error('Super Admin role not found. Run: php artisan db:seed --class=RolesAndPermissionsSeeder');
            return self::FAILURE;
        }

        $user->update(['is_admin' => true]);

        if ($user->adminRoles()->where('role_id', $superAdminRole->id)->exists()) {
            $this->warn("{$user->name} ({$email}) is already a Super Admin.");
            return self::SUCCESS;
        }

        $permissions->assignRole($user, $superAdminRole);

        $this->info("✓ {$user->name} ({$email}) has been promoted to Super Admin.");
        return self::SUCCESS;
    }
}
