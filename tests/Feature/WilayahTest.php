<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wilayah;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\WilayahSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WilayahTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(WilayahSeeder::class);
    }

    public function test_tables_use_prgb_prefix(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('wilayah'));

        $tables = collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table'"))
            ->pluck('name');

        $this->assertTrue($tables->contains('prgb_users'));
        $this->assertTrue($tables->contains('prgb_wilayah'));
        $this->assertFalse($tables->contains('users'));
        $this->assertFalse($tables->contains('wilayah'));
    }

    public function test_super_admin_can_view_wilayah_index(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('wilayah.index'))
            ->assertOk()
            ->assertSee('Wilayah');
    }

    public function test_admin_pp_can_view_wilayah_index_and_menu(): void
    {
        $this->actingAs($this->adminPp())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Master')
            ->assertSee('Wilayah')
            ->assertSee('Area')
            ->assertSee(route('wilayah.index'))
            ->assertSee(route('area.index'));

        $this->actingAs($this->adminPp())
            ->get(route('wilayah.index'))
            ->assertOk();
    }

    public function test_admin_pd_cannot_access_wilayah(): void
    {
        $this->actingAs($this->adminPd())
            ->get(route('wilayah.index'))
            ->assertForbidden();

        $this->actingAs($this->adminPd())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('wilayah.index'))
            ->assertDontSee(route('area.index'));
    }

    public function test_admin_pc_cannot_access_wilayah(): void
    {
        $this->actingAs($this->adminPc())
            ->get(route('wilayah.index'))
            ->assertForbidden();
    }

    public function test_admin_pp_can_view_area_index(): void
    {
        $this->actingAs($this->adminPp())
            ->get(route('area.index'))
            ->assertOk()
            ->assertSee('Pengurus Pusat')
            ->assertSee('Pengurus Daerah')
            ->assertSee('Pengurus Cabang');
    }

    public function test_admin_pd_cannot_access_area(): void
    {
        $this->actingAs($this->adminPd())
            ->get(route('area.index'))
            ->assertForbidden();
    }

    public function test_datatables_search_filters_wilayah(): void
    {
        $response = $this->actingAs($this->adminPp())
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('wilayah.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'search' => ['value' => 'Tangerang'],
                'columns' => [
                    ['data' => 'kode', 'name' => 'kode', 'searchable' => 'true', 'orderable' => 'true'],
                    ['data' => 'nama', 'name' => 'nama', 'searchable' => 'true', 'orderable' => 'true'],
                    ['data' => 'tingkat', 'name' => 'tingkat', 'searchable' => 'false', 'orderable' => 'false'],
                    ['data' => 'actions', 'name' => 'actions', 'searchable' => 'false', 'orderable' => 'false'],
                ],
            ]));

        $response->assertOk();
        $this->assertLessThan($response->json('recordsTotal'), $response->json('recordsFiltered'));
        $this->assertGreaterThanOrEqual(1, $response->json('recordsFiltered'));
        $this->assertStringContainsString('Tangerang', json_encode($response->json('data')));
    }

    public function test_admin_pp_can_create_update_and_delete_wilayah(): void
    {
        $this->actingAs($this->adminPp())
            ->post(route('wilayah.store'), [
                'kode' => '99',
                'nama' => 'Wilayah Uji',
            ])
            ->assertRedirect(route('wilayah.index'));

        $this->assertDatabaseHas('wilayah', [
            'kode' => '99',
            'nama' => 'Wilayah Uji',
        ]);

        $this->actingAs($this->adminPp())
            ->put(route('wilayah.update', '99'), [
                'nama' => 'Wilayah Uji Updated',
            ])
            ->assertRedirect(route('wilayah.index'));

        $this->assertDatabaseHas('wilayah', [
            'kode' => '99',
            'nama' => 'Wilayah Uji Updated',
        ]);

        $this->actingAs($this->adminPp())
            ->get(route('wilayah.show', '99'))
            ->assertOk()
            ->assertSee('Wilayah Uji Updated')
            ->assertSee('Provinsi');

        $this->actingAs($this->adminPp())
            ->delete(route('wilayah.destroy', '99'))
            ->assertRedirect(route('wilayah.index'));

        $this->assertDatabaseMissing('wilayah', [
            'kode' => '99',
        ]);
    }

    public function test_tingkat_filter_limits_results(): void
    {
        $response = $this->actingAs($this->adminPp())
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('wilayah.index', [
                'draw' => 1,
                'start' => 0,
                'length' => 25,
                'tingkat' => Wilayah::TINGKAT_PROVINSI,
            ]));

        $response->assertOk();

        foreach ($response->json('data') as $row) {
            $this->assertSame('Provinsi', $row['tingkat']);
        }
    }

    private function adminPp(): User
    {
        return User::where('email', 'admin@example.com')->firstOrFail();
    }

    private function adminPd(): User
    {
        return User::where('email', 'admin.pd@example.com')->firstOrFail();
    }

    private function adminPc(): User
    {
        return User::where('email', 'admin.pc@example.com')->firstOrFail();
    }

    private function superAdmin(): User
    {
        return User::where('email', 'superadmin@example.com')->firstOrFail();
    }
}
