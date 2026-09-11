<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\DashboardCacheService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seedWilayah();
    }

    public function test_dashboard_caches_nasional_provinsi_and_kabupaten_separately(): void
    {
        $this->makeAnggota('36', '36.71');
        $this->makeAnggota('36', '36.72');
        $this->makeAnggota('32', '32.73');

        $this->actingAs($this->superAdmin())->get(route('dashboard'))->assertOk();
        $this->assertTrue(Cache::has(DashboardCacheService::KEY_ALL));

        $this->actingAs($this->superAdmin())->get(route('dashboard', ['provinsi' => '36']))->assertOk();
        $this->assertTrue(Cache::has('dashboard:provinsi:36'));

        $this->actingAs($this->superAdmin())->get(route('dashboard', ['provinsi' => '36', 'kabupaten' => '36.71']))->assertOk();
        $this->assertTrue(Cache::has('dashboard:kabupaten:36.71'));

        $this->assertNotSame(
            Cache::get(DashboardCacheService::KEY_ALL)['totalAnggota'],
            Cache::get('dashboard:provinsi:36')['totalAnggota'],
        );
        $this->assertSame(3, Cache::get(DashboardCacheService::KEY_ALL)['totalAnggota']);
        $this->assertSame(2, Cache::get('dashboard:provinsi:36')['totalAnggota']);
        $this->assertSame(1, Cache::get('dashboard:kabupaten:36.71')['totalAnggota']);
    }

    public function test_admin_pc_uses_kabupaten_cache_not_nasional(): void
    {
        $this->makeAnggota('36', '36.71');

        $this->actingAs($this->adminPc())->get(route('dashboard'))->assertOk();

        $this->assertTrue(Cache::has('dashboard:kabupaten:36.71'));
        $this->assertFalse(Cache::has(DashboardCacheService::KEY_ALL));
        $this->assertSame(1, Cache::get('dashboard:kabupaten:36.71')['totalAnggota']);
    }

    public function test_admin_pd_uses_provinsi_cache(): void
    {
        $this->makeAnggota('36', '36.71');
        $this->makeAnggota('32', '32.73');

        $this->actingAs($this->adminPd())->get(route('dashboard'))->assertOk();

        $this->assertTrue(Cache::has('dashboard:provinsi:36'));
        $this->assertSame(1, Cache::get('dashboard:provinsi:36')['totalAnggota']);
    }

    public function test_new_anggota_invalidates_related_dashboard_cache(): void
    {
        $this->makeAnggota('36', '36.71');
        $this->actingAs($this->superAdmin())->get(route('dashboard'))->assertOk();
        $this->assertSame(1, Cache::get(DashboardCacheService::KEY_ALL)['totalAnggota']);

        $this->makeAnggota('36', '36.71');

        $this->assertFalse(Cache::has(DashboardCacheService::KEY_ALL));
        $this->assertFalse(Cache::has('dashboard:provinsi:36'));
        $this->assertFalse(Cache::has('dashboard:kabupaten:36.71'));

        $this->actingAs($this->superAdmin())->get(route('dashboard'))->assertOk();
        $this->assertSame(2, Cache::get(DashboardCacheService::KEY_ALL)['totalAnggota']);
    }

    public function test_super_admin_can_clear_key_prefix_and_all(): void
    {
        $this->makeAnggota('36', '36.71');
        $this->actingAs($this->superAdmin())->get(route('dashboard'))->assertOk();
        $this->actingAs($this->superAdmin())->get(route('dashboard', ['provinsi' => '36']))->assertOk();
        $this->actingAs($this->superAdmin())->get(route('dashboard', ['provinsi' => '36', 'kabupaten' => '36.71']))->assertOk();

        $this->actingAs($this->superAdmin())
            ->get(route('cache.index'))
            ->assertOk()
            ->assertSee('dashboard:all')
            ->assertSee('dashboard:provinsi:36')
            ->assertSee('dashboard:kabupaten:36.71')
            ->assertSee('Clear all')
            ->assertSee('Clear per prefix');

        $this->actingAs($this->superAdmin())
            ->from(route('cache.index'))
            ->post(route('cache.destroy'), ['key' => 'dashboard:all'])
            ->assertRedirect();
        $this->assertFalse(Cache::has(DashboardCacheService::KEY_ALL));
        $this->assertTrue(Cache::has('dashboard:provinsi:36'));

        $this->actingAs($this->superAdmin())
            ->from(route('cache.index'))
            ->post(route('cache.destroy-prefix'), ['prefix' => 'dashboard:provinsi'])
            ->assertRedirect();
        $this->assertFalse(Cache::has('dashboard:provinsi:36'));
        $this->assertTrue(Cache::has('dashboard:kabupaten:36.71'));

        $this->actingAs($this->superAdmin())
            ->from(route('cache.index'))
            ->post(route('cache.flush'))
            ->assertRedirect();
        $this->assertFalse(Cache::has('dashboard:kabupaten:36.71'));
    }

    public function test_admin_pp_cannot_open_cache_menu(): void
    {
        $this->actingAs($this->adminPp())
            ->get(route('cache.index'))
            ->assertForbidden();

        $this->actingAs($this->adminPp())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('cache.index'));
    }

    private function makeAnggota(string $pd, string $pc): Anggota
    {
        $user = User::factory()->create();
        $user->assignRole(Role::ANGGOTA);

        return Anggota::query()->create([
            'user_id' => $user->id,
            'nik' => fake()->unique()->numerify('3201############'),
            'nama' => $user->name,
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Tangerang',
            'tanggal_lahir' => '1990-01-15',
            'agama' => 'Buddha',
            'status_perkawinan' => 'Kawin',
            'hp' => '081234567890',
            'whatsapp' => '081234567890',
            'email' => $user->email,
            'alamat' => 'Jl. Melati No. 1',
            'provinsi_kode' => $pd,
            'kabupaten_kode' => $pc,
            'kecamatan_kode' => $pc.'.01',
            'kelurahan_kode' => $pc.'.01.1001',
            'kode_pos' => '15111',
            'status_guru' => 'ASN',
            'jenjang' => 'SMA',
            'nama_sekolah' => 'SMA Negeri 1',
            'status_sekolah' => 'Negeri',
            'alamat_sekolah' => 'Jl. Sekolah No. 2',
            'pd_kode' => $pd,
            'pc_kode' => $pc,
            'status' => Anggota::STATUS_MENUNGGU_VERIFIKASI_PC,
        ]);
    }

    private function seedWilayah(): void
    {
        Wilayah::query()->insert([
            ['kode' => '32', 'nama' => 'Jawa Barat'],
            ['kode' => '32.73', 'nama' => 'Kota Bandung'],
            ['kode' => '36', 'nama' => 'Banten'],
            ['kode' => '36.71', 'nama' => 'Kota Tangerang'],
            ['kode' => '36.72', 'nama' => 'Kota Cilegon'],
        ]);
    }

    private function superAdmin(): User
    {
        return User::query()->where('email', 'superadmin@example.com')->firstOrFail();
    }

    private function adminPp(): User
    {
        return User::query()->where('email', 'admin@example.com')->firstOrFail();
    }

    private function adminPd(): User
    {
        return User::query()->where('email', 'admin.pd@example.com')->firstOrFail();
    }

    private function adminPc(): User
    {
        return User::query()->where('email', 'admin.pc@example.com')->firstOrFail();
    }
}
