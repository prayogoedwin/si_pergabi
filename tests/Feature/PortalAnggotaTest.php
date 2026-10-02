<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\AnggotaDokumen;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\WilayahSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalAnggotaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');
    }

    public function test_pengurus_login_goes_to_dashboard(): void
    {
        foreach (['superadmin@example.com', 'admin@example.com', 'admin.pd@example.com', 'admin.pc@example.com'] as $email) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'password',
            ])->assertRedirect(route('dashboard', absolute: false));

            $this->post('/logout');
        }
    }

    public function test_verified_member_is_sent_to_portal_not_dashboard(): void
    {
        $user = $this->makeMember();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('portal.show', absolute: false));

        $this->actingAs($user->fresh())
            ->get(route('dashboard'))
            ->assertRedirect(route('portal.show'));
    }

    public function test_unverified_member_cannot_open_portal(): void
    {
        $user = $this->makeMember(Anggota::STATUS_BELUM_VERIFIKASI_EMAIL, verified: false);

        $this->actingAs($user)
            ->get(route('portal.show'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_member_sees_compact_portal_without_full_kta(): void
    {
        $user = $this->makeMember();

        $this->actingAs($user)
            ->get(route('portal.show'))
            ->assertOk()
            ->assertSee('Portal anggota')
            ->assertSee('Guru Portal')
            ->assertSee('Menunggu verifikasi PC')
            ->assertSee('Tersedia setelah persetujuan PP')
            ->assertDontSee('Master')
            ->assertDontSee('User Management');

        $this->actingAs($user)
            ->get(route('portal.kta'))
            ->assertOk()
            ->assertSee('Belum dapat ditampilkan')
            ->assertDontSee('Cetak ID Card');

        $this->actingAs($user)
            ->get(route('portal.qr'))
            ->assertOk()
            ->assertSee('QR Code anggota')
            ->assertSee('Belum dapat ditampilkan')
            ->assertDontSee('data-anggota-qr', false);
    }

    public function test_full_kta_is_available_after_pp_approval(): void
    {
        $user = $this->makeMember(Anggota::STATUS_AKTIF);
        $anggota = $user->anggota;
        $anggota->statusLogs()->create([
            'status_dari' => Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP,
            'status_ke' => Anggota::STATUS_AKTIF,
            'alasan' => 'Disetujui Pengurus Pusat.',
            'user_id' => $user->id,
            'created_at' => now(),
        ]);
        $tanggalPp = $anggota->fresh()->tanggalVerifikasiLabel();

        $this->actingAs($user)
            ->get(route('portal.kta'))
            ->assertOk()
            ->assertSee('Cetak ID Card')
            ->assertSee('Cetak A4 / PDF')
            ->assertSee('Unduh PNG depan')
            ->assertSee('Unduh PNG belakang')
            ->assertSee('kta-demo-front', false)
            ->assertSee('kta-demo-back', false)
            ->assertSee('KARTU TANDA ANGGOTA')
            ->assertSee('DATA ANGGOTA')
            ->assertSee('85,60')
            ->assertSee('KTP / SIM')
            ->assertSee($anggota->namaLengkap())
            ->assertSee($anggota->nomor_anggota)
            ->assertSee($anggota->nik)
            ->assertSee($anggota->instansiLabel())
            ->assertSee($anggota->alamatLabel())
            ->assertSee($anggota->labelPdPergabi())
            ->assertSee($anggota->masaBerlakuLabel())
            ->assertSee('KTA ini berlaku s.d.')
            ->assertSee('Nama Lengkap')
            ->assertSee('Pengurus Pusat')
            ->assertDontSee('halaman-depan.png')
            ->assertDontSee('halaman-belakang.png')
            ->assertSee('verifikasi-qr-anggota', false)
            ->assertSee('kode=', false)
            ->assertSee('data-kta-qr', false)
            ->assertSee('data-kta-ttd-qr', false)
            ->assertDontSee('kta-stempel-belakang', false)
            ->assertSee('Telah disetujui dan disahkan oleh Ketua Umum PP Pergabi pada tanggal '.$tanggalPp, false)
            ->assertSee('Telah disetujui oleh Sekretaris Jenderal PP Pergabi pada tanggal '.$tanggalPp, false)
            ->assertSee('js/pergabi-qr.js', false)
            ->assertSee('js/html-to-image.js', false);

        $this->actingAs($user)
            ->get(route('portal.qr'))
            ->assertOk()
            ->assertSee('data-anggota-qr', false)
            ->assertSee('/verifikasi-qr-anggota/?kode=', false)
            ->assertSee($anggota->nomor_anggota);

        $this->get($anggota->urlVerifikasiQr())
            ->assertOk()
            ->assertSee('Kartu ini valid')
            ->assertSee($anggota->namaLengkap())
            ->assertSee('Aktif')
            ->assertSee($anggota->nomor_anggota)
            ->assertSee($anggota->nik)
            ->assertSee($anggota->ttlLabel())
            ->assertSee($anggota->instansiLabel())
            ->assertSee($anggota->alamatLabel())
            ->assertSee($anggota->labelPdPergabi())
            ->assertSee('NB:');
    }

    public function test_public_verification_shows_inactive_status(): void
    {
        $user = $this->makeMember(Anggota::STATUS_AKTIF);
        $user->anggota->update(['status' => Anggota::STATUS_TIDAK_AKTIF]);

        $this->get($user->anggota->urlVerifikasiQr())
            ->assertOk()
            ->assertSee('Kartu tidak valid')
            ->assertSee('Tidak aktif');
    }

    public function test_pengurus_is_redirected_away_from_portal(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('portal.show'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_member_can_update_password_from_portal(): void
    {
        $user = $this->makeMember();

        $this->actingAs($user)
            ->put(route('portal.password'), [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('portal.profil', ['tab' => 'akun']));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_member_can_update_photo_from_portal(): void
    {
        $user = $this->makeMember();

        $this->actingAs($user)
            ->post(route('portal.foto.update'), [
                'foto' => UploadedFile::fake()->image('baru.jpg'),
            ])
            ->assertRedirect();

        $this->assertNotNull($user->fresh()->anggota?->foto_path);
        Storage::disk('local')->assertExists($user->fresh()->anggota->foto_path);
    }

    public function test_member_can_open_profile_tabs(): void
    {
        $user = $this->makeMember();

        $this->actingAs($user)
            ->get(route('portal.profil'))
            ->assertOk()
            ->assertSee('Data identitas')
            ->assertSee('Kontak & alamat')
            ->assertSee('Data profesi')
            ->assertSee('Dokumen')
            ->assertSee('Akun')
            ->assertSee('Simpan data')
            ->assertSee('Ganti password')
            ->assertSee('Data keanggotaan')
            ->assertSee($user->anggota->nik)
            ->assertSee('Pas foto')
            ->assertSee('SK Mengajar')
            ->assertSee('Bukti pembayaran')
            ->assertDontSee('Ijazah')
            ->assertDontSee('Sertifikat Pendidik')
            ->assertSee('TK/PAUD')
            ->assertSee('Dhammasekha TK/PAUD')
            ->assertSee('Dhammasekha SD')
            ->assertSee('Dhammasekha SMP')
            ->assertSee('Dhammasekha SMA');
    }

    public function test_member_can_update_profile_data_and_documents(): void
    {
        $this->seed(WilayahSeeder::class);
        $user = $this->makeMember();

        $this->actingAs($user)
            ->put(route('portal.profil.update'), $this->profilePayload($user, [
                'nama' => 'Guru Diperbarui',
                'alamat' => 'Jl. Kenanga No. 9',
                'nama_sekolah' => 'SMA Negeri 3',
                'pas_foto' => UploadedFile::fake()->image('baru.jpg'),
                'sk_mengajar' => UploadedFile::fake()->create('sk-baru.pdf', 80, 'application/pdf'),
            ]))
            ->assertRedirect(route('portal.profil', ['tab' => 'identitas']));

        $anggota = $user->fresh()->anggota;
        $this->assertSame('Guru Diperbarui', $anggota->nama);
        $this->assertSame('Guru Diperbarui', $user->fresh()->name);
        $this->assertSame('Jl. Kenanga No. 9', $anggota->alamat);
        $this->assertSame('SMA Negeri 3', $anggota->nama_sekolah);
        $this->assertSame('36', $anggota->pd_kode);
        $this->assertSame('36.71', $anggota->pc_kode);
        $this->assertNotNull($anggota->foto_path);
        $this->assertNotNull($anggota->dokumenTerbaru(AnggotaDokumen::PAS_FOTO));
        $this->assertNotNull($anggota->dokumenTerbaru(AnggotaDokumen::SK_MENGAJAR));
        Storage::disk('local')->assertExists($anggota->foto_path);
    }

    public function test_member_profile_keeps_existing_documents_when_files_omitted(): void
    {
        $this->seed(WilayahSeeder::class);
        $user = $this->makeMember();
        $path = 'anggota/'.$user->anggota->id.'/lama.jpg';
        Storage::disk('local')->put($path, 'foto');
        $user->anggota->update(['foto_path' => $path, 'nama' => 'Guru Portal']);
        $user->anggota->dokumen()->create([
            'jenis' => AnggotaDokumen::PAS_FOTO,
            'path' => $path,
            'nama_asli' => 'lama.jpg',
            'mime' => 'image/jpeg',
            'ukuran' => 4,
        ]);

        $this->actingAs($user)
            ->put(route('portal.profil.update'), $this->profilePayload($user, [
                'nama' => 'Guru Tanpa Ganti Foto',
            ]))
            ->assertRedirect();

        $anggota = $user->fresh()->anggota;
        $this->assertSame('Guru Tanpa Ganti Foto', $anggota->nama);
        $this->assertSame($path, $anggota->foto_path);
        $this->assertSame(1, $anggota->dokumen()->where('jenis', AnggotaDokumen::PAS_FOTO)->count());
    }

    public function test_portal_shows_rekening_and_upload_control_above_progres(): void
    {
        $user = $this->makeMember();

        $this->actingAs($user)
            ->get(route('portal.show'))
            ->assertOk()
            ->assertSee('Uang pendaftaran ditransfer ke Rekening:')
            ->assertSee('BANK BRI')
            ->assertSee('0418 0100 0920 300')
            ->assertSee('Perkumpulan Guru Agama Buddha Indonesia (PD PERGABI)')
            ->assertSee('Simpan bukti transfer untuk diunggah di formulir pendaftaran.')
            ->assertSee('Unggah bukti pembayaran')
            ->assertSee('Progres verifikasi');
    }

    public function test_member_can_upload_and_view_bukti_pembayaran(): void
    {
        $user = $this->makeMember();

        $this->actingAs($user)
            ->post(route('portal.bukti-pembayaran'), [
                'bukti_pembayaran' => UploadedFile::fake()->create('transfer.pdf', 80, 'application/pdf'),
            ])
            ->assertRedirect(route('portal.show'));

        $bukti = $user->fresh()->anggota->dokumenTerbaru(AnggotaDokumen::BUKTI_PEMBAYARAN);
        $this->assertNotNull($bukti);
        $this->assertSame('transfer.pdf', $bukti->nama_asli);
        Storage::disk('local')->assertExists($bukti->path);

        $this->actingAs($user)
            ->get(route('portal.show'))
            ->assertOk()
            ->assertSee('transfer.pdf')
            ->assertSee('Unggah ulang bukti pembayaran');

        $this->actingAs($user)
            ->get(route('portal.profil', ['tab' => 'dokumen']))
            ->assertOk()
            ->assertSee('Bukti pembayaran')
            ->assertSee('transfer.pdf');

        $preview = $this->actingAs($user)
            ->get(route('anggota.dokumen', [$user->anggota, $bukti]));

        $preview->assertOk();
        $this->assertStringContainsString('inline', strtolower((string) $preview->headers->get('content-disposition')));
    }

    public function test_admin_can_view_bukti_pembayaran_below_sk_mengajar(): void
    {
        $user = $this->makeMember();
        $path = 'anggota/'.$user->anggota->id.'/transfer.pdf';
        Storage::disk('local')->put($path, 'pdf');
        $bukti = $user->anggota->dokumen()->create([
            'jenis' => AnggotaDokumen::BUKTI_PEMBAYARAN,
            'path' => $path,
            'nama_asli' => 'transfer.pdf',
            'mime' => 'application/pdf',
            'ukuran' => 3,
        ]);

        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('anggota.show', $user->anggota))
            ->assertOk()
            ->assertSee('SK Mengajar')
            ->assertSee('Bukti pembayaran')
            ->assertSee('transfer.pdf');

        $this->actingAs($admin)
            ->get(route('anggota.dokumen', [$user->anggota, $bukti]))
            ->assertOk();
    }

    private function makeMember(
        string $status = Anggota::STATUS_MENUNGGU_VERIFIKASI_PC,
        bool $verified = true,
    ): User {
        $user = User::factory()->create([
            'name' => 'Guru Portal',
            'email' => 'guru.portal@example.com',
            'email_verified_at' => $verified ? now() : null,
        ]);
        $user->assignRole(Role::ANGGOTA);

        $aktif = $status === Anggota::STATUS_AKTIF;

        Anggota::query()->create([
            'user_id' => $user->id,
            'nik' => '3201010101010099',
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
            'nip' => '199001152020011001',
            'jenjang' => 'SMA',
            'nama_sekolah' => 'SMA Negeri 1',
            'status_sekolah' => 'Negeri',
            'alamat_sekolah' => 'Jl. Sekolah No. 2',
            'pd_kode' => '36',
            'pc_kode' => '36.71',
            'status' => $status,
            'nomor_anggota' => $aktif ? now()->year.'.36.3671.099' : null,
            'tanggal_bergabung' => $aktif ? now()->toDateString() : null,
            'masa_berlaku_hingga' => $aktif ? now()->addYears(3)->toDateString() : null,
        ]);

        return $user->fresh(['anggota', 'roles']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function profilePayload(User $user, array $overrides = []): array
    {
        $anggota = $user->anggota;

        return array_merge([
            'tab' => 'identitas',
            'nik' => $anggota->nik,
            'nama' => $anggota->nama,
            'gelar_depan' => $anggota->gelar_depan,
            'gelar_belakang' => $anggota->gelar_belakang,
            'jenis_kelamin' => $anggota->jenis_kelamin,
            'tempat_lahir' => $anggota->tempat_lahir,
            'tanggal_lahir' => $anggota->tanggal_lahir?->toDateString(),
            'agama' => $anggota->agama,
            'status_perkawinan' => $anggota->status_perkawinan,
            'hp' => $anggota->hp,
            'whatsapp' => $anggota->whatsapp,
            'email' => $anggota->email,
            'alamat' => $anggota->alamat,
            'provinsi_kode' => $anggota->provinsi_kode,
            'kabupaten_kode' => $anggota->kabupaten_kode,
            'kecamatan_kode' => $anggota->kecamatan_kode,
            'kelurahan_kode' => $anggota->kelurahan_kode,
            'kode_pos' => $anggota->kode_pos,
            'status_guru' => $anggota->status_guru,
            'nip' => $anggota->nip,
            'nuptk' => $anggota->nuptk,
            'jenjang' => $anggota->jenjang,
            'nama_sekolah' => $anggota->nama_sekolah,
            'npsn' => $anggota->npsn,
            'status_sekolah' => $anggota->status_sekolah,
            'alamat_sekolah' => $anggota->alamat_sekolah,
        ], $overrides);
    }
}
