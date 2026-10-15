<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\AnggotaStatusLog;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\NomorAnggotaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SampleAnggotaSeeder extends Seeder
{
    /**
     * @var array<string, int|null>
     */
    private array $aktor = [];

    public function run(): void
    {
        if (! Role::query()->where('slug', Role::ANGGOTA)->exists()) {
            $this->call(RolePermissionSeeder::class);
        }

        $this->assertWilayah();
        $this->aktor = [
            'pc' => User::query()->where('email', 'admin.pc@example.com')->value('id'),
            'pd' => User::query()->where('email', 'admin.pd@example.com')->value('id'),
            'pp' => User::query()->where('email', 'admin@example.com')->value('id'),
        ];

        $created = 0;

        foreach ($this->samples() as $sample) {
            if (Anggota::query()->where('email', $sample['email'])->exists()) {
                $this->command?->line("Lewati {$sample['email']} (sudah ada).");

                continue;
            }

            DB::transaction(function () use ($sample): void {
                $this->createSample($sample);
            });

            $created++;
            $this->command?->info("{$sample['email']} — {$sample['status']}");
        }

        $this->command?->info("Sample anggota baru: {$created}. Password login: password");
    }

    /**
     * @param  array<string, mixed>  $sample
     */
    private function createSample(array $sample): void
    {
        $verified = $sample['status'] !== Anggota::STATUS_BELUM_VERIFIKASI_EMAIL;

        $user = User::query()->firstOrCreate(
            ['email' => $sample['email']],
            [
                'name' => $sample['nama'],
                'password' => Hash::make('password'),
                'email_verified_at' => $verified ? now() : null,
            ],
        );

        if (! $user->wasRecentlyCreated) {
            $user->forceFill([
                'name' => $sample['nama'],
                'email_verified_at' => $verified ? ($user->email_verified_at ?? now()) : null,
            ])->save();
        }

        $user->assignRole(Role::ANGGOTA);

        $issued = in_array($sample['status'], [Anggota::STATUS_AKTIF, Anggota::STATUS_TIDAK_AKTIF], true);
        $history = $this->history($sample['status']);
        $start = now()->subDays(count($history) + 2)->startOfDay();

        $anggota = Anggota::query()->create([
            'user_id' => $user->id,
            'nik' => $sample['nik'],
            'gelar_depan' => $sample['gelar_depan'],
            'nama' => $sample['nama'],
            'gelar_belakang' => $sample['gelar_belakang'],
            'jenis_kelamin' => $sample['jenis_kelamin'],
            'tempat_lahir' => $sample['tempat_lahir'],
            'tanggal_lahir' => $sample['tanggal_lahir'],
            'agama' => 'Buddha',
            'status_perkawinan' => $sample['status_perkawinan'],
            'hp' => $sample['hp'],
            'whatsapp' => $sample['hp'],
            'email' => $sample['email'],
            'alamat' => $sample['alamat'],
            'provinsi_kode' => $sample['provinsi_kode'],
            'kabupaten_kode' => $sample['kabupaten_kode'],
            'kecamatan_kode' => $sample['kecamatan_kode'],
            'kelurahan_kode' => $sample['kelurahan_kode'],
            'kode_pos' => $sample['kode_pos'],
            'status_guru' => $sample['status_guru'],
            'nip' => $sample['nip'],
            'nuptk' => $sample['nuptk'],
            'jenjang' => $sample['jenjang'],
            'nama_sekolah' => $sample['nama_sekolah'],
            'npsn' => $sample['npsn'],
            'status_sekolah' => $sample['status_sekolah'],
            'alamat_sekolah' => $sample['alamat_sekolah'],
            'tanggal_bergabung' => $issued ? $start->toDateString() : null,
            'pd_kode' => $sample['provinsi_kode'],
            'pc_kode' => $sample['kabupaten_kode'],
            'status' => $sample['status'],
            'kanal_verifikasi' => $verified ? null : 'email',
            'masa_berlaku_hingga' => match ($sample['status']) {
                Anggota::STATUS_AKTIF => now()->addYears((int) config('pergabi.masa_berlaku_tahun'))->toDateString(),
                Anggota::STATUS_TIDAK_AKTIF => now()->subMonth()->toDateString(),
                default => null,
            },
        ]);

        if ($issued) {
            $anggota->nomor_anggota = app(NomorAnggotaService::class)->issue($anggota);
        }

        $anggota->forceFill([
            'created_at' => $start,
            'updated_at' => now(),
        ])->save();

        $previous = null;

        foreach ($history as $index => $status) {
            AnggotaStatusLog::query()->create([
                'anggota_id' => $anggota->id,
                'status_dari' => $previous,
                'status_ke' => $status,
                'alasan' => $this->alasan($status),
                'user_id' => $this->aktorId($status),
                'created_at' => $start->copy()->addDays($index),
            ]);

            $previous = $status;
        }
    }

    /**
     * @return list<string>
     */
    private function history(string $status): array
    {
        $pipeline = [
            Anggota::STATUS_MENUNGGU_VERIFIKASI_PC,
            Anggota::STATUS_MENUNGGU_VALIDASI_PD,
            Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP,
            Anggota::STATUS_AKTIF,
        ];

        if ($status === Anggota::STATUS_BELUM_VERIFIKASI_EMAIL) {
            return [Anggota::STATUS_BELUM_VERIFIKASI_EMAIL];
        }

        if ($status === Anggota::STATUS_DITOLAK) {
            return [Anggota::STATUS_MENUNGGU_VERIFIKASI_PC, Anggota::STATUS_DITOLAK];
        }

        if ($status === Anggota::STATUS_TIDAK_AKTIF) {
            return [...$pipeline, Anggota::STATUS_TIDAK_AKTIF];
        }

        $index = array_search($status, $pipeline, true);

        if ($index === false) {
            return [$status];
        }

        return array_slice($pipeline, 0, $index + 1);
    }

    private function alasan(string $status): string
    {
        return match ($status) {
            Anggota::STATUS_BELUM_VERIFIKASI_EMAIL => 'Pendaftaran sample. Menunggu verifikasi email.',
            Anggota::STATUS_MENUNGGU_VERIFIKASI_PC => 'Pendaftaran sample diterima.',
            Anggota::STATUS_MENUNGGU_VALIDASI_PD => 'Dokumen lengkap, diteruskan ke PD.',
            Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP => 'Data sesuai wilayah, diteruskan ke PP.',
            Anggota::STATUS_AKTIF => 'Disetujui PP. Nomor anggota terbit.',
            Anggota::STATUS_TIDAK_AKTIF => 'Masa berlaku habis.',
            Anggota::STATUS_DITOLAK => 'Dokumen tidak sesuai.',
            default => 'Data sample.',
        };
    }

    private function aktorId(string $status): ?int
    {
        $key = match ($status) {
            Anggota::STATUS_MENUNGGU_VALIDASI_PD, Anggota::STATUS_DITOLAK => 'pc',
            Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP => 'pd',
            Anggota::STATUS_AKTIF => 'pp',
            default => null,
        };

        if ($key === null) {
            return null;
        }

        $id = $this->aktor[$key] ?? null;

        return $id !== null ? (int) $id : null;
    }

    private function assertWilayah(): void
    {
        $kodes = [
            '31', '31.71', '31.71.01', '31.71.01.1001',
            '32', '32.73', '32.73.01', '32.73.01.1001',
            '36', '36.03', '36.03.01', '36.03.01.1001',
            '36.71', '36.71.01', '36.71.01.1001', '36.71.02', '36.71.02.1001',
            '36.72', '36.72.01', '36.72.01.1001',
            '36.74', '36.74.01', '36.74.01.1001',
        ];

        $missing = collect($kodes)
            ->reject(fn (string $kode): bool => Wilayah::query()->whereKey($kode)->exists())
            ->values();

        if ($missing->isNotEmpty()) {
            throw new RuntimeException('Wilayah sample belum ada: '.$missing->implode(', ').'. Jalankan WilayahSeeder dulu.');
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function samples(): array
    {
        return [
            $this->row('anggota@example.com', '3671015501850001', 'Sari', 'Dewi', 'P', 'S.Pd.', Anggota::STATUS_AKTIF, 'ASN', 'SMA', 'SMAN 1 Kota Tangerang', '36.71', '36.71.01', '36.71.01.1001', '15111', 'Jl. Daan Mogot No. 10, Sukarasa'),
            $this->row('budi.wicaksono@example.com', '3671011203800002', 'Budi', 'Wicaksono', 'L', 'S.Pd.', Anggota::STATUS_MENUNGGU_VERIFIKASI_PC, 'ASN', 'SMP', 'SMPN 2 Kota Tangerang', '36.71', '36.71.02', '36.71.02.1001', '15134', 'Jl. Gatot Subroto No. 8, Keroncong'),
            $this->row('dewi.lestari@example.com', '3671014603920003', 'Dewi', 'Lestari', 'P', 'S.Pd.', Anggota::STATUS_MENUNGGU_VALIDASI_PD, 'Guru Tetap Yayasan', 'SD', 'SDK Bhakti Kota Tangerang', '36.71', '36.71.01', '36.71.01.1001', '15111', 'Jl. MH Thamrin No. 5, Sukarasa'),
            $this->row('agus.pratama@example.com', '3671010805780004', 'Agus', 'Pratama', 'L', 'M.Pd.', Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP, 'ASN', 'SMK', 'SMKN 1 Kota Tangerang', '36.71', '36.71.02', '36.71.02.1001', '15134', 'Jl. Imam Bonjol No. 12, Keroncong'),
            $this->row('rina.kartika@example.com', '3671016204880005', 'Rina', 'Kartika', 'P', 'S.Pd.', Anggota::STATUS_TIDAK_AKTIF, 'Guru Tidak Tetap', 'TK/PAUD', 'TK Bhakti Jaya', '36.71', '36.71.01', '36.71.01.1001', '15111', 'Jl. Kisamaun No. 3, Sukarasa'),
            $this->row('made.sutrisna@example.com', '3671011508750006', 'Made', 'Sutrisna', 'L', null, Anggota::STATUS_DITOLAK, 'Guru Tidak Tetap', 'SD', 'SD Bodhisattva', '36.71', '36.71.02', '36.71.02.1001', '15134', 'Jl. Perintis Kemerdekaan No. 7, Keroncong'),
            $this->row('yoga.pramudita@example.com', '3671012202950007', 'Yoga', 'Pramudita', 'L', 'S.Pd.', Anggota::STATUS_BELUM_VERIFIKASI_EMAIL, 'ASN', 'SMA', 'SMAN 3 Kota Tangerang', '36.71', '36.71.01', '36.71.01.1001', '15111', 'Jl. Otista No. 15, Sukarasa'),
            $this->row('komang.adnyana@example.com', '3671010304810008', 'Komang', 'Adnyana', 'L', 'S.Pd.', Anggota::STATUS_AKTIF, 'ASN', 'Dhammasekha SMP', 'Dhammasekha SMP Metta', '36.71', '36.71.02', '36.71.02.1001', '15134', 'Jl. Industri No. 4, Keroncong'),
            $this->row('lina.kusuma@example.com', '3603015103890009', 'Lina', 'Kusuma', 'P', 'S.Pd.', Anggota::STATUS_AKTIF, 'Guru Tetap Yayasan', 'SD', 'SD Bodhicitta Balaraja', '36.03', '36.03.01', '36.03.01.1001', '15610', 'Jl. Raya Balaraja No. 20'),
            $this->row('hendra.gunawan@example.com', '3672011704770010', 'Hendra', 'Gunawan', 'L', 'S.Pd.', Anggota::STATUS_AKTIF, 'ASN', 'SMP', 'SMPN 1 Cilegon', '36.72', '36.72.01', '36.72.01.1001', '42426', 'Jl. Ahmad Yani No. 9, Cibeber'),
            $this->row('putri.anggraini@example.com', '3674016406910011', 'Putri', 'Anggraini', 'P', 'S.Pd.', Anggota::STATUS_MENUNGGU_VERIFIKASI_PC, 'Guru Tidak Tetap', 'SMA', 'SMA Dharma Karya Serpong', '36.74', '36.74.01', '36.74.01.1001', '15325', 'Jl. Ciater Raya No. 11'),
            $this->row('ahmad.dharma@example.com', '3273012508820012', 'Ahmad', 'Dharma', 'L', 'M.Pd.', Anggota::STATUS_AKTIF, 'ASN', 'Dhammasekha SMA', 'Dhammasekha SMA Sukasari', '32.73', '32.73.01', '32.73.01.1001', '40154', 'Jl. Sukarasa No. 6, Kota Bandung', '32', 'Bandung'),
            $this->row('siti.rahayu@example.com', '3171014802860013', 'Siti', 'Rahayu', 'P', 'S.Pd.', Anggota::STATUS_AKTIF, 'Guru Tetap Yayasan', 'TK/PAUD', 'TK Metta Gambir', '31.71', '31.71.01', '31.71.01.1001', '10110', 'Jl. Medan Merdeka No. 2, Gambir', '31', 'Jakarta'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function row(
        string $email,
        string $nik,
        string $namaDepan,
        string $namaBelakang,
        string $jenisKelamin,
        ?string $gelarBelakang,
        string $status,
        string $statusGuru,
        string $jenjang,
        string $sekolah,
        string $kabupaten,
        string $kecamatan,
        string $kelurahan,
        string $kodePos,
        string $alamat,
        string $provinsi = '36',
        string $tempatLahir = 'Tangerang',
    ): array {
        $nama = trim($namaDepan.' '.$namaBelakang);
        $asn = $statusGuru === 'ASN';

        return [
            'email' => $email,
            'nik' => $nik,
            'nama' => $nama,
            'gelar_depan' => null,
            'gelar_belakang' => $gelarBelakang,
            'jenis_kelamin' => $jenisKelamin,
            'tempat_lahir' => $tempatLahir,
            'tanggal_lahir' => '1985-01-15',
            'status_perkawinan' => $jenisKelamin === 'P' ? 'Kawin' : 'Belum kawin',
            'hp' => '0812'.substr($nik, -8),
            'alamat' => $alamat,
            'provinsi_kode' => $provinsi,
            'kabupaten_kode' => $kabupaten,
            'kecamatan_kode' => $kecamatan,
            'kelurahan_kode' => $kelurahan,
            'kode_pos' => $kodePos,
            'status_guru' => $statusGuru,
            'nip' => $asn ? '19'.substr($nik, 0, 16) : null,
            'nuptk' => $asn ? substr($nik, 0, 16) : null,
            'jenjang' => $jenjang,
            'nama_sekolah' => $sekolah,
            'npsn' => substr($nik, 0, 8),
            'status_sekolah' => $asn ? 'Negeri' : 'Swasta',
            'alamat_sekolah' => $alamat,
            'status' => $status,
        ];
    }
}
