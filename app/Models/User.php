<?php

namespace App\Models;

use App\Models\AdminLog;
use App\Models\BotEarning;
use App\Models\BotInvestment;
use App\Models\PaymentDeposit;
use App\Models\ReferralCommission;
use App\Models\Role;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\UserBan;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'country',
        'password',
        'avatar',
        'theme_preference',
        'referral_code',
        'referred_by',
        'is_admin',
        'is_banned',
        'last_login_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->referral_code)) {
                do {
                    $code = strtoupper(Str::random(8));
                } while (static::where('referral_code', $code)->exists());
                $user->referral_code = $code;
            }
        });
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
            'is_banned'         => 'boolean',
        ];
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function botInvestments(): HasMany
    {
        return $this->hasMany(BotInvestment::class);
    }

    public function botEarnings(): HasMany
    {
        return $this->hasMany(BotEarning::class);
    }

    public function paymentDeposits(): HasMany
    {
        return $this->hasMany(PaymentDeposit::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function bans(): HasMany
    {
        return $this->hasMany(UserBan::class);
    }

    public function activeBan(): HasOne
    {
        return $this->hasOne(UserBan::class)->where('is_active', true)->latest();
    }

    public function adminRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'admin_role_user')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function adminLogs(): HasMany
    {
        return $this->hasMany(AdminLog::class, 'admin_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function referralCommissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'referrer_id');
    }

    public function isKenya(): bool
    {
        $c = strtolower(trim($this->country ?? ''));
        return $c === 'kenya' || $c === 'ke';
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_admin && $this->adminRoles()->where('slug', 'super-admin')->exists();
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return $this->adminRoles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($r) => $r->permissions)
            ->pluck('slug')
            ->contains($slug);
    }

    public function demoWallet(): ?Wallet
    {
        return $this->wallets()->where('type', 'demo')->first();
    }

    public function liveWallet(): ?Wallet
    {
        return $this->wallets()->where('type', 'live')->first();
    }

    public function activeWallet(string $mode = 'demo'): ?Wallet
    {
        return $this->wallets()->where('type', $mode)->first();
    }

    // ─── Avatar helpers ───────────────────────────────────────────────────────

    public function getInitialsAttribute(): string
    {
        $words = array_filter(explode(' ', trim($this->name)));
        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1));
        }
        return strtoupper(mb_substr($this->name, 0, 2));
    }

    public function getAvatarUrlAttribute(): string
    {
        $seed = rawurlencode($this->username ?? $this->name);
        return "https://api.dicebear.com/9.x/personas/svg?seed={$seed}&backgroundColor=0f172a&body=rounded&eyes=open";
    }

    public function getAvatarGradientAttribute(): string
    {
        $palettes = [
            ['#06b6d4', '#0891b2'],
            ['#8b5cf6', '#7c3aed'],
            ['#ec4899', '#db2777'],
            ['#f59e0b', '#d97706'],
            ['#10b981', '#059669'],
            ['#3b82f6', '#2563eb'],
            ['#f97316', '#ea580c'],
            ['#a78bfa', '#7c3aed'],
        ];
        [$from, $to] = $palettes[$this->id % count($palettes)];
        return "linear-gradient(135deg,{$from},{$to})";
    }
}
