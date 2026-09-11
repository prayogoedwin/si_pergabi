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
        'view-cache',
    ];

    public function run(): void
    {
        foreach (self::NAMES as $name) {
            Permission::query()->firstOrCreate(['name' => $name]);
        }

        $ids = Permission::query()->whereIn('name', self::NAMES)->pluck('id');

        Role::query()->where('slug', Role::ADMIN_PP)->first()?->permissions()->syncWithoutDetaching($ids);
        Role::query()->where('slug', Role::SUPER_ADMIN)->first()?->permissions()->syncWithoutDetaching($ids);
    }
}
