<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_download_users_excel(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee(route('users.export'), false);

        $this->actingAs($admin)
            ->get(route('users.export'))
            ->assertOk()
            ->assertDownload('users-'.now()->format('Y-m-d').'.xlsx');
    }

    public function test_member_cannot_download_users_excel(): void
    {
        $this->actingAs(User::query()->where('email', 'anggota@example.com')->firstOrFail())
            ->get(route('users.export'))
            ->assertForbidden();
    }
}
