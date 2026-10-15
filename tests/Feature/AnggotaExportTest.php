<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\AnggotaDokumen;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class AnggotaExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_export_anggota_with_file_paths_and_name_filter(): void
    {
        $this->seedWilayah();
        $heni = $this->makeAnggota('36', '36.71', [
            'nama' => 'Heni Damayanti',
            'nomor_anggota' => '2026.36.3671.001',
            'foto_path' => 'anggota/heni/foto.jpg',
        ]);
        $heni->dokumen()->create([
            'jenis' => AnggotaDokumen::KTP,
            'path' => 'anggota/heni/ktp.pdf',
            'nama_asli' => 'ktp.pdf',
            'mime' => 'application/pdf',
            'ukuran' => 1000,
        ]);
        $this->makeAnggota('36', '36.71', ['nama' => 'Sugiat']);

        $this->actingAs($this->adminPp())
            ->get(route('anggota.index'))
            ->assertOk()
            ->assertSee('Export Excel')
            ->assertSee(route('anggota.export'), false);

        $rows = $this->sheetRows($this->actingAs($this->adminPp())->get(route('anggota.export', ['nama' => 'Heni'])));
        $flat = collect($rows)->flatten()->filter()->all();

        $this->assertContains('Path KTP', $rows[0]);
        $this->assertContains('Path foto', $rows[0]);
        $this->assertContains('Heni Damayanti', $flat);
        $this->assertContains('2026.36.3671.001', $flat);
        $this->assertContains('anggota/heni/ktp.pdf', $flat);
        $this->assertContains('anggota/heni/foto.jpg', $flat);
        $this->assertNotContains('Sugiat', $flat);
    }

    public function test_admin_pc_export_stays_inside_own_city(): void
    {
        $own = $this->makeAnggota('36', '36.71', ['nama' => 'Anggota Tangerang']);
        $other = $this->makeAnggota('32', '32.73', ['nama' => 'Anggota Bandung']);

        $rows = $this->sheetRows($this->actingAs($this->adminPc())->get(route('anggota.export')));
        $flat = collect($rows)->flatten()->filter()->all();

        $this->assertContains($own->nama, $flat);
        $this->assertNotContains($other->nama, $flat);
    }

    public function test_member_cannot_export_anggota(): void
    {
        $this->actingAs(User::query()->where('email', 'anggota@example.com')->firstOrFail())
            ->get(route('anggota.export'))
            ->assertForbidden();
    }

    /**
     * @return list<list<mixed>>
     */
    private function sheetRows(TestResponse $response): array
    {
        $response->assertOk()->assertDownload('anggota-'.now()->format('Y-m-d').'.xlsx');

        $path = $response->baseResponse->getFile()->getPathname();

        return IOFactory::load($path)->getActiveSheet()->toArray();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeAnggota(string $pd, string $pc, array $overrides = []): Anggota
    {
        $user = User::factory()->create();
        $user->assignRole(Role::ANGGOTA);

        return Anggota::query()->create(array_merge([
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
            'kecamatan_kode' => $pd.'.01',
            'kelurahan_kode' => $pd.'.01.1001',
            'kode_pos' => '15111',
            'status_guru' => 'ASN',
            'jenjang' => 'SMA',
            'nama_sekolah' => 'SMA Negeri 1',
            'status_sekolah' => 'Negeri',
            'alamat_sekolah' => 'Jl. Sekolah No. 2',
            'pd_kode' => $pd,
            'pc_kode' => $pc,
            'status' => Anggota::STATUS_AKTIF,
        ], $overrides));
    }

    private function seedWilayah(): void
    {
        Wilayah::query()->insert([
            ['kode' => '36', 'nama' => 'Banten'],
            ['kode' => '36.71', 'nama' => 'Kota Tangerang'],
        ]);
    }

    private function adminPp(): User
    {
        return User::query()->where('email', 'admin@example.com')->firstOrFail();
    }

    private function adminPc(): User
    {
        return User::query()->where('email', 'admin.pc@example.com')->firstOrFail();
    }
}
