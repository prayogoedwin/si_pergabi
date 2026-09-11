<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\AnggotaStatusLog;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AnggotaStatusService
{
    public function __construct(private readonly NomorAnggotaService $nomorAnggota) {}

    public function log(
        Anggota $anggota,
        string $ke,
        ?string $dari = null,
        ?string $alasan = null,
        ?User $aktor = null,
    ): AnggotaStatusLog {
        return AnggotaStatusLog::query()->create([
            'anggota_id' => $anggota->id,
            'status_dari' => $dari,
            'status_ke' => $ke,
            'alasan' => $alasan,
            'user_id' => $aktor?->id,
            'created_at' => now(),
        ]);
    }

    public function transition(
        Anggota $anggota,
        string $ke,
        string $alasan,
        ?User $aktor = null,
        bool $system = false,
    ): Anggota {
        $dari = $anggota->status;

        if ($dari === $ke) {
            throw new InvalidArgumentException('Status anggota tidak berubah.');
        }

        if (! $this->isAllowed($dari, $ke, $system)) {
            throw new InvalidArgumentException("Perpindahan status dari {$dari} ke {$ke} tidak diizinkan.");
        }

        if ($ke === Anggota::STATUS_DITOLAK && filled($alasan) === false) {
            throw new InvalidArgumentException('Alasan wajib diisi jika menolak pendaftaran.');
        }

        if (! $system && $ke !== Anggota::STATUS_DITOLAK && filled($alasan) === false) {
            $alasan = match ($ke) {
                Anggota::STATUS_MENUNGGU_VALIDASI_PD => 'Diverifikasi PC.',
                Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP => 'Divalidasi PD.',
                Anggota::STATUS_AKTIF => 'Disetujui PP.',
                default => $alasan,
            };
        }

        return DB::transaction(function () use ($anggota, $ke, $dari, $alasan, $aktor, $system) {
            $anggota->status = $ke;

            if ($ke === Anggota::STATUS_AKTIF) {
                $anggota->tanggal_bergabung ??= now()->toDateString();
                $anggota->masa_berlaku_hingga ??= now()->addYears((int) config('pergabi.masa_berlaku_tahun'))->toDateString();
                $anggota->nomor_anggota ??= $this->nomorAnggota->issue($anggota);
            }

            $anggota->save();

            $this->log(
                $anggota,
                $ke,
                $dari,
                $system ? ($alasan ?: 'Pembaruan sistem') : $alasan,
                $aktor,
            );

            return $anggota->refresh();
        });
    }

    public function verifyEmail(Anggota $anggota, User $user): Anggota
    {
        return $this->verifyKontak($anggota, $user, 'Email berhasil diverifikasi.');
    }

    public function verifyWhatsApp(Anggota $anggota, User $user): Anggota
    {
        return $this->verifyKontak($anggota, $user, 'WhatsApp berhasil diverifikasi.');
    }

    public function verifyKontak(Anggota $anggota, User $user, string $alasan): Anggota
    {
        if ($anggota->status !== Anggota::STATUS_BELUM_VERIFIKASI_EMAIL) {
            return $anggota;
        }

        return $this->transition(
            $anggota,
            Anggota::STATUS_MENUNGGU_VERIFIKASI_PC,
            $alasan,
            $user,
            true,
        );
    }

    public function verifyByPc(Anggota $anggota, User $aktor, string $alasan): Anggota
    {
        $this->assertPermission($aktor, 'verify-anggota-pc');

        return $this->transition($anggota, Anggota::STATUS_MENUNGGU_VALIDASI_PD, $alasan, $aktor);
    }

    public function validateByPd(Anggota $anggota, User $aktor, string $alasan): Anggota
    {
        $this->assertPermission($aktor, 'validate-anggota-pd');

        return $this->transition($anggota, Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP, $alasan, $aktor);
    }

    public function approveByPp(Anggota $anggota, User $aktor, string $alasan): Anggota
    {
        $this->assertPermission($aktor, 'approve-anggota-pp');

        return $this->transition($anggota, Anggota::STATUS_AKTIF, $alasan, $aktor);
    }

    public function reject(Anggota $anggota, User $aktor, string $alasan): Anggota
    {
        $permission = match ($anggota->status) {
            Anggota::STATUS_MENUNGGU_VERIFIKASI_PC => 'verify-anggota-pc',
            Anggota::STATUS_MENUNGGU_VALIDASI_PD => 'validate-anggota-pd',
            Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP => 'approve-anggota-pp',
            default => null,
        };

        if ($permission === null) {
            throw new InvalidArgumentException('Pendaftaran ini tidak dapat ditolak pada status saat ini.');
        }

        $this->assertPermission($aktor, $permission);

        return $this->transition($anggota, Anggota::STATUS_DITOLAK, $alasan, $aktor);
    }

    private function isAllowed(string $dari, string $ke, bool $system): bool
    {
        $map = [
            Anggota::STATUS_BELUM_VERIFIKASI_EMAIL => [Anggota::STATUS_MENUNGGU_VERIFIKASI_PC],
            Anggota::STATUS_MENUNGGU_VERIFIKASI_PC => [Anggota::STATUS_MENUNGGU_VALIDASI_PD, Anggota::STATUS_DITOLAK],
            Anggota::STATUS_MENUNGGU_VALIDASI_PD => [Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP, Anggota::STATUS_DITOLAK],
            Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP => [Anggota::STATUS_AKTIF, Anggota::STATUS_DITOLAK],
            Anggota::STATUS_AKTIF => [Anggota::STATUS_TIDAK_AKTIF],
            Anggota::STATUS_TIDAK_AKTIF => [Anggota::STATUS_AKTIF],
        ];

        if ($dari === Anggota::STATUS_BELUM_VERIFIKASI_EMAIL && $ke === Anggota::STATUS_MENUNGGU_VERIFIKASI_PC) {
            return $system;
        }

        return in_array($ke, $map[$dari] ?? [], true);
    }

    private function assertPermission(User $aktor, string $permission): void
    {
        if ($aktor->isSuperAdmin() || $aktor->hasPermission($permission)) {
            return;
        }

        throw new AuthorizationException('Anda tidak berwenang memproses status ini.');
    }
}
