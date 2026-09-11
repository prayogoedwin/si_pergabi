<?php

namespace App\Services;

use App\Helpers\Area;
use App\Models\Anggota;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Cache;

class DashboardCacheService
{
    public const PREFIX = 'dashboard';

    public const KEY_ALL = 'dashboard:all';

    public const PREFIX_PROVINSI = 'dashboard:provinsi:';

    public const PREFIX_KABUPATEN = 'dashboard:kabupaten:';

    public const INDEX_KEY = 'pergabi.cache.index';

    /**
     * @return list<array{prefix: string, label: string}>
     */
    public static function prefixGroups(): array
    {
        return [
            ['prefix' => self::PREFIX, 'label' => 'Semua dashboard'],
            ['prefix' => self::KEY_ALL, 'label' => 'Nasional (all)'],
            ['prefix' => rtrim(self::PREFIX_PROVINSI, ':'), 'label' => 'Provinsi'],
            ['prefix' => rtrim(self::PREFIX_KABUPATEN, ':'), 'label' => 'Kabupaten/kota'],
            ['prefix' => 'pergabi', 'label' => 'Pengaturan'],
        ];
    }

    public function keyFor(User $user, ?string $provinsi = null, ?string $kabupaten = null): string
    {
        return match ($user->organisasiLevel()) {
            Area::CABANG => $this->kabupatenKey($user->pc_kode),
            Area::DAERAH => $this->provinsiKey($user->pd_kode),
            default => $this->key($provinsi, $kabupaten),
        };
    }

    public function key(?string $provinsi, ?string $kabupaten): string
    {
        if (filled($kabupaten)) {
            return $this->kabupatenKey($kabupaten);
        }

        if (filled($provinsi)) {
            return $this->provinsiKey($provinsi);
        }

        return self::KEY_ALL;
    }

    public function provinsiKey(?string $kode): string
    {
        return self::PREFIX_PROVINSI.(filled($kode) ? $kode : '_');
    }

    public function kabupatenKey(?string $kode): string
    {
        return self::PREFIX_KABUPATEN.(filled($kode) ? $kode : '_');
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function remember(string $key, callable $callback): mixed
    {
        $this->register($key);

        return Cache::remember($key, (int) config('pergabi.dashboard_cache_ttl', 3600), $callback);
    }

    public function forgetForAnggota(Anggota $anggota): void
    {
        $this->forgetKey(self::KEY_ALL);

        if (filled($anggota->pd_kode)) {
            $this->forgetKey($this->provinsiKey($anggota->pd_kode));
        }

        if (filled($anggota->pc_kode)) {
            $this->forgetKey($this->kabupatenKey($anggota->pc_kode));
        }
    }

    public function forgetKey(string $key): void
    {
        if (! $this->isManaged($key)) {
            return;
        }

        Cache::forget($key);
        $this->unregister($key);
    }

    public function forgetPrefix(string $prefix): int
    {
        if (! $this->isManaged($prefix)) {
            return 0;
        }

        $deleted = 0;

        foreach ($this->trackedKeys() as $key) {
            if ($key === $prefix || str_starts_with($key, $prefix.':') || str_starts_with($key, $prefix.'.')) {
                Cache::forget($key);
                $this->unregister($key);
                $deleted++;
            }
        }

        return $deleted;
    }

    public function flushAll(): void
    {
        Cache::flush();
    }

    public function isManaged(string $value): bool
    {
        return $value === self::PREFIX
            || $value === 'pergabi'
            || str_starts_with($value, self::PREFIX.':')
            || str_starts_with($value, 'pergabi.');
    }

    /**
     * @return list<array{key: string, group: string, wilayah: string, exists: bool}>
     */
    public function entries(): array
    {
        $keys = $this->trackedKeys();
        $kodes = collect($keys)
            ->map(fn (string $key) => $this->kodeFromKey($key))
            ->filter()
            ->unique()
            ->values();

        $nama = $kodes->isEmpty()
            ? collect()
            : Wilayah::query()->whereIn('kode', $kodes)->pluck('nama', 'kode');

        $entries = [];

        foreach ($keys as $key) {
            $kode = $this->kodeFromKey($key);
            $entries[] = [
                'key' => $key,
                'group' => $this->groupLabel($key),
                'wilayah' => $kode ? ($nama[$kode] ?? $kode) : 'Nasional',
                'exists' => Cache::has($key),
            ];
        }

        return $entries;
    }

    public function register(string $key): void
    {
        $keys = $this->trackedKeys();

        if (in_array($key, $keys, true)) {
            return;
        }

        $keys[] = $key;
        sort($keys);
        Cache::forever(self::INDEX_KEY, $keys);
    }

    /**
     * @return list<string>
     */
    public function trackedKeys(): array
    {
        $keys = Cache::get(self::INDEX_KEY, []);

        if (! is_array($keys)) {
            return [];
        }

        $known = [SettingService::CACHE_KEY];

        return array_values(array_unique([...$known, ...array_values($keys)]));
    }

    private function unregister(string $key): void
    {
        $keys = array_values(array_filter(
            $this->trackedKeys(),
            fn (string $tracked) => $tracked !== $key,
        ));

        Cache::forever(self::INDEX_KEY, $keys);
    }

    private function groupLabel(string $key): string
    {
        return match (true) {
            $key === self::KEY_ALL => 'Nasional',
            str_starts_with($key, self::PREFIX_PROVINSI) => 'Provinsi',
            str_starts_with($key, self::PREFIX_KABUPATEN) => 'Kabupaten/kota',
            str_starts_with($key, 'pergabi') => 'Pengaturan',
            default => 'Lainnya',
        };
    }

    private function kodeFromKey(string $key): ?string
    {
        foreach ([self::PREFIX_PROVINSI, self::PREFIX_KABUPATEN] as $prefix) {
            if (str_starts_with($key, $prefix)) {
                $kode = substr($key, strlen($prefix));

                return $kode === '' || $kode === '_' ? null : $kode;
            }
        }

        return null;
    }
}
