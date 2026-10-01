<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\LinkInformasi;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\LinkInformasiPermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LinkInformasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_table_uses_prgb_prefix(): void
    {
        $this->assertTrue(Schema::hasTable('link_informasi'));

        $tables = collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table'"))
            ->pluck('name');

        $this->assertTrue($tables->contains('prgb_link_informasi'));
        $this->assertFalse($tables->contains('link_informasi'));
    }

    public function test_permissions_are_assigned_to_national_admins_only(): void
    {
        foreach (LinkInformasiPermissionSeeder::NAMES as $name) {
            $this->assertTrue(Permission::query()->where('name', $name)->exists(), $name);
        }

        $adminPp = Role::query()->where('slug', Role::ADMIN_PP)->firstOrFail();
        $superAdmin = Role::query()->where('slug', Role::SUPER_ADMIN)->firstOrFail();
        $adminPd = Role::query()->where('slug', Role::ADMIN_PD)->firstOrFail();

        foreach (LinkInformasiPermissionSeeder::NAMES as $name) {
            $this->assertTrue($adminPp->permissions()->where('name', $name)->exists(), 'Admin PP missing '.$name);
            $this->assertTrue($superAdmin->permissions()->where('name', $name)->exists(), 'Super Admin missing '.$name);
            $this->assertFalse($adminPd->permissions()->where('name', $name)->exists(), 'Admin PD should not have '.$name);
        }
    }

    public function test_admin_pp_can_crud_link_informasi(): void
    {
        $admin = $this->adminPp();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Link Informasi')
            ->assertSee(route('link-informasi.index'));

        $this->actingAs($admin)
            ->get(route('link-informasi.index'))
            ->assertOk()
            ->assertSee('Belum ada link informasi');

        $this->actingAs($admin)
            ->get(route('link-informasi.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('link-informasi.store'), [
                'nama' => 'Kalender Kegiatan',
                'url' => 'pergabi.net/kalender',
                'keterangan' => 'Jadwal resmi',
                'status' => '1',
            ])
            ->assertRedirect(route('link-informasi.index'));

        $link = LinkInformasi::query()->where('nama', 'Kalender Kegiatan')->first();
        $this->assertNotNull($link);
        $this->assertSame('https://pergabi.net/kalender', $link->url);
        $this->assertSame(1, $link->status);

        $this->actingAs($admin)
            ->get(route('link-informasi.show', $link))
            ->assertOk()
            ->assertSee('Kalender Kegiatan')
            ->assertSee('https://pergabi.net/kalender');

        $this->actingAs($admin)
            ->put(route('link-informasi.update', $link), [
                'nama' => 'Panduan Anggota',
                'url' => 'https://pergabi.net/panduan',
                'keterangan' => '',
                'status' => '0',
            ])
            ->assertRedirect(route('link-informasi.index'));

        $link->refresh();
        $this->assertSame('Panduan Anggota', $link->nama);
        $this->assertSame(0, $link->status);
        $this->assertNull($link->keterangan);

        $this->actingAs($admin)
            ->delete(route('link-informasi.destroy', $link))
            ->assertRedirect(route('link-informasi.index'));

        $this->assertDatabaseMissing('link_informasi', ['id' => $link->id]);
    }

    public function test_admin_pd_cannot_access_link_informasi(): void
    {
        $this->actingAs($this->adminPd())
            ->get(route('link-informasi.index'))
            ->assertForbidden();

        $this->actingAs($this->adminPd())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('link-informasi.index'));
    }

    public function test_member_cannot_open_admin_link_informasi(): void
    {
        $this->actingAs($this->makeMember())
            ->get(route('link-informasi.index'))
            ->assertForbidden();
    }

    public function test_portal_hides_informasi_menu_when_no_active_link(): void
    {
        LinkInformasi::factory()->nonaktif()->create([
            'nama' => 'Draft Internal',
            'url' => 'https://example.com/draft',
        ]);

        $this->actingAs($this->makeMember())
            ->get(route('portal.show'))
            ->assertOk()
            ->assertDontSee('Informasi')
            ->assertDontSee('Draft Internal');
    }

    public function test_portal_shows_informasi_submenu_for_active_links(): void
    {
        LinkInformasi::factory()->create([
            'nama' => 'Kalender Nasional',
            'url' => 'https://pergabi.net/kalender',
        ]);
        LinkInformasi::factory()->create([
            'nama' => 'Formulir Aduan',
            'url' => 'https://pergabi.net/aduan',
        ]);
        LinkInformasi::factory()->nonaktif()->create([
            'nama' => 'Link Nonaktif',
            'url' => 'https://pergabi.net/rahasia',
        ]);

        $this->actingAs($this->makeMember())
            ->get(route('portal.show'))
            ->assertOk()
            ->assertSee('Informasi')
            ->assertSee('Kalender Nasional')
            ->assertSee('Formulir Aduan')
            ->assertSee('https://pergabi.net/kalender')
            ->assertSee('https://pergabi.net/aduan')
            ->assertDontSee('Link Nonaktif')
            ->assertDontSee('https://pergabi.net/rahasia');
    }

    private function adminPp(): User
    {
        return User::query()->where('email', 'admin@example.com')->firstOrFail();
    }

    private function adminPd(): User
    {
        return User::query()->where('email', 'admin.pd@example.com')->firstOrFail();
    }

    private function makeMember(): User
    {
        $user = User::factory()->create([
            'name' => 'Guru Portal',
            'email' => 'guru.link@example.com',
            'email_verified_at' => now(),
        ]);
        $user->assignRole(Role::ANGGOTA);

        Anggota::query()->create([
            'user_id' => $user->id,
            'nik' => '3201010101010077',
            'nama' => 'Guru Portal',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Tangerang',
            'tanggal_lahir' => '1990-01-15',
            'agama' => 'Buddha',
            'status_perkawinan' => 'Kawin',
            'hp' => '081234567890',
            'whatsapp' => '081234567890',
            'email' => $user->email,
            'alamat' => 'Jl. Melati No. 1',
            'provinsi_kode' => '36',
            'kabupaten_kode' => '36.71',
            'kecamatan_kode' => '36.71.01',
            'kelurahan_kode' => '36.71.01.1001',
            'kode_pos' => '15111',
            'status_guru' => 'ASN',
            'jenjang' => 'SMA',
            'nama_sekolah' => 'SMA Negeri 1',
            'status_sekolah' => 'Negeri',
            'alamat_sekolah' => 'Jl. Sekolah No. 2',
            'pd_kode' => '36',
            'pc_kode' => '36.71',
            'status' => Anggota::STATUS_MENUNGGU_VERIFIKASI_PC,
        ]);

        return $user->fresh(['anggota', 'roles']);
    }
}
