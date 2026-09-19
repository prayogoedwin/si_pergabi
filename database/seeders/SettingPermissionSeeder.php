<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class SettingPermissionSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    public const NAMES = [
        'view-organisasi',
        'edit-organisasi',
        'view-pendaftaran',
        'edit-pendaftaran',
        'view-integrasi',
        'edit-integrasi',
        'view-cache',
    ];

    /**
     * Menu Setting hanya Super Admin dan Admin PP (admin nasional).
     *
     * @var list<string>
     */
    public const ROLE_SLUGS = [
        Role::SUPER_ADMIN,
        Role::ADMIN_PP,
    ];

    public function run(): void
    {
        foreach (self::NAMES as $name) {
            Permission::query()->firstOrCreate(['name' => $name]);
        }

        $ids = Permission::query()->whereIn('name', self::NAMES)->pluck('id');

        Role::query()
            ->whereIn('slug', self::ROLE_SLUGS)
            ->get()
            ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching($ids));

        Role::query()
            ->whereNotIn('slug', self::ROLE_SLUGS)
            ->get()
            ->each(fn (Role $role) => $role->permissions()->detach($ids));
    }
}
