<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\AdminLogService;
use App\Services\NotificationService;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AdminWithdrawalController extends Controller
{
    public function __construct(
        private WithdrawalService   $withdrawalService,
        private AdminLogService     $logger,
        private NotificationService $notifier
    ) {}

    public function index(Request $request): View
    {
        $query = Withdrawal::with(['user', 'wallet'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        $withdrawals = $query->paginate(20)->withQueryString();

        $pendingCount      = Withdrawal::where('status', 'pending')->count();
        $processingCount   = Withdrawal::where('status', 'processing')->count();
        $approvedCount     = Withdrawal::where('status', 'approved')->count();
        $successfulToday   = Withdrawal::where('status', 'successful')->whereDate('completed_at', today())->count();
        $rejectedToday     = Withdrawal::where('status', 'rejected')->whereDate('reviewed_at', today())->count();
        $totalUsdToday     = Withdrawal::where('status', 'successful')->whereDate('completed_at', today())->sum('usd_amount');
        $totalLocked       = Withdrawal::whereIn('status', ['pending', 'approved', 'processing'])->sum('usd_amount');

        return view('admin.withdrawals.index', compact(
            'withdrawals', 'pendingCount', 'processingCount', 'approvedCount',
            'successfulToday', 'rejectedToday', 'totalUsdToday', 'totalLocked'
        ));
    }

    public function show(Withdrawal $withdrawal): View
    {
        $withdrawal->load(['user', 'wallet', 'reviewer']);
        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    public function approve(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        if (!$withdrawal->isPending()) {
            return back()->with('error', 'Withdrawal must be pending to approve.');
        }

        try {
            $withdrawal = $this->withdrawalService->approveWithdrawal(
                $withdrawal,
                auth()->user(),
                $request->input('admin_notes') ?: null
            );

            $this->logger->log(
                auth()->user(), 'withdrawal_approved',
                Withdrawal::class, $withdrawal->id,
                ['status' => 'pending'],
                ['status' => 'approved', 'usd_amount' => $withdrawal->usd_amount]
            );

            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->with('success', 'Withdrawal approved. Mark as processing when payment is sent.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], ['rejection_reason.required' => 'A rejection reason is required.']);

        try {
            $withdrawal = $this->withdrawalService->rejectWithdrawal(
                $withdrawal,
                auth()->user(),
                $request->rejection_reason,
                $request->input('admin_notes') ?: null
            );

            $this->logger->log(
                auth()->user(), 'withdrawal_rejected',
                Withdrawal::class, $withdrawal->id,
                ['status' => 'pending'],
                ['status' => 'rejected', 'reason' => $withdrawal->rejection_reason]
            );

            $this->notifier->send($withdrawal->user, 'withdrawal_rejected', 'Withdrawal Rejected',
                'Your withdrawal of $' . number_format($withdrawal->usd_amount, 2) . ' has been rejected. Reason: ' . $request->rejection_reason . '. Funds have been returned to your wallet.',
                ['withdrawal_id' => $withdrawal->id]);

            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->with('success', 'Withdrawal rejected and funds unlocked.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function processing(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        try {
            $withdrawal = $this->withdrawalService->markWithdrawalProcessing(
                $withdrawal,
                auth()->user(),
                $request->input('provider_reference') ?: null
            );

            $this->logger->log(
                auth()->user(), 'withdrawal_processing',
                Withdrawal::class, $withdrawal->id,
                [], ['status' => 'processing']
            );

            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->with('success', 'Withdrawal marked as processing.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function successful(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        try {
            $withdrawal = $this->withdrawalService->markWithdrawalSuccessful(
                $withdrawal,
                auth()->user(),
                $request->input('provider_reference') ?: null,
                $request->input('txid') ?: null
            );

            $this->logger->log(
                auth()->user(), 'withdrawal_successful',
                Withdrawal::class, $withdrawal->id,
                [], ['status' => 'successful', 'usd_amount' => $withdrawal->usd_amount]
            );

            $this->notifier->send($withdrawal->user, 'withdrawal_successful', 'Withdrawal Successful',
                'Your withdrawal of $' . number_format($withdrawal->usd_amount, 2) . ' has been processed successfully.',
                ['withdrawal_id' => $withdrawal->id]);

            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->with('success', '$' . number_format($withdrawal->usd_amount, 2) . ' withdrawal completed. Funds deducted.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function failed(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], ['rejection_reason.required' => 'Please provide a reason for the failure.']);

        try {
            $withdrawal = $this->withdrawalService->markWithdrawalFailed(
                $withdrawal,
                auth()->user(),
                $request->rejection_reason
            );

            $this->logger->log(
                auth()->user(), 'withdrawal_failed',
                Withdrawal::class, $withdrawal->id,
                [], ['status' => 'failed', 'reason' => $request->rejection_reason]
            );

            return redirect()
                ->route('admin.withdrawals.show', $withdrawal)
                ->with('success', 'Withdrawal marked as failed. Funds returned to available balance.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
