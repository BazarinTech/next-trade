<?php

namespace App\Listeners;

use App\Services\WalletService;
use Illuminate\Auth\Events\Registered;

class CreateUserWallets
{
    public function __construct(private WalletService $walletService) {}

    public function handle(Registered $event): void
    {
        $this->walletService->createDefaultWallets($event->user);
    }
}
