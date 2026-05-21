<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index(Request $request): View
    {
        $user  = auth()->user();
        $query = Notification::where('user_id', $user->id)->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(20)->withQueryString();
        $unreadCount   = $this->notifications->unreadCount($user);

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markRead(Notification $notification): JsonResponse|RedirectResponse
    {
        abort_if($notification->user_id !== auth()->id(), 403);
        $this->notifications->markAsRead($notification);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }

    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        $this->notifications->markAllAsRead(auth()->user());

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }
}
