<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_seeder_creates_setting_permissions_and_assigns_them_to_admin_pp(): void
    {
        foreach (SettingPermissionSeeder::NAMES as $name) {
            $this->assertTrue(Permission::query()->where('name', $name)->exists(), $name);
        }

        $adminPp = Role::query()->where('slug', Role::ADMIN_PP)->firstOrFail();

        foreach (SettingPermissionSeeder::NAMES as $name) {
            $this->assertTrue(
                $adminPp->permissions()->where('name', $name)->exists(),
                'Admin PP missing '.$name
            );
        }

        $superAdmin = Role::query()->where('slug', Role::SUPER_ADMIN)->firstOrFail();

        foreach (SettingPermissionSeeder::NAMES as $name) {
            $this->assertTrue(
                $superAdmin->permissions()->where('name', $name)->exists(),
                'Super Admin missing '.$name
            );
        }

        $this->assertFalse(
            $adminPp->permissions()->where('name', 'view-permissions')->exists()
        );
    }

    public function test_setting_permissions_are_not_given_to_other_roles(): void
    {
        $otherSlugs = Role::query()
            ->whereNotIn('slug', SettingPermissionSeeder::ROLE_SLUGS)
            ->pluck('slug');

        $this->assertNotEmpty($otherSlugs);

        foreach ($otherSlugs as $slug) {
            $role = Role::query()->where('slug', $slug)->firstOrFail();

            foreach (SettingPermissionSeeder::NAMES as $name) {
                $this->assertFalse(
                    $role->permissions()->where('name', $name)->exists(),
                    $slug.' should not have '.$name
                );
            }
        }
    }

    public function test_seeder_revokes_setting_permissions_from_non_national_roles(): void
    {
        $adminPd = Role::query()->where('slug', Role::ADMIN_PD)->firstOrFail();
        $ids = Permission::query()->whereIn('name', SettingPermissionSeeder::NAMES)->pluck('id');
        $adminPd->permissions()->syncWithoutDetaching($ids);

        $this->assertTrue($adminPd->permissions()->where('name', 'view-integrasi')->exists());

        $this->seed(SettingPermissionSeeder::class);

        foreach (SettingPermissionSeeder::NAMES as $name) {
            $this->assertFalse(
                $adminPd->fresh()->permissions()->where('name', $name)->exists(),
                'Admin PD still has '.$name
            );
        }
    }

    public function test_admin_pd_cannot_open_setting_menus(): void
    {
        $adminPd = User::query()->where('email', 'admin.pd@example.com')->firstOrFail();

        $this->actingAs($adminPd)->get(route('settings.organisasi.edit'))->assertForbidden();
        $this->actingAs($adminPd)->get(route('settings.pendaftaran.edit'))->assertForbidden();
        $this->actingAs($adminPd)->get(route('settings.integrasi.edit'))->assertForbidden();
        $this->actingAs($adminPd)->get(route('cache.index'))->assertForbidden();

        $this->actingAs($adminPd)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('settings.organisasi.edit'), false)
            ->assertDontSee(route('settings.pendaftaran.edit'), false)
            ->assertDontSee(route('settings.integrasi.edit'), false)
            ->assertDontSee('Integrasi')
            ->assertDontSee('Pengaturan Pendaftaran');
    }

    public function test_admin_pc_cannot_open_setting_menus(): void
    {
        $adminPc = User::query()->where('email', 'admin.pc@example.com')->firstOrFail();

        $this->actingAs($adminPc)->get(route('settings.organisasi.edit'))->assertForbidden();
        $this->actingAs($adminPc)->get(route('settings.pendaftaran.edit'))->assertForbidden();
        $this->actingAs($adminPc)->get(route('settings.integrasi.edit'))->assertForbidden();
        $this->actingAs($adminPc)->get(route('cache.index'))->assertForbidden();
    }
}
