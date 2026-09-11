<?php

namespace App\Models;

use App\Helpers\Area;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'theme_preference',
        'email_verified_at',
        'pd_kode',
        'pc_kode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function anggota(): HasOne
    {
        return $this->hasOne(Anggota::class);
    }

    public function isAnggota(): bool
    {
        return $this->hasRole(Role::ANGGOTA);
    }

    public function isPengurus(): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(
                fn (Role $role) => $role->is_active && $role->slug !== Role::ANGGOTA
            );
        }

        return $this->roles()->where('is_active', true)->where('slug', '!=', Role::ANGGOTA)->exists();
    }

    public function usesMemberPortal(): bool
    {
        if ($this->isPengurus()) {
            return false;
        }

        $hasAnggota = $this->relationLoaded('anggota')
            ? $this->anggota !== null
            : $this->anggota()->exists();

        return $hasAnggota || $this->isAnggota();
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function homeRoute(bool $absolute = false, array $query = []): string
    {
        $url = route($this->usesMemberPortal() ? 'portal.show' : 'dashboard', absolute: $absolute);

        if ($query !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?').http_build_query($query);
        }

        return $url;
    }

    public function isSuperAdmin(): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(fn (Role $role) => $role->slug === Role::SUPER_ADMIN && $role->is_active);
        }

        return $this->hasRole(Role::SUPER_ADMIN);
    }

    public function resetPasswordToTemporary(): string
    {
        $plain = Str::password(12, symbols: false);

        $this->update(['password' => $plain]);

        return $plain;
    }

    /**
     * @return Collection<int, Role>
     */
    public function assignableRoles(): Collection
    {
        return Role::query()
            ->visibleTo($this)
            ->assignable()
            ->orderBy('name')
            ->get();
    }

    public function isAdminPusat(): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(fn (Role $role) => $role->slug === Role::ADMIN_PP && $role->is_active);
        }

        return $this->hasRole(Role::ADMIN_PP);
    }

    public function organisasiLevel(): string
    {
        if ($this->isSuperAdmin()) {
            return Area::PUSAT;
        }

        $areas = $this->relationLoaded('roles')
            ? $this->roles->where('is_active', true)->pluck('area')
            : $this->roles()->where('is_active', true)->pluck('area');

        if ($areas->contains(Area::PUSAT)) {
            return Area::PUSAT;
        }

        if ($areas->contains(Area::DAERAH)) {
            return Area::DAERAH;
        }

        if ($areas->contains(Area::CABANG)) {
            return Area::CABANG;
        }

        return Area::PUSAT;
    }

    public function isNasional(): bool
    {
        return $this->organisasiLevel() === Area::PUSAT;
    }

    public function canAccessAnggota(Anggota $anggota): bool
    {
        return match ($this->organisasiLevel()) {
            Area::CABANG => filled($this->pc_kode) && $anggota->pc_kode === $this->pc_kode,
            Area::DAERAH => filled($this->pd_kode) && $anggota->pd_kode === $this->pd_kode,
            default => true,
        };
    }

    public function wilayahTugasLabel(): string
    {
        return match ($this->organisasiLevel()) {
            Area::CABANG => 'PC '.($this->pc?->nama ?? $this->pc_kode ?: 'belum di-set'),
            Area::DAERAH => 'PD '.($this->pd?->nama ?? $this->pd_kode ?: 'belum di-set'),
            default => 'Nasional',
        };
    }

    public function pd(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'pd_kode', 'kode');
    }

    public function pc(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'pc_kode', 'kode');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->where('is_active', true)->exists();
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeVisibleTo($query, User $viewer)
    {
        if ($viewer->isSuperAdmin()) {
            return $query;
        }

        return $query->whereDoesntHave('roles', function ($roles) {
            $roles->where('slug', Role::SUPER_ADMIN);
        });
    }

    /**
     * @param  array<int|string>  $roleIds
     * @return array<int>
     */
    public function filterAssignableRoleIds(array $roleIds, ?User $existingUser = null): array
    {
        $assignable = Role::query()
            ->visibleTo($this)
            ->assignable()
            ->whereIn('id', $roleIds)
            ->pluck('id');

        if ($existingUser !== null) {
            $keepInactive = $existingUser->roles()
                ->visibleTo($this)
                ->where('is_active', false)
                ->pluck('roles.id');

            return $assignable->merge($keepInactive)->unique()->values()->all();
        }

        return $assignable->all();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()->where('is_active', true)->whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission);
        })->exists();
    }

    public function assignRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->orWhere('name', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching([$role->id]);
    }

    public function removeRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->orWhere('name', $role)->firstOrFail();
        }

        $this->roles()->detach($role->id);
    }
}
