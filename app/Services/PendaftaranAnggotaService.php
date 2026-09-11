<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\AnggotaDokumen;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PendaftaranAnggotaService
{
    public function __construct(
        private readonly AnggotaStatusService $status,
        private readonly SettingService $settings,
        private readonly WhatsAppOtpService $otp,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, UploadedFile>  $dokumen
     */
    public function register(array $data, array $dokumen): User
    {
        $langsung = $this->settings->isLangsung();
        $kanal = $langsung ? null : $this->settings->resolveKanal($data['kanal_verifikasi'] ?? null);

        return DB::transaction(function () use ($data, $dokumen, $langsung, $kanal) {
            $user = User::query()->create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => $data['password'],
                'email_verified_at' => $langsung ? now() : null,
            ]);

            $user->assignRole(Role::ANGGOTA);

            $status = $langsung
                ? Anggota::STATUS_MENUNGGU_VERIFIKASI_PC
                : Anggota::STATUS_BELUM_VERIFIKASI_EMAIL;

            $alasan = $langsung
                ? 'Pendaftaran langsung. Menunggu verifikasi PC.'
                : ($kanal === SettingService::KANAL_WHATSAPP
                    ? 'Pendaftaran online diterima. Menunggu verifikasi WhatsApp.'
                    : 'Pendaftaran online diterima. Menunggu verifikasi email.');

            $anggota = Anggota::query()->create([
                'user_id' => $user->id,
                'nik' => $data['nik'],
                'gelar_depan' => $data['gelar_depan'] ?? null,
                'nama' => $data['nama'],
                'gelar_belakang' => $data['gelar_belakang'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'agama' => $data['agama'],
                'status_perkawinan' => $data['status_perkawinan'],
                'hp' => $data['hp'],
                'whatsapp' => $data['whatsapp'],
                'email' => $data['email'],
                'alamat' => $data['alamat'],
                'provinsi_kode' => $data['provinsi_kode'],
                'kabupaten_kode' => $data['kabupaten_kode'],
                'kecamatan_kode' => $data['kecamatan_kode'],
                'kelurahan_kode' => $data['kelurahan_kode'],
                'kode_pos' => $data['kode_pos'],
                'status_guru' => $data['status_guru'],
                'nip' => $data['nip'] ?? null,
                'nuptk' => $data['nuptk'] ?? null,
                'nomor_gtk' => $data['nomor_gtk'] ?? null,
                'mapel' => $data['mapel'] ?? null,
                'jenjang' => $data['jenjang'],
                'nama_sekolah' => $data['nama_sekolah'],
                'npsn' => $data['npsn'] ?? null,
                'status_sekolah' => $data['status_sekolah'],
                'alamat_sekolah' => $data['alamat_sekolah'],
                'pd_kode' => $data['provinsi_kode'],
                'pc_kode' => $data['kabupaten_kode'],
                'status' => $status,
                'kanal_verifikasi' => $kanal,
            ]);

            foreach ($dokumen as $jenis => $file) {
                $path = $file->store("anggota/{$anggota->id}", 'local');

                $anggota->dokumen()->create([
                    'jenis' => $jenis,
                    'path' => $path,
                    'nama_asli' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'ukuran' => $file->getSize() ?: 0,
                ]);

                if ($jenis === AnggotaDokumen::PAS_FOTO) {
                    $anggota->update(['foto_path' => $path]);
                }
            }

            $this->status->log($anggota, $status, null, $alasan, $user);

            return $user->fresh(['roles', 'anggota']);
        });
    }

    public function afterRegister(User $user): string
    {
        if ($this->settings->isLangsung()) {
            return $user->homeRoute(true);
        }

        $kanal = $user->anggota?->kanal_verifikasi;

        if ($kanal === SettingService::KANAL_WHATSAPP) {
            $this->otp->send($user, (string) $user->anggota?->whatsapp);

            return route('verification.notice');
        }

        event(new Registered($user));

        return route('verification.notice');
    }
}
