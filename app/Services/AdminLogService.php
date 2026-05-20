<?php

namespace App\Services;

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Support\Collection;

class AdminLogService
{
    public function log(
        User   $admin,
        string $action,
        string $targetType = null,
        mixed  $targetId   = null,
        array  $oldValues  = [],
        array  $newValues  = []
    ): AdminLog {
        return AdminLog::create([
            'admin_id'    => $admin->id,
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'old_values'  => empty($oldValues) ? null : $oldValues,
            'new_values'  => empty($newValues) ? null : $newValues,
            'ip_address'  => request()->ip(),
            'user_agent'  => substr((string) request()->userAgent(), 0, 300),
        ]);
    }

    public function recent(int $limit = 50): Collection
    {
        return AdminLog::with('admin')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
