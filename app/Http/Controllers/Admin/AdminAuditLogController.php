<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AdminLog::with('admin')->latest();

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }
        if ($request->filled('target_type')) {
            $query->where('target_type', $request->target_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs    = $query->paginate(50)->withQueryString();
        $admins  = User::where('is_admin', true)->orderBy('name')->get(['id', 'name', 'email']);
        $actions = AdminLog::distinct()->orderBy('action')->pluck('action');

        return view('admin.audit-logs.index', compact('logs', 'admins', 'actions'));
    }
}
