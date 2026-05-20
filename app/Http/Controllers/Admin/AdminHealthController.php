<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminHealthController extends Controller
{
    public function index(): View
    {
        // Database check
        $dbOk = false;
        $dbError = null;
        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Exception $e) {
            $dbError = $e->getMessage();
        }

        // Storage symlink
        $storageOk = file_exists(public_path('storage'));

        // PalPluss
        $palplussOk = !empty(env('PALPLUSS_SHORTCODE')) && !empty(env('PALPLUSS_SECRET'));

        // App key
        $appKeyOk = strlen(config('app.key')) > 0;

        // Logs writable
        $logsWritable = is_writable(storage_path('logs'));

        // Queue driver
        $queueDriver = config('queue.default');

        // Last cleanup
        $lastCleanup = SystemSetting::where('key', 'last_cleanup_at')->value('value');

        // Last reconciliation (from latest log file)
        $recoFiles  = glob(storage_path('logs/reconciliation_*.json'));
        $lastReco   = $recoFiles ? basename(end($recoFiles)) : null;

        $checks = [
            ['label' => 'App Key',          'ok' => $appKeyOk,   'note' => $appKeyOk   ? 'Set' : 'Missing APP_KEY in .env'],
            ['label' => 'Database',         'ok' => $dbOk,       'note' => $dbOk       ? 'Connected' : $dbError],
            ['label' => 'Storage Symlink',  'ok' => $storageOk,  'note' => $storageOk  ? 'Linked' : 'Run php artisan storage:link'],
            ['label' => 'Logs Writable',    'ok' => $logsWritable,'note' => $logsWritable ? 'Writable' : 'Check permissions on storage/logs'],
            ['label' => 'PalPluss Config',  'ok' => $palplussOk, 'note' => $palplussOk ? 'Configured' : 'Missing PALPLUSS_SHORTCODE or PALPLUSS_SECRET'],
        ];

        return view('admin.system-health', compact(
            'checks', 'queueDriver', 'lastCleanup', 'lastReco'
        ));
    }
}
