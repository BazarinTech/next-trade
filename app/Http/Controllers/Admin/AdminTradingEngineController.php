<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SimulationSetting;
use App\Services\AdminLogService;
use App\Services\SimulationConfigService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTradingEngineController extends Controller
{
    public function __construct(
        private SimulationConfigService $simConfig,
        private AdminLogService         $logger
    ) {}

    public function index(): View
    {
        $configs      = SimulationSetting::orderByRaw("FIELD(difficulty,'easy','normal','hard','extreme')")->get();
        $activeConfig = $this->simConfig->getActiveConfig();
        $recentLogs   = $this->logger->recent(20);

        return view('admin.trading-engine', compact('configs', 'activeConfig', 'recentLogs'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id'                    => 'required|exists:simulation_settings,id',
            'win_probability'       => 'required|numeric|min:0|max:100',
            'volatility_multiplier' => 'required|numeric|min:0.01|max:10',
            'trend_strength'        => 'required|numeric|min:0|max:2',
            'min_profit_multiplier' => 'required|numeric|min:0.01|max:1',
            'max_profit_multiplier' => 'required|numeric|min:0.01|max:1',
            'max_loss_multiplier'   => 'required|numeric|min:0.01|max:2',
            'candle_speed_seconds'  => 'required|integer|min:1|max:60',
        ]);

        $config = SimulationSetting::findOrFail($validated['id']);
        $old    = $config->only([
            'win_probability','volatility_multiplier','trend_strength',
            'min_profit_multiplier','max_profit_multiplier','candle_speed_seconds',
        ]);

        $config->update([
            'win_probability'       => $validated['win_probability'],
            'volatility_multiplier' => $validated['volatility_multiplier'],
            'trend_strength'        => $validated['trend_strength'],
            'min_profit_multiplier' => $validated['min_profit_multiplier'],
            'max_profit_multiplier' => $validated['max_profit_multiplier'],
            'max_loss_multiplier'   => $validated['max_loss_multiplier'],
            'candle_speed_seconds'  => $validated['candle_speed_seconds'],
        ]);

        $this->logger->log(
            auth()->user(),
            'simulation_setting_updated',
            SimulationSetting::class,
            $config->id,
            $old,
            $config->fresh()->only(array_keys($old))
        );

        $this->simConfig->invalidateCache();

        return back()->with('success', "Simulation settings for «{$config->difficulty_label}» updated.");
    }

    public function activate(SimulationSetting $simulationSetting): RedirectResponse
    {
        $prev = $this->simConfig->getActiveConfig();

        $this->simConfig->setActiveConfig($simulationSetting);

        $this->logger->log(
            auth()->user(),
            'difficulty_changed',
            SimulationSetting::class,
            $simulationSetting->id,
            ['previous_difficulty' => $prev?->difficulty],
            ['new_difficulty'      => $simulationSetting->difficulty]
        );

        return back()->with('success', "Active difficulty switched to «{$simulationSetting->difficulty_label}».");
    }

    public function resetDefaults(): RedirectResponse
    {
        $prev = $this->simConfig->getActiveConfig()?->difficulty;

        $this->simConfig->createDefaultSettings();
        $this->simConfig->invalidateCache();

        $this->logger->log(
            auth()->user(),
            'simulation_defaults_reset',
            null, null,
            ['previous_active' => $prev],
            ['restored_to'     => 'factory defaults']
        );

        return back()->with('success', 'All simulation settings reset to factory defaults.');
    }
}
