<?php

namespace App\Services;

use App\Models\Anggota;

class NomorAnggotaService
{
    public function issue(Anggota $anggota): string
    {
        $tahun = (string) now()->year;
        $provinsi = $this->bpsCode($anggota->pd_kode, 2);
        $kabupaten = $this->bpsCode($anggota->pc_kode, 4);
        $prefix = "{$tahun}.{$provinsi}.{$kabupaten}.";

        $terakhir = Anggota::query()
            ->where('nomor_anggota', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('nomor_anggota')
            ->value('nomor_anggota');

        $urut = 1;

        if (is_string($terakhir)) {
            $bagian = explode('.', $terakhir);
            $urut = ((int) end($bagian)) + 1;
        }

        return $prefix.str_pad((string) $urut, 3, '0', STR_PAD_LEFT);
    }

    private function bpsCode(string $kode, int $length): string
    {
        $digits = str_replace('.', '', $kode);

        return substr($digits, 0, $length);
    }
}
