<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Collection;

class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = SystemSetting::where('key', $key)->first();
        return $setting ? $setting->typedValue() : $default;
    }

    public function set(
        string $key,
        mixed  $value,
        string $type        = 'string',
        string $group       = null,
        string $description = null,
        bool   $isPublic    = false
    ): SystemSetting {
        $serialised = match ($type) {
            'json'    => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default   => (string) $value,
        };

        return SystemSetting::updateOrCreate(
            ['key' => $key],
            [
                'value'       => $serialised,
                'type'        => $type,
                'group'       => $group,
                'description' => $description,
                'is_public'   => $isPublic,
            ]
        );
    }

    public function getGroup(string $group): Collection
    {
        return SystemSetting::where('group', $group)->get();
    }

    public function getPublicSettings(): Collection
    {
        return SystemSetting::where('is_public', true)->get();
    }

    public function boolean(string $key, bool $default = false): bool
    {
        $val = $this->get($key);
        return $val === null ? $default : (bool) $val;
    }

    public function number(string $key, float $default = 0): float
    {
        $val = $this->get($key);
        return $val === null ? $default : (float) $val;
    }

    public function json(string $key, array $default = []): array
    {
        $val = $this->get($key);
        return is_array($val) ? $val : $default;
    }
}
