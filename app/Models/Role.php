<?php

namespace App\Models;

use App\Helpers\Area;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    public const SUPER_ADMIN = 'super-admin';

    public const ADMIN_PP = 'admin-pp';

    public const ADMIN_PD = 'admin-pd';

    public const ADMIN_PC = 'admin-pc';

    public const ANGGOTA = 'anggota';

    protected $fillable = [
        'name',
        'slug',
        'area',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->slug === self::SUPER_ADMIN;
    }

    public function areaLabel(): string
    {
        return Area::label($this->area);
    }

    /**
     * @param  Builder<Role>  $query
     * @return Builder<Role>
     */
    public function scopeVisibleTo($query, User $viewer)
    {
        if ($viewer->isSuperAdmin()) {
            return $query;
        }

        return $query->where('slug', '!=', self::SUPER_ADMIN);
    }

    /**
     * @param  Builder<Role>  $query
     * @return Builder<Role>
     */
    public function scopeAssignable($query)
    {
        return $query->where('is_active', true);
    }
}
