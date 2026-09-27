<?php

namespace App\Services;

use App\Models\Anggota;
use Illuminate\Database\Eloquent\Builder;

class NomorAnggotaService
{
    public function issue(Anggota $anggota): string
    {
        $tahun = (string) now()->year;
        $provinsi = $this->bpsCode($anggota->pd_kode, 2);
        $kabupaten = $this->bpsCode($anggota->pc_kode, 4);
        $prefix = "{$tahun}.{$provinsi}.{$kabupaten}.";

        $terakhir = Anggota::query()
            ->whereNotNull('nomor_anggota')
            ->where(function (Builder $query) use ($anggota, $provinsi, $kabupaten): void {
                $query->where('pc_kode', $anggota->pc_kode)
                    ->orWhere('nomor_anggota', 'like', '%.'.$provinsi.'.'.$kabupaten.'.%');
            })
            ->lockForUpdate()
            ->pluck('nomor_anggota');

        $urut = 1;

        foreach ($terakhir as $nomor) {
            $parsed = is_string($nomor) ? $this->parse($nomor) : null;

            if ($parsed === null) {
                continue;
            }

            $urut = max($urut, ((int) $parsed['urut']) + 1);
        }

        return $prefix.str_pad((string) $urut, 3, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{nomor: string, tahun: string, provinsi_kode: string, kabupaten_kode: string, urut: string}|null
     */
    public function parse(string $nta): ?array
    {
        $nta = trim($nta);

        if ($nta === '') {
            return null;
        }

        if (! preg_match('/^(\d{4})\.(\d{2})\.(\d{2}|\d{4})\.(\d+)$/', $nta, $match)) {
            return null;
        }

        $tahun = $match[1];
        $provinsi = $match[2];
        $kabupatenBps = $match[3];
        $urut = str_pad($match[4], 3, '0', STR_PAD_LEFT);

        if (strlen($kabupatenBps) === 2) {
            $kabupatenBps = $provinsi.$kabupatenBps;
        }

        $kabupatenKode = substr($kabupatenBps, 0, 2).'.'.substr($kabupatenBps, 2, 2);

        if ($provinsi !== substr($kabupatenBps, 0, 2)) {
            return null;
        }

        return [
            'nomor' => "{$tahun}.{$provinsi}.{$kabupatenBps}.{$urut}",
            'tahun' => $tahun,
            'provinsi_kode' => $provinsi,
            'kabupaten_kode' => $kabupatenKode,
            'urut' => $urut,
        ];
    }

    private function bpsCode(string $kode, int $length): string
    {
        $digits = str_replace('.', '', $kode);

        return substr($digits, 0, $length);
    }
}
