<?php

namespace App\Models;

use Database\Factories\LinkInformasiFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkInformasi extends Model
{
    /** @use HasFactory<LinkInformasiFactory> */
    use HasFactory;

    public const STATUS_NONAKTIF = 0;

    public const STATUS_AKTIF = 1;

    protected $table = 'link_informasi';

    protected $fillable = [
        'nama',
        'url',
        'keterangan',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    public function isAktif(): bool
    {
        return $this->status === self::STATUS_AKTIF;
    }

    /**
     * @param  Builder<LinkInformasi>  $query
     * @return Builder<LinkInformasi>
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    /**
     * @return Collection<int, LinkInformasi>
     */
    public static function untukMenuPortal(): Collection
    {
        return static::query()
            ->aktif()
            ->orderBy('nama')
            ->get(['id', 'nama', 'url']);
    }
}
