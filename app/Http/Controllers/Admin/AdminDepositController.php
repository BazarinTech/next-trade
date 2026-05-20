<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentDeposit;
use App\Services\AdminLogService;
use App\Services\DepositService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AdminDepositController extends Controller
{
    public function __construct(
        private DepositService      $depositService,
        private AdminLogService     $logger,
        private NotificationService $notifier
    ) {}

    public function index(Request $request): View
    {
        $query = PaymentDeposit::with(['user', 'wallet'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('email')) {
            $query->whereHas('user', fn($q) => $q->where('email', 'like', '%' . $request->email . '%'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $deposits = $query->paginate(20)->withQueryString();

        $pendingCount         = PaymentDeposit::where('status', 'pending')->count();
        $successfulToday      = PaymentDeposit::where('status', 'successful')->whereDate('credited_at', today())->count();
        $failedToday          = PaymentDeposit::where('status', 'failed')->whereDate('updated_at', today())->count();
        $totalKesToday        = PaymentDeposit::where('status', 'successful')->where('local_currency', 'KES')->whereDate('credited_at', today())->sum('local_amount');
        $totalUsdToday        = PaymentDeposit::where('status', 'successful')->whereDate('credited_at', today())->sum('usd_amount');
        $pendingUsdtCount     = PaymentDeposit::where('status', 'pending')->where('method', 'crypto_usdt_trc20')->count();

        return view('admin.deposits.index', compact(
            'deposits', 'pendingCount', 'successfulToday',
            'failedToday', 'totalKesToday', 'totalUsdToday', 'pendingUsdtCount'
        ));
    }

    public function show(PaymentDeposit $deposit): View
    {
        $deposit->load(['user', 'wallet', 'reviewer']);
        return view('admin.deposits.show', compact('deposit'));
    }

    public function approveUsdt(Request $request, PaymentDeposit $deposit): RedirectResponse
    {
        if ($deposit->method !== 'crypto_usdt_trc20') {
            return back()->with('error', 'This deposit is not a USDT deposit.');
        }

        if (!$deposit->isPending()) {
            return back()->with('error', 'Deposit is already ' . $deposit->status . '. Cannot approve.');
        }

        $notes = $request->input('admin_notes');

        try {
            $deposit = $this->depositService->approveUsdtDeposit(
                $deposit,
                auth()->user(),
                $notes ?: null
            );

            $this->logger->log(
                auth()->user(),
                'usdt_deposit_approved',
                PaymentDeposit::class,
                $deposit->id,
                ['status' => 'pending'],
                ['status' => 'successful', 'usd_amount' => $deposit->usd_amount, 'txid' => $deposit->txid]
            );

            $this->notifier->send($deposit->user, 'deposit_successful', 'Deposit Approved',
                'Your USDT deposit of $' . number_format($deposit->usd_amount, 2) . ' has been approved and credited to your wallet.',
                ['deposit_id' => $deposit->id]);

            return redirect()
                ->route('admin.deposits.show', $deposit)
                ->with('success', 'USDT deposit approved. $' . number_format($deposit->usd_amount, 2) . ' credited to wallet.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectUsdt(Request $request, PaymentDeposit $deposit): RedirectResponse
    {
        if ($deposit->method !== 'crypto_usdt_trc20') {
            return back()->with('error', 'This deposit is not a USDT deposit.');
        }

        if (!$deposit->isPending()) {
            return back()->with('error', 'Deposit is already ' . $deposit->status . '. Cannot reject.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'Please provide a reason for rejection.',
        ]);

        try {
            $deposit = $this->depositService->rejectUsdtDeposit(
                $deposit,
                auth()->user(),
                $request->rejection_reason,
                $request->input('admin_notes') ?: null
            );

            $this->logger->log(
                auth()->user(),
                'usdt_deposit_rejected',
                PaymentDeposit::class,
                $deposit->id,
                ['status' => 'pending'],
                ['status' => 'failed', 'rejection_reason' => $deposit->rejection_reason]
            );

            $this->notifier->send($deposit->user, 'deposit_failed', 'Deposit Rejected',
                'Your USDT deposit has been rejected. Reason: ' . $request->rejection_reason,
                ['deposit_id' => $deposit->id]);

            return redirect()
                ->route('admin.deposits.show', $deposit)
                ->with('success', 'USDT deposit rejected.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
