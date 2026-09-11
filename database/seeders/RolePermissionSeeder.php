<?php

namespace Database\Seeders;

use App\Helpers\Area;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view-users',
            'show-users',
            'create-users',
            'edit-users',
            'download-users',
            'delete-users',
            'view-roles',
            'show-roles',
            'create-roles',
            'edit-roles',
            'download-roles',
            'delete-roles',
            'view-permissions',
            'show-permissions',
            'create-permissions',
            'edit-permissions',
            'download-permissions',
            'delete-permissions',
            'view-wilayah',
            'show-wilayah',
            'create-wilayah',
            'edit-wilayah',
            'download-wilayah',
            'delete-wilayah',
            'view-area',
            'view-anggota',
            'show-anggota',
            'verify-anggota-pc',
            'validate-anggota-pd',
            'approve-anggota-pp',
            'view-organisasi',
            'edit-organisasi',
            'view-pendaftaran',
            'edit-pendaftaran',
            'view-cache',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        $userPermissions = Permission::whereIn('name', [
            'view-users', 'show-users', 'create-users', 'edit-users', 'download-users', 'delete-users',
        ])->pluck('id');

        $leaderPermissions = Permission::whereIn('name', [
            'view-anggota', 'show-anggota',
        ])->pluck('id');

        $adminPcPermissions = Permission::whereIn('name', [
            'view-anggota', 'show-anggota', 'verify-anggota-pc',
        ])->pluck('id');

        $adminPdPermissions = Permission::whereIn('name', [
            'view-anggota', 'show-anggota', 'validate-anggota-pd',
        ])->pluck('id');

        $adminPpPermissions = $userPermissions->merge(
            Permission::whereIn('name', [
                'view-wilayah', 'show-wilayah', 'create-wilayah', 'edit-wilayah', 'download-wilayah', 'delete-wilayah',
                'view-area',
                'view-anggota', 'show-anggota', 'approve-anggota-pp',
            ])->pluck('id')
        );

        $roles = [
            ['slug' => Role::SUPER_ADMIN, 'name' => 'Super Admin', 'area' => Area::PUSAT, 'permissions' => 'all'],
            ['slug' => Role::ADMIN_PP, 'name' => 'Admin PP', 'area' => Area::PUSAT, 'permissions' => 'admin-pp'],
            ['slug' => Role::ADMIN_PD, 'name' => 'Admin PD', 'area' => Area::DAERAH, 'permissions' => 'admin-pd'],
            ['slug' => Role::ADMIN_PC, 'name' => 'Admin PC', 'area' => Area::CABANG, 'permissions' => 'admin-pc'],
            ['slug' => 'ketua-umum-pp', 'name' => 'Ketua Umum PP', 'area' => Area::PUSAT, 'permissions' => 'leader'],
            ['slug' => 'sekretaris-jenderal-pp', 'name' => 'Sekretaris Jenderal PP', 'area' => Area::PUSAT, 'permissions' => 'leader'],
            ['slug' => 'ketua-pd', 'name' => 'Ketua PD', 'area' => Area::DAERAH, 'permissions' => 'leader'],
            ['slug' => 'sekretaris-pd', 'name' => 'Sekretaris PD', 'area' => Area::DAERAH, 'permissions' => 'leader'],
            ['slug' => 'ketua-pc', 'name' => 'Ketua PC', 'area' => Area::CABANG, 'permissions' => 'leader'],
            ['slug' => 'sekretaris-pc', 'name' => 'Sekretaris PC', 'area' => Area::CABANG, 'permissions' => 'leader'],
            ['slug' => Role::ANGGOTA, 'name' => 'Anggota', 'area' => null, 'permissions' => 'none'],
        ];

        $keptSlugs = [];

        foreach ($roles as $roleData) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $roleData['slug']],
                [
                    'name' => $roleData['name'],
                    'area' => $roleData['area'],
                    'is_active' => true,
                ]
            );

            $permissionIds = match ($roleData['permissions']) {
                'all' => Permission::pluck('id'),
                'admin-pp' => $adminPpPermissions,
                'admin-pd' => $adminPdPermissions,
                'admin-pc' => $adminPcPermissions,
                'leader' => $leaderPermissions,
                default => collect(),
            };

            $role->permissions()->sync($permissionIds);
            $keptSlugs[] = $role->slug;
        }

        $this->removeLegacyRoles($keptSlugs);
        $this->seedUsers();
        $this->call(SettingPermissionSeeder::class);
    }

    /**
     * @param  list<string>  $keptSlugs
     */
    private function removeLegacyRoles(array $keptSlugs): void
    {
        Role::query()
            ->whereNotIn('slug', $keptSlugs)
            ->get()
            ->each(function (Role $role) {
                $role->users()->detach();
                $role->permissions()->detach();
                $role->delete();
            });
    }

    private function seedUsers(): void
    {
        $accounts = [
            ['email' => 'superadmin@example.com', 'name' => 'Super Admin', 'role' => Role::SUPER_ADMIN],
            ['email' => 'admin@example.com', 'name' => 'Admin PP', 'role' => Role::ADMIN_PP],
            ['email' => 'admin.pd@example.com', 'name' => 'Admin PD', 'role' => 'admin-pd'],
            ['email' => 'admin.pc@example.com', 'name' => 'Admin PC', 'role' => 'admin-pc'],
            ['email' => 'anggota@example.com', 'name' => 'Anggota', 'role' => 'anggota'],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'pd_kode' => match ($account['role']) {
                        Role::ADMIN_PD => '36',
                        Role::ADMIN_PC => '36',
                        default => null,
                    },
                    'pc_kode' => $account['role'] === Role::ADMIN_PC ? '36.71' : null,
                ]
            );

            $role = Role::query()->where('slug', $account['role'])->firstOrFail();
            $user->roles()->sync([$role->id]);
        }
    }
}
