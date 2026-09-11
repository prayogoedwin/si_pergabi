<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    public const TINGKAT_PROVINSI = 1;

    public const TINGKAT_KABUPATEN = 2;

    public const TINGKAT_KECAMATAN = 3;

    public const TINGKAT_KELURAHAN = 4;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'wilayah';

    protected $primaryKey = 'kode';

    protected $keyType = 'string';

    protected $fillable = [
        'kode',
        'nama',
    ];

    /**
     * @return array<int, string>
     */
    public static function tingkatOptions(): array
    {
        return [
            self::TINGKAT_PROVINSI => 'Provinsi',
            self::TINGKAT_KABUPATEN => 'Kabupaten/Kota',
            self::TINGKAT_KECAMATAN => 'Kecamatan',
            self::TINGKAT_KELURAHAN => 'Kelurahan/Desa',
        ];
    }

    public function tingkat(): int
    {
        return substr_count($this->kode, '.') + 1;
    }

    public function tingkatLabel(): string
    {
        return self::tingkatOptions()[$this->tingkat()] ?? 'Lainnya';
    }

    public function parentKode(): ?string
    {
        if (! str_contains($this->kode, '.')) {
            return null;
        }

        return substr($this->kode, 0, (int) strrpos($this->kode, '.'));
    }

    public function parent(): ?self
    {
        $parentKode = $this->parentKode();

        if ($parentKode === null) {
            return null;
        }

        return static::query()->find($parentKode);
    }

    /**
     * @param  Builder<Wilayah>  $query
     * @return Builder<Wilayah>
     */
    public function scopeTingkat(Builder $query, int $tingkat): Builder
    {
        $dots = $tingkat - 1;

        if ($dots < 0) {
            return $query;
        }

        return $query->whereRaw("(LENGTH(kode) - LENGTH(REPLACE(kode, '.', ''))) = ?", [$dots]);
    }

    /**
     * @param  Builder<Wilayah>  $query
     * @return Builder<Wilayah>
     */
    public function scopeAnak(Builder $query, ?string $parent = null): Builder
    {
        if ($parent === null || $parent === '') {
            return $query->tingkat(self::TINGKAT_PROVINSI);
        }

        $tingkat = substr_count($parent, '.') + 2;

        return $query->tingkat($tingkat)->where('kode', 'like', $parent.'.%');
    }
}
