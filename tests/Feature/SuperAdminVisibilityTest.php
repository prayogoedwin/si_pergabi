<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_does_not_see_example_or_roles_menus(): void
    {
        $this->actingAs($this->admin())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Example two level')
            ->assertDontSee('Example three level')
            ->assertDontSee(route('roles.index'))
            ->assertDontSee(route('cache.index'))
            ->assertDontSee(route('settings.organisasi.edit'))
            ->assertDontSee(route('settings.pendaftaran.edit'))
            ->assertDontSee('Identitas Organisasi')
            ->assertDontSee('Pengaturan Pendaftaran');
    }

    public function test_super_admin_sees_roles_menu(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Roles')
            ->assertSee('Setting')
            ->assertSee('Identitas Organisasi')
            ->assertSee('Pengaturan Pendaftaran')
            ->assertSee('Cache');
    }

    public function test_admin_cannot_see_super_admin_user_in_the_list(): void
    {
        $response = $this->actingAs($this->admin())
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('users.index'));

        $response->assertOk();

        $emails = collect($response->json('data'))->pluck('email');

        $this->assertFalse($emails->contains('superadmin@example.com'));
        $this->assertTrue($emails->contains('admin@example.com'));
    }

    public function test_only_super_admin_and_admin_pp_can_manage_users(): void
    {
        $this->actingAs($this->admin())
            ->get(route('users.index'))
            ->assertOk();

        $this->actingAs($this->superAdmin())
            ->get(route('users.index'))
            ->assertOk();

        foreach (['admin.pd@example.com', 'admin.pc@example.com'] as $email) {
            $this->actingAs(User::query()->where('email', $email)->firstOrFail())
                ->get(route('users.index'))
                ->assertForbidden();

            $this->actingAs(User::query()->where('email', $email)->firstOrFail())
                ->get(route('users.create'))
                ->assertForbidden();
        }

        $this->assertFalse(
            Role::query()->where('slug', 'admin-pd')->firstOrFail()
                ->permissions()->where('name', 'view-users')->exists()
        );
        $this->assertFalse(
            Role::query()->where('slug', 'ketua-pd')->firstOrFail()
                ->permissions()->where('name', 'view-users')->exists()
        );
    }

    public function test_admin_pp_can_reset_user_password(): void
    {
        $target = User::query()->where('email', 'admin.pd@example.com')->firstOrFail();

        $response = $this->actingAs($this->admin())
            ->from(route('users.show', $target))
            ->post(route('users.reset-password', $target));

        $response->assertRedirect()->assertSessionHas('password_reset');

        $plain = $this->app['session']->get('password_reset');
        $this->assertNotEmpty($plain);
        $this->assertTrue(Hash::check($plain, $target->fresh()->password));
        $this->assertFalse(Hash::check('password', $target->fresh()->password));
    }

    public function test_admin_pp_cannot_reset_super_admin_password(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.reset-password', $this->superAdmin()))
            ->assertNotFound();
    }

    public function test_admin_pd_cannot_reset_user_password(): void
    {
        $target = User::query()->where('email', 'admin.pc@example.com')->firstOrFail();

        $this->actingAs(User::query()->where('email', 'admin.pd@example.com')->firstOrFail())
            ->post(route('users.reset-password', $target))
            ->assertForbidden();
    }

    public function test_super_admin_can_see_super_admin_user_in_the_list(): void
    {
        $response = $this->actingAs($this->superAdmin())
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('users.index'));

        $response->assertOk();

        $emails = collect($response->json('data'))->pluck('email');

        $this->assertTrue($emails->contains('superadmin@example.com'));
    }

    public function test_admin_cannot_access_roles_management(): void
    {
        $this->actingAs($this->admin())
            ->get(route('roles.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_see_super_admin_role_in_the_list(): void
    {
        $response = $this->actingAs($this->superAdmin())
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('roles.index'));

        $response->assertOk();

        $roleNames = collect($response->json('data'))->pluck('name');

        $this->assertTrue($roleNames->contains('Super Admin'));
        $this->assertTrue($roleNames->contains('Admin PP'));
    }

    public function test_admin_cannot_assign_super_admin_role_when_creating_a_user(): void
    {
        $this->actingAs($this->admin())
            ->get(route('users.create'))
            ->assertOk()
            ->assertDontSee('Super Admin');

        $this->actingAs($this->superAdmin())
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('Super Admin');
    }

    public function test_admin_pp_cannot_see_super_admin_role_when_editing_a_user(): void
    {
        $target = User::query()->where('email', 'admin.pd@example.com')->firstOrFail();

        $this->actingAs($this->admin())
            ->get(route('users.edit', $target))
            ->assertOk()
            ->assertDontSee('Super Admin');
    }

    public function test_admin_cannot_view_super_admin_user_directly(): void
    {
        $this->actingAs($this->admin())
            ->get(route('users.show', $this->superAdmin()))
            ->assertNotFound();
    }

    public function test_admin_cannot_assign_super_admin_role_via_form_tampering(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), [
                'name' => 'New Client Admin',
                'email' => 'client-admin@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'roles' => [$this->superAdminRole()->id],
            ])
            ->assertSessionHasErrors('roles.0');

        $this->assertNull(User::where('email', 'client-admin@example.com')->first());
    }

    public function test_inactive_role_is_hidden_from_user_form(): void
    {
        $ketuaPc = Role::query()->where('slug', 'ketua-pc')->firstOrFail();
        $ketuaPc->update(['is_active' => false]);

        $this->actingAs($this->admin())
            ->get(route('users.create'))
            ->assertOk()
            ->assertDontSee('Ketua PC')
            ->assertSee('Admin PP');
    }

    public function test_super_admin_can_toggle_role_status(): void
    {
        $ketuaPc = Role::query()->where('slug', 'ketua-pc')->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->from(route('roles.index'))
            ->patch(route('roles.toggle', $ketuaPc));

        $this->assertFalse($ketuaPc->fresh()->is_active);
    }

    public function test_super_admin_cannot_deactivate_super_admin_role(): void
    {
        $this->actingAs($this->superAdmin())
            ->patch(route('roles.toggle', $this->superAdminRole()))
            ->assertForbidden();

        $this->assertTrue($this->superAdminRole()->fresh()->is_active);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@example.com')->firstOrFail();
    }

    private function superAdmin(): User
    {
        return User::where('email', 'superadmin@example.com')->firstOrFail();
    }

    private function superAdminRole(): Role
    {
        return Role::where('slug', Role::SUPER_ADMIN)->firstOrFail();
    }
}
