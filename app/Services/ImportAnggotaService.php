<?php

namespace App\Services;

use App\Helpers\Area;
use App\Imports\AnggotaImport;
use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ImportAnggotaService
{
    private const HP_DEFAULT = '081';

    public function __construct(
        private readonly NomorAnggotaService $nomorAnggota,
        private readonly AnggotaStatusService $status,
    ) {}

    /**
     * @return array{imported: int, failed: int, rows: list<array{baris: int, nta: string, nama: string, email: string, password: string|null, ok: bool, pesan: string}>}
     */
    public function import(UploadedFile $file, User $aktor, bool $akunAktif, bool $terverifikasi): array
    {
        $reader = new AnggotaImport;
        Excel::import($reader, $file);

        $hasil = [];
        $seenNta = [];
        $seenEmail = [];
        $seenNik = [];

        foreach ($reader->rows as $index => $row) {
            $baris = $index + 2;
            $cells = $this->cells($row);

            if ($this->rowKosong($cells)) {
                continue;
            }

            $hasil[] = $this->imporBaris(
                $baris,
                $cells,
                $aktor,
                $akunAktif,
                $terverifikasi,
                $seenNta,
                $seenEmail,
                $seenNik,
            );
        }

        $gagal = collect($hasil)->where('ok', false)->count();

        return [
            'imported' => collect($hasil)->where('ok', true)->count(),
            'failed' => $gagal,
            'rows' => $hasil,
        ];
    }

    /**
     * @param  array<string, string>  $cells
     * @param  array<string, true>  $seenNta
     * @param  array<string, true>  $seenEmail
     * @param  array<string, true>  $seenNik
     * @return array{baris: int, nta: string, nama: string, email: string, password: string|null, ok: bool, pesan: string}
     */
    private function imporBaris(
        int $baris,
        array $cells,
        User $aktor,
        bool $akunAktif,
        bool $terverifikasi,
        array &$seenNta,
        array &$seenEmail,
        array &$seenNik,
    ): array {
        $nta = $this->value($cells, 'nta', 'nomor_anggota', 'no_anggota', 'nomor_nta');
        $namaLengkap = $this->value($cells, 'nama', 'name');
        $email = Str::lower($this->value($cells, 'email'));
        $hp = $this->normalizeHp($this->value($cells, 'hp', 'no_hp', 'telepon', 'telp', 'no_telp', 'handphone')) ?: self::HP_DEFAULT;

        try {
            $parsed = $this->nomorAnggota->parse($nta);

            if ($parsed === null) {
                throw new \InvalidArgumentException('NTA tidak valid. Gunakan format TAHUN.KODE_PROV.KODE_KAB.URUT, contoh 2024.52.5208.002.');
            }

            $nta = $parsed['nomor'];
            $namaParts = $this->splitNama($namaLengkap);

            if ($namaParts['nama'] === '') {
                throw new \InvalidArgumentException('Nama wajib diisi.');
            }

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Email tidak valid.');
            }

            $tempatLahir = $this->value($cells, 'tempat_lahir');

            if ($tempatLahir === '') {
                throw new \InvalidArgumentException('Tempat lahir wajib diisi.');
            }

            $jenisKelamin = $this->parseJenisKelamin($this->value($cells, 'jl', 'jenis_kelamin', 'jk'));

            if ($jenisKelamin === null) {
                throw new \InvalidArgumentException('Jenis kelamin wajib Laki-laki atau Perempuan.');
            }

            $alamat = $this->value($cells, 'alamat');

            if ($alamat === '') {
                throw new \InvalidArgumentException('Alamat wajib diisi.');
            }

            if (isset($seenNta[$nta])) {
                throw new \InvalidArgumentException('NTA duplikat di file yang sama.');
            }

            if (isset($seenEmail[$email])) {
                throw new \InvalidArgumentException('Email duplikat di file yang sama.');
            }

            $seenNta[$nta] = true;
            $seenEmail[$email] = true;

            $this->assertWilayahAktor($aktor, $parsed['provinsi_kode'], $parsed['kabupaten_kode']);

            $provinsi = Wilayah::query()->find($parsed['provinsi_kode']);
            $kabupaten = Wilayah::query()->find($parsed['kabupaten_kode']);

            if ($provinsi === null) {
                throw new \InvalidArgumentException("Provinsi {$parsed['provinsi_kode']} dari NTA tidak ada di master wilayah.");
            }

            if ($kabupaten === null) {
                throw new \InvalidArgumentException("Kabupaten/kota {$parsed['kabupaten_kode']} dari NTA tidak ada di master wilayah.");
            }

            [$kecamatanKode, $kelurahanKode] = $this->wilayahBawahan($parsed['kabupaten_kode']);
            $nik = $this->resolveNik($this->value($cells, 'nik'), $nta, $seenNik);
            $seenNik[$nik] = true;

            if (Anggota::query()->where('nomor_anggota', $nta)->exists()) {
                throw new \InvalidArgumentException('NTA sudah terdaftar.');
            }

            if (User::query()->where('email', $email)->exists() || Anggota::query()->where('email', $email)->exists()) {
                throw new \InvalidArgumentException('Email sudah terdaftar.');
            }

            if (Anggota::query()->where('nik', $nik)->exists()) {
                throw new \InvalidArgumentException('NIK sudah terdaftar.');
            }

            $agama = $this->optionalIn($cells, 'agama', Anggota::agamaOptions()) ?? 'Buddha';
            $statusPerkawinan = $this->optionalIn($cells, 'status_perkawinan', Anggota::statusPerkawinanOptions()) ?? 'Belum kawin';
            $statusGuru = $this->optionalIn($cells, 'status_guru', Anggota::statusGuruOptions()) ?? 'Guru Tidak Tetap';
            $jenjang = $this->optionalIn($cells, 'jenjang', Anggota::jenjangOptions()) ?? 'SD';
            $statusSekolah = $this->optionalIn($cells, 'status_sekolah', Anggota::statusSekolahOptions()) ?? 'Swasta';
            $whatsapp = $this->normalizeHp($this->value($cells, 'whatsapp')) ?: $hp;
            $namaSekolah = $this->value($cells, 'nama_sekolah') ?: 'Belum diisi';
            $alamatSekolah = $this->value($cells, 'alamat_sekolah') ?: $alamat;
            $kodePos = $this->value($cells, 'kode_pos') ?: '00000';
            $password = Str::password(10, symbols: false);
            $status = $terverifikasi ? Anggota::STATUS_AKTIF : Anggota::STATUS_MENUNGGU_VERIFIKASI_PC;
            $tanggalDaftar = now()->toDateString();
            $masaBerlaku = now()->addYears((int) config('pergabi.masa_berlaku_tahun'))->toDateString();

            DB::transaction(function () use (
                $parsed,
                $nta,
                $namaParts,
                $email,
                $hp,
                $tempatLahir,
                $jenisKelamin,
                $alamat,
                $kecamatanKode,
                $kelurahanKode,
                $nik,
                $agama,
                $statusPerkawinan,
                $statusGuru,
                $jenjang,
                $statusSekolah,
                $whatsapp,
                $namaSekolah,
                $alamatSekolah,
                $kodePos,
                $password,
                $status,
                $akunAktif,
                $terverifikasi,
                $tanggalDaftar,
                $masaBerlaku,
                $aktor,
                $cells,
            ): void {
                $user = User::query()->create([
                    'name' => $namaParts['nama'],
                    'email' => $email,
                    'password' => $password,
                    'email_verified_at' => $akunAktif ? now() : null,
                ]);
                $user->assignRole(Role::ANGGOTA);

                $anggota = Anggota::query()->create([
                    'user_id' => $user->id,
                    'nik' => $nik,
                    'gelar_depan' => $namaParts['gelar_depan'],
                    'nama' => $namaParts['nama'],
                    'gelar_belakang' => $namaParts['gelar_belakang'],
                    'jenis_kelamin' => $jenisKelamin,
                    'tempat_lahir' => $tempatLahir,
                    'tanggal_lahir' => null,
                    'agama' => $agama,
                    'status_perkawinan' => $statusPerkawinan,
                    'hp' => $hp,
                    'whatsapp' => $whatsapp,
                    'email' => $email,
                    'alamat' => $alamat,
                    'provinsi_kode' => $parsed['provinsi_kode'],
                    'kabupaten_kode' => $parsed['kabupaten_kode'],
                    'kecamatan_kode' => $kecamatanKode,
                    'kelurahan_kode' => $kelurahanKode,
                    'kode_pos' => $kodePos,
                    'status_guru' => $statusGuru,
                    'nip' => $this->nullable($this->value($cells, 'nip')),
                    'nuptk' => $this->nullable($this->value($cells, 'nuptk')),
                    'jenjang' => $jenjang,
                    'nama_sekolah' => $namaSekolah,
                    'npsn' => $this->nullable($this->value($cells, 'npsn')),
                    'status_sekolah' => $statusSekolah,
                    'alamat_sekolah' => $alamatSekolah,
                    'nomor_anggota' => $nta,
                    'tanggal_bergabung' => $tanggalDaftar,
                    'pd_kode' => $parsed['provinsi_kode'],
                    'pc_kode' => $parsed['kabupaten_kode'],
                    'status' => $status,
                    'kanal_verifikasi' => null,
                    'masa_berlaku_hingga' => $masaBerlaku,
                ]);

                $this->status->log(
                    $anggota,
                    $status,
                    null,
                    $terverifikasi
                        ? 'Impor anggota: terverifikasi seluruh jenjang (PC, PD, PP).'
                        : 'Impor anggota: menunggu verifikasi PC.',
                    $aktor,
                );
            });

            return [
                'baris' => $baris,
                'nta' => $nta,
                'nama' => $namaParts['nama'],
                'email' => $email,
                'password' => $password,
                'ok' => true,
                'pesan' => 'Berhasil diimpor.',
            ];
        } catch (\Throwable $exception) {
            return [
                'baris' => $baris,
                'nta' => $nta,
                'nama' => $namaLengkap,
                'email' => $email,
                'password' => null,
                'ok' => false,
                'pesan' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @param  array<string, mixed>|Collection<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function cells(mixed $row): array
    {
        if ($row instanceof Collection) {
            return $row->toArray();
        }

        return is_array($row) ? $row : [];
    }

    /**
     * @param  array<string, mixed>  $cells
     */
    private function rowKosong(array $cells): bool
    {
        foreach ($cells as $value) {
            if (trim($this->stringify($value)) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $cells
     */
    private function value(array $cells, string ...$keys): string
    {
        return trim($this->stringify($this->raw($cells, ...$keys)));
    }

    /**
     * @param  array<string, mixed>  $cells
     */
    private function raw(array $cells, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            foreach ($cells as $heading => $value) {
                if (Str::slug((string) $heading, '_') === $key) {
                    return $value;
                }
            }
        }

        return null;
    }

    private function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return number_format($value, 0, '', '');
        }

        return trim((string) $value);
    }

    private function nullable(string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    private function normalizeHp(string $hp): string
    {
        $digits = preg_replace('/\D+/', '', $hp) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '62') && strlen($digits) >= 11) {
            $digits = '0'.substr($digits, 2);
        }

        if (str_starts_with($digits, '8') && strlen($digits) >= 10 && strlen($digits) <= 13) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    /**
     * @return array{gelar_depan: ?string, nama: string, gelar_belakang: ?string}
     */
    private function splitNama(string $nama): array
    {
        $nama = trim(preg_replace('/\s+/', ' ', $nama) ?? $nama);
        $gelarBelakang = null;

        if (str_contains($nama, ',')) {
            [$nama, $gelar] = array_map('trim', explode(',', $nama, 2));
            $gelarBelakang = $gelar !== '' ? $gelar : null;
        }

        return [
            'gelar_depan' => null,
            'nama' => $nama,
            'gelar_belakang' => $gelarBelakang,
        ];
    }

    private function parseJenisKelamin(string $value): ?string
    {
        $value = Str::lower(trim($value));

        return match (true) {
            in_array($value, ['l', 'laki-laki', 'laki laki', 'pria', 'male'], true) => Anggota::JENIS_KELAMIN_L,
            in_array($value, ['p', 'perempuan', 'wanita', 'female'], true) => Anggota::JENIS_KELAMIN_P,
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $cells
     * @param  array<string, string>  $options
     */
    private function optionalIn(array $cells, string $key, array $options): ?string
    {
        $value = $this->value($cells, $key);

        if ($value === '') {
            return null;
        }

        foreach ($options as $option => $label) {
            if (strcasecmp($value, $option) === 0 || strcasecmp($value, $label) === 0) {
                return $option;
            }
        }

        return null;
    }

    /**
     * @param  array<string, true>  $seenNik
     */
    private function resolveNik(string $nik, string $nta, array $seenNik): string
    {
        $nik = preg_replace('/\D+/', '', $nik) ?? '';

        if (strlen($nik) === 16) {
            if (isset($seenNik[$nik])) {
                throw new \InvalidArgumentException('NIK duplikat di file yang sama.');
            }

            return $nik;
        }

        if ($nik !== '') {
            throw new \InvalidArgumentException('NIK harus 16 digit jika diisi.');
        }

        $digits = preg_replace('/\D+/', '', $nta) ?? '';
        $generated = str_pad($digits, 16, '9', STR_PAD_LEFT);

        while (isset($seenNik[$generated]) || Anggota::query()->where('nik', $generated)->exists()) {
            $generated = str_pad((string) (((int) $generated) + 1), 16, '0', STR_PAD_LEFT);
        }

        return $generated;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function wilayahBawahan(string $kabupatenKode): array
    {
        $kecamatan = Wilayah::query()->anak($kabupatenKode)->orderBy('kode')->first();
        $kecamatanKode = $kecamatan?->kode ?? $kabupatenKode.'.01';
        $kelurahan = Wilayah::query()->anak($kecamatanKode)->orderBy('kode')->first();
        $kelurahanKode = $kelurahan?->kode ?? $kecamatanKode.'.1001';

        return [$kecamatanKode, $kelurahanKode];
    }

    private function assertWilayahAktor(User $aktor, string $provinsi, string $kabupaten): void
    {
        if ($aktor->isNasional()) {
            return;
        }

        if ($aktor->organisasiLevel() === Area::DAERAH && $aktor->pd_kode !== $provinsi) {
            throw new \InvalidArgumentException('NTA di luar wilayah PD Anda.');
        }

        if ($aktor->organisasiLevel() === Area::CABANG && $aktor->pc_kode !== $kabupaten) {
            throw new \InvalidArgumentException('NTA di luar wilayah PC Anda.');
        }
    }
}
