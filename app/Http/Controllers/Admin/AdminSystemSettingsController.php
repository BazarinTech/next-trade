<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\AdminLogService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSystemSettingsController extends Controller
{
    public function __construct(
        private SettingsService $settings,
        private AdminLogService $logger
    ) {}

    public function index(): View
    {
        $groups = SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        return view('admin.system-settings.index', compact('groups'));
    }

    public function update(Request $request): RedirectResponse
    {
        $old = [];
        $new = [];

        foreach ($request->except(['_token']) as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            if (!$setting) {
                continue;
            }

            $old[$key] = $setting->value;

            $normalized = match ($setting->type) {
                'boolean' => $value === '1' || $value === 'true' || $value === true ? '1' : '0',
                'number'  => (string) (float) $value,
                default   => (string) $value,
            };

            $setting->update(['value' => $normalized]);
            $new[$key] = $normalized;
        }

        // Checkboxes that are not submitted come as null — treat as false for booleans
        $booleanKeys = SystemSetting::where('type', 'boolean')->pluck('key');
        foreach ($booleanKeys as $key) {
            if (!array_key_exists($key, $request->except(['_token']))) {
                $setting = SystemSetting::where('key', $key)->first();
                if ($setting) {
                    $old[$key] = $setting->value;
                    $setting->update(['value' => '0']);
                    $new[$key] = '0';
                }
            }
        }

        $this->logger->log(auth()->user(), 'system_settings_updated', SystemSetting::class, null, $old, $new);

        return back()->with('success', 'System settings saved.');
    }
}
