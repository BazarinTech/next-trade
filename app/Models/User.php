<?php

namespace App\Models;

use App\Models\AdminLog;
use App\Models\BotEarning;
use App\Models\BotInvestment;
use App\Models\PaymentDeposit;
use App\Models\Role;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\UserBan;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'is_admin',
        'is_banned',
        'last_login_at',
    ];

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

    public function isKenya(): bool
    {
        return strtolower(trim($this->country ?? '')) === 'kenya';
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
}
