<?php

namespace App\Helpers;

class Area
{
    public const PUSAT = 'pusat';

    public const DAERAH = 'daerah';

    public const CABANG = 'cabang';

    /**
     * @return array<string, array{code: string, short: string, name: string, wilayah: string}>
     */
    public static function all(): array
    {
        return [
            self::PUSAT => [
                'code' => self::PUSAT,
                'short' => 'PP',
                'name' => 'Pengurus Pusat',
                'wilayah' => 'Nasional',
            ],
            self::DAERAH => [
                'code' => self::DAERAH,
                'short' => 'PD',
                'name' => 'Pengurus Daerah',
                'wilayah' => 'Provinsi',
            ],
            self::CABANG => [
                'code' => self::CABANG,
                'short' => 'PC',
                'name' => 'Pengurus Cabang',
                'wilayah' => 'Kabupaten/Kota',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::all());
    }

    /**
     * @return array{code: string, short: string, name: string, wilayah: string}|null
     */
    public static function get(?string $code): ?array
    {
        if ($code === null) {
            return null;
        }

        return self::all()[$code] ?? null;
    }

    public static function label(?string $code): string
    {
        $area = self::get($code);

        if ($area === null) {
            return '-';
        }

        return "{$area['short']} — {$area['name']} ({$area['wilayah']})";
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::all() as $code => $area) {
            $options[$code] = self::label($code);
        }

        return $options;
    }
}
