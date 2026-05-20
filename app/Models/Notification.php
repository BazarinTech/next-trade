<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'data', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data'    => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function iconClass(): string
    {
        return match($this->type) {
            'deposit_successful'    => 'text-emerald-400',
            'deposit_failed'        => 'text-red-400',
            'withdrawal_requested'  => 'text-cyan-400',
            'withdrawal_successful' => 'text-emerald-400',
            'withdrawal_rejected'   => 'text-red-400',
            'trade_won'             => 'text-emerald-400',
            'trade_lost'            => 'text-red-400',
            'bot_earning'           => 'text-violet-400',
            'account_banned'        => 'text-red-400',
            'wallet_frozen'         => 'text-amber-400',
            default                 => 'text-cyan-400',
        };
    }
}
