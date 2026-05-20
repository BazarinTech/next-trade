<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivityLog;

class UserActivityLogService
{
    public function log(
        ?User   $user,
        string  $action,
        ?string $description = null,
        array   $metadata    = []
    ): UserActivityLog {
        return UserActivityLog::create([
            'user_id'     => $user?->id,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => substr((string) request()->userAgent(), 0, 300),
            'metadata'    => empty($metadata) ? null : $metadata,
        ]);
    }
}
