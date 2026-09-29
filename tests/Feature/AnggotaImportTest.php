<?php

namespace Tests\Feature;

use App\Exports\AnggotaImportTemplateExport;
use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\NomorAnggotaService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\WilayahSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AnggotaImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(WilayahSeeder::class);
    }

    public function test_nta_parser_reads_province_and_regency_from_number(): void
    {
        $parser = app(NomorAnggotaService::class);

        $this->assertSame([
            'nomor' => '2024.52.5208.002',
            'tahun' => '2024',
            'provinsi_kode' => '52',
            'kabupaten_kode' => '52.08',
            'urut' => '002',
        ], $parser->parse('2024.52.5208.002'));

        $this->assertSame('36.71', $parser->parse('2026.36.3671.001')['kabupaten_kode']);
        $this->assertSame('2024.52.5208.002', $parser->parse('2024.52.08.002')['nomor']);
        $this->assertNull($parser->parse('nomor-salah'));
    }

    public function test_guests_are_redirected_from_import(): void
    {
        $this->get(route('anggota.import'))->assertRedirect(route('login'));
        $this->get(route('anggota.import.template'))->assertRedirect(route('login'));
    }

    public function test_member_cannot_import(): void
    {
        $anggota = User::query()->where('email', 'anggota@example.com')->firstOrFail();

        $this->actingAs($anggota)->get(route('anggota.import'))->assertForbidden();
        $this->actingAs($anggota)->get(route('anggota.import.template'))->assertForbidden();
    }

    public function test_admin_can_download_template(): void
    {
        $this->actingAs($this->adminPp())
            ->get(route('anggota.import.template'))
            ->assertOk()
            ->assertDownload('template-import-anggota.xlsx');
    }

    public function test_admin_sees_import_form_and_index_button(): void
    {
        $this->actingAs($this->adminPp())
            ->get(route('anggota.index'))
            ->assertOk()
            ->assertSee(route('anggota.import'))
            ->assertSee('Import Excel');

        $this->actingAs($this->adminPp())
            ->get(route('anggota.import'))
            ->assertOk()
            ->assertSee('Akun langsung aktif')
            ->assertSee('terverifikasi seluruh jenjang')
            ->assertSee('Unduh template Excel')
            ->assertDontSee('TANGGAL LAHIR');
    }

    public function test_import_ignores_petunjuk_sheet_from_template(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'template-import-anggota.xlsx',
            Excel::raw(new AnggotaImportTemplateExport, \Maatwebsite\Excel\Excel::XLSX),
        );

        $this->actingAs($this->adminPp())
            ->post(route('anggota.import.store'), [
                'file' => $file,
                'akun_aktif' => '1',
                'terverifikasi' => '1',
            ])
            ->assertRedirect(route('anggota.import'));

        $hasil = session('import_hasil');
        $this->assertSame(2, $hasil['imported']);
        $this->assertSame(0, $hasil['failed']);
        $this->assertTrue(Anggota::query()->where('email', 'sinta.contoh@example.com')->exists());
        $this->assertTrue(Anggota::query()->where('email', 'budi.contoh@example.com')->exists());
        $this->assertFalse(Anggota::query()->where('email', 'wajib. nomor anggota lama')->exists());
    }

    public function test_admin_can_import_excel_and_map_wilayah_from_nta(): void
    {
        $file = $this->excelFile([
            [
                '2026.36.3671.001',
                'Nopiyanti, S.Pd',
                '085338415653',
                'nopiyanti@example.com',
                'Batu Lawang',
                'Perempuan',
                'Dusun Median, Lombok Utara',
            ],
        ]);

        $response = $this->actingAs($this->adminPp())
            ->from(route('anggota.import'))
            ->post(route('anggota.import.store'), [
                'file' => $file,
                'akun_aktif' => '1',
                'terverifikasi' => '1',
            ]);

        $response->assertRedirect(route('anggota.import'))
            ->assertSessionHas('status');

        $hasil = session('import_hasil');
        $this->assertSame(1, $hasil['imported']);
        $this->assertSame(0, $hasil['failed']);
        $this->assertNotEmpty($hasil['rows'][0]['password']);

        $anggota = Anggota::query()->where('nomor_anggota', '2026.36.3671.001')->first();
        $this->assertNotNull($anggota);
        $this->assertSame('Nopiyanti', $anggota->nama);
        $this->assertSame('S.Pd', $anggota->gelar_belakang);
        $this->assertSame('P', $anggota->jenis_kelamin);
        $this->assertNull($anggota->tanggal_lahir);
        $this->assertSame(now()->toDateString(), $anggota->tanggal_bergabung?->toDateString());
        $this->assertSame(now()->addYears(5)->toDateString(), $anggota->masa_berlaku_hingga?->toDateString());
        $this->assertSame('36', $anggota->pd_kode);
        $this->assertSame('36.71', $anggota->pc_kode);
        $this->assertSame('36', $anggota->provinsi_kode);
        $this->assertSame('36.71', $anggota->kabupaten_kode);
        $this->assertSame(Anggota::STATUS_AKTIF, $anggota->status);
        $this->assertNotNull($anggota->masa_berlaku_hingga);
        $this->assertNotNull($anggota->user?->email_verified_at);
        $this->assertTrue($anggota->user->hasRole(Role::ANGGOTA));
        $this->assertTrue(Hash::check($hasil['rows'][0]['password'], $anggota->user->password));
    }

    public function test_import_uses_default_hp_when_excel_cell_empty(): void
    {
        $file = $this->excelFile([
            [
                '2026.36.3671.009',
                'Hanuradi',
                '',
                'hanuradi@example.com',
                'Ngestikarya',
                'L',
                'Jl. Sunter Kemayoran',
            ],
        ]);

        $this->actingAs($this->adminPp())
            ->post(route('anggota.import.store'), [
                'file' => $file,
                'akun_aktif' => '1',
                'terverifikasi' => '1',
            ])
            ->assertRedirect(route('anggota.import'));

        $hasil = session('import_hasil');
        $this->assertSame(1, $hasil['imported']);
        $this->assertSame(0, $hasil['failed']);

        $anggota = Anggota::query()->where('email', 'hanuradi@example.com')->first();
        $this->assertNotNull($anggota);
        $this->assertSame('081', $anggota->hp);
        $this->assertSame('081', $anggota->whatsapp);
    }

    public function test_import_reads_ntb_regency_code_from_nta(): void
    {
        Wilayah::query()->insert([
            ['kode' => '52', 'nama' => 'Nusa Tenggara Barat'],
            ['kode' => '52.08', 'nama' => 'Kabupaten Lombok Utara'],
            ['kode' => '52.08.01', 'nama' => 'Tanjung'],
            ['kode' => '52.08.01.2001', 'nama' => 'Tanjung'],
        ]);

        $file = $this->excelFile([
            [
                '2024.52.5208.002',
                'Amirudin S.Ag',
                '085205730561',
                'amirudin@example.com',
                'Lombok Barat',
                'Laki-laki',
                'Raya tendaan dusun tendaan desa mareje',
            ],
        ]);

        $this->actingAs($this->adminPp())
            ->post(route('anggota.import.store'), [
                'file' => $file,
                'akun_aktif' => '1',
                'terverifikasi' => '1',
            ])
            ->assertRedirect(route('anggota.import'));

        $anggota = Anggota::query()->where('nomor_anggota', '2024.52.5208.002')->first();
        $this->assertNotNull($anggota);
        $this->assertSame('52', $anggota->pd_kode);
        $this->assertSame('52.08', $anggota->pc_kode);
        $this->assertSame('Amirudin S.Ag', $anggota->nama);
    }

    public function test_import_can_leave_account_unverified_and_pending_pc(): void
    {
        $file = $this->excelFile([
            [
                '2026.36.3671.003',
                'Guru Pending',
                '081234567890',
                'pending@example.com',
                'Tangerang',
                'L',
                'Jl. Melati',
            ],
        ]);

        $this->actingAs($this->adminPp())
            ->post(route('anggota.import.store'), [
                'file' => $file,
                'akun_aktif' => '0',
                'terverifikasi' => '0',
            ])
            ->assertRedirect();

        $anggota = Anggota::query()->where('email', 'pending@example.com')->first();
        $this->assertNotNull($anggota);
        $this->assertSame(Anggota::STATUS_MENUNGGU_VERIFIKASI_PC, $anggota->status);
        $this->assertSame('2026.36.3671.003', $anggota->nomor_anggota);
        $this->assertSame(now()->toDateString(), $anggota->tanggal_bergabung?->toDateString());
        $this->assertSame(now()->addYears(5)->toDateString(), $anggota->masa_berlaku_hingga?->toDateString());
        $this->assertNull($anggota->user?->email_verified_at);
    }

    public function test_invalid_nta_is_reported_and_does_not_create_member(): void
    {
        $file = $this->excelFile([
            [
                'NTA-SALAH',
                'Guru Salah',
                '081234567890',
                'salah@example.com',
                'Tangerang',
                'L',
                'Jl. Melati',
            ],
        ]);

        $this->actingAs($this->adminPp())
            ->post(route('anggota.import.store'), [
                'file' => $file,
                'akun_aktif' => '1',
                'terverifikasi' => '1',
            ])
            ->assertRedirect(route('anggota.import'));

        $hasil = session('import_hasil');
        $this->assertSame(0, $hasil['imported']);
        $this->assertSame(1, $hasil['failed']);
        $this->assertFalse(Anggota::query()->where('email', 'salah@example.com')->exists());
    }

    public function test_admin_pc_cannot_import_member_outside_city(): void
    {
        Wilayah::query()->insert([
            ['kode' => '52', 'nama' => 'Nusa Tenggara Barat'],
            ['kode' => '52.08', 'nama' => 'Kabupaten Lombok Utara'],
        ]);

        $file = $this->excelFile([
            [
                '2024.52.5208.002',
                'Guru Luar',
                '081234567890',
                'luar@example.com',
                'Lombok',
                'L',
                'Alamat',
            ],
        ]);

        $this->actingAs($this->adminPc())
            ->post(route('anggota.import.store'), [
                'file' => $file,
                'akun_aktif' => '1',
                'terverifikasi' => '1',
            ])
            ->assertRedirect();

        $this->assertSame(0, session('import_hasil')['imported']);
        $this->assertFalse(Anggota::query()->where('email', 'luar@example.com')->exists());
    }

    public function test_next_kta_sequence_continues_from_imported_numbers(): void
    {
        $imported = $this->makeAnggota([
            'nomor_anggota' => '2024.36.3671.015',
            'pd_kode' => '36',
            'pc_kode' => '36.71',
            'provinsi_kode' => '36',
            'kabupaten_kode' => '36.71',
            'status' => Anggota::STATUS_AKTIF,
        ]);

        $berikutnya = $this->makeAnggota([
            'nomor_anggota' => null,
            'email' => 'baru@example.com',
            'nik' => '3671010101010099',
            'pd_kode' => '36',
            'pc_kode' => '36.71',
            'provinsi_kode' => '36',
            'kabupaten_kode' => '36.71',
            'status' => Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP,
        ]);

        $nomor = app(NomorAnggotaService::class)->issue($berikutnya);

        $this->assertSame(now()->year.'.36.3671.016', $nomor);
        $this->assertSame('2024.36.3671.015', $imported->nomor_anggota);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function excelFile(array $rows): UploadedFile
    {
        $export = new class($rows) implements FromArray, WithHeadings
        {
            /**
             * @param  list<list<string>>  $rows
             */
            public function __construct(private array $rows) {}

            /**
             * @return list<string>
             */
            public function headings(): array
            {
                return ['NTA', 'NAMA', 'HP', 'EMAIL', 'TEMPAT LAHIR', 'JL', 'ALAMAT'];
            }

            /**
             * @return list<list<string>>
             */
            public function array(): array
            {
                return $this->rows;
            }
        };

        return UploadedFile::fake()->createWithContent(
            'anggota.xlsx',
            Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX),
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeAnggota(array $overrides = []): Anggota
    {
        $user = User::factory()->create([
            'email' => $overrides['email'] ?? fake()->unique()->safeEmail(),
        ]);
        $user->assignRole(Role::ANGGOTA);
        unset($overrides['email']);

        return Anggota::query()->create(array_merge([
            'user_id' => $user->id,
            'nik' => fake()->unique()->numerify('3671############'),
            'nama' => $user->name,
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Tangerang',
            'tanggal_lahir' => null,
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
            'status' => Anggota::STATUS_AKTIF,
        ], $overrides));
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
