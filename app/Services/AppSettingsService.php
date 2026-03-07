<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AppSettingsService
{
    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if (! Schema::hasTable('app_settings')) {
            return [];
        }

        /** @var Collection<int, AppSetting> $rows */
        $rows = AppSetting::query()->get(['key', 'value']);

        return $rows
            ->mapWithKeys(fn (AppSetting $row): array => [$row->key => data_get($row->value, 'value')])
            ->all();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable('app_settings')) {
            return $default;
        }

        $row = AppSetting::query()->where('key', $key)->first();

        if (! $row) {
            return $default;
        }

        return data_get($row->value, 'value', $default);
    }

    public function set(string $key, mixed $value): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        AppSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => ['value' => $value]]
        );
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }
}
