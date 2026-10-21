<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KetuaVerifikasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_ketua_pc_can_verify_members_in_own_city(): void
    {
        $anggota = $this->makeAnggota('36', '36.71', Anggota::STATUS_MENUNGGU_VERIFIKASI_PC);
        $outside = $this->makeAnggota('32', '32.73', Anggota::STATUS_MENUNGGU_VERIFIKASI_PC);
        $ketua = $this->makePengurus('ketua-pc', '36', '36.71');
        $sekretaris = $this->makePengurus('sekretaris-pc', '36', '36.71');

        $this->actingAs($ketua)
            ->get(route('anggota.show', $anggota))
            ->assertOk()
            ->assertSee('Verifikasi PC');

        $this->actingAs($sekretaris)
            ->get(route('anggota.show', $anggota))
            ->assertOk()
            ->assertDontSee('Verifikasi PC');

        $this->actingAs($sekretaris)
            ->post(route('anggota.verify-pc', $anggota), ['alasan' => 'Tidak berwenang.'])
            ->assertForbidden();

        $this->actingAs($ketua)
            ->post(route('anggota.verify-pc', $anggota), ['alasan' => 'Lengkap.'])
            ->assertRedirect();

        $this->assertSame(Anggota::STATUS_MENUNGGU_VALIDASI_PD, $anggota->fresh()->status);

        $this->actingAs($ketua)
            ->post(route('anggota.verify-pc', $outside), ['alasan' => 'Bukan wilayah.'])
            ->assertForbidden();
    }

    public function test_ketua_pd_and_ketua_umum_process_their_own_stage(): void
    {
        $anggota = $this->makeAnggota('36', '36.71', Anggota::STATUS_MENUNGGU_VALIDASI_PD);

        $this->actingAs($this->makePengurus('ketua-pd', '36', null))
            ->post(route('anggota.validate-pd', $anggota), ['alasan' => 'Valid.'])
            ->assertRedirect();

        $this->assertSame(Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP, $anggota->fresh()->status);

        $this->actingAs($this->makePengurus('ketua-umum-pp', null, null))
            ->post(route('anggota.approve-pp', $anggota), ['alasan' => 'Disetujui.'])
            ->assertRedirect();

        $this->assertSame(Anggota::STATUS_AKTIF, $anggota->fresh()->status);
    }

    private function makePengurus(string $slug, ?string $pd, ?string $pc): User
    {
        $user = User::factory()->create([
            'pd_kode' => $pd,
            'pc_kode' => $pc,
        ]);
        $user->assignRole($slug);

        return $user;
    }

    private function makeAnggota(string $pd, string $pc, string $status): Anggota
    {
        $user = User::factory()->create();
        $user->assignRole(Role::ANGGOTA);

        return Anggota::query()->create([
            'user_id' => $user->id,
            'nik' => fake()->unique()->numerify('3201############'),
            'nama' => 'Calon Anggota',
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
            'status' => $status,
        ]);
    }
}
