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

        $this->assertFalse(
            $adminPp->permissions()->where('name', 'view-permissions')->exists()
        );
    }

    public function test_admin_pd_cannot_open_setting_menus(): void
    {
        $adminPd = User::query()->where('email', 'admin.pd@example.com')->firstOrFail();

        $this->actingAs($adminPd)->get(route('settings.organisasi.edit'))->assertForbidden();
        $this->actingAs($adminPd)->get(route('settings.pendaftaran.edit'))->assertForbidden();
        $this->actingAs($adminPd)->get(route('cache.index'))->assertForbidden();
    }
}
