<?php

namespace App\Services;

use App\Helpers\Area;
use App\Models\Anggota;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanService
{
    /**
     * @return list<array{judul: string, headings: list<string>, rows: list<list<string|int>>}>
     */
    public function sections(User $user, ?string $provinsi, ?string $kabupaten, int $tahun): array
    {
        $base = Anggota::query()->visibleTo($user, $provinsi, $kabupaten);
        $total = (clone $base)->count();

        $statusCounts = $this->grouped($base, 'status');
        $guruCounts = $this->grouped($base, 'status_guru');
        $jenjangCounts = $this->grouped($base, 'jenjang');
        $pdCounts = $this->grouped($base, 'pd_kode');
        $pcCounts = $this->grouped($base, 'pc_kode');
        $cakupan = $this->cakupanLabel($user, $provinsi, $kabupaten);

        return [
            [
                'judul' => $user->isNasional() ? 'Jumlah anggota nasional' : 'Jumlah anggota',
                'headings' => ['Cakupan', 'Jumlah'],
                'rows' => [[$cakupan, $total]],
            ],
            [
                'judul' => 'Jumlah anggota per provinsi',
                'headings' => ['Provinsi (PD)', 'Jumlah'],
                'rows' => $this->wilayahRows($this->provinsiUntukLaporan($user, $provinsi), $pdCounts),
            ],
            [
                'judul' => 'Jumlah anggota per kabupaten/kota',
                'headings' => ['Kabupaten/Kota (PC)', 'Jumlah'],
                'rows' => $this->wilayahRows($this->kabupatenUntukLaporan($user, $provinsi, $kabupaten), $pcCounts),
            ],
            [
                'judul' => 'Anggota aktif / tidak aktif',
                'headings' => ['Status', 'Jumlah'],
                'rows' => [
                    ['Aktif', (int) ($statusCounts[Anggota::STATUS_AKTIF] ?? 0)],
                    ['Tidak aktif', (int) ($statusCounts[Anggota::STATUS_TIDAK_AKTIF] ?? 0)],
                ],
            ],
            [
                'judul' => 'Status guru',
                'headings' => ['Status guru', 'Jumlah'],
                'rows' => $this->optionRows(Anggota::statusGuruOptions(), $guruCounts),
            ],
            [
                'judul' => 'Jenjang pendidikan',
                'headings' => ['Jenjang', 'Jumlah'],
                'rows' => $this->optionRows(Anggota::jenjangOptions(), $jenjangCounts),
            ],
            [
                'judul' => 'Rekap pendaftaran '.$tahun,
                'headings' => ['Bulan', 'Jumlah pendaftaran'],
                'rows' => $this->monthly($base, 'created_at', $tahun),
            ],
            [
                'judul' => 'Rekap perpanjangan '.$tahun,
                'headings' => ['Bulan', 'Jumlah masa berlaku habis'],
                'rows' => $this->monthly($base, 'masa_berlaku_hingga', $tahun),
            ],
        ];
    }

    /**
     * @param  Builder<Anggota>  $base
     * @return Collection<string, int>
     */
    private function grouped(Builder $base, string $column): Collection
    {
        $allowed = ['status', 'status_guru', 'jenjang', 'pd_kode', 'pc_kode'];

        if (! in_array($column, $allowed, true)) {
            throw new \InvalidArgumentException('Kolom rekap tidak valid.');
        }

        return (clone $base)
            ->selectRaw($column.' as kunci, count(*) as total')
            ->groupBy($column)
            ->pluck('total', 'kunci')
            ->map(fn ($total) => (int) $total);
    }

    /**
     * @param  Collection<int, Wilayah>  $wilayah
     * @param  Collection<string, int>  $counts
     * @return list<list<string|int>>
     */
    private function wilayahRows(Collection $wilayah, Collection $counts): array
    {
        if ($wilayah->isEmpty()) {
            $kodes = $counts->keys()->filter(fn ($kode) => filled($kode))->values()->all();
            $wilayah = $kodes === []
                ? collect()
                : Wilayah::query()->whereIn('kode', $kodes)->orderBy('nama')->get();
        }

        if ($wilayah->isEmpty()) {
            return [];
        }

        return $wilayah
            ->map(fn (Wilayah $item) => [$item->nama, (int) ($counts[$item->kode] ?? 0)])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string>  $options
     * @param  Collection<string, int>  $counts
     * @return list<list<string|int>>
     */
    private function optionRows(array $options, Collection $counts): array
    {
        $rows = [];

        foreach ($options as $value => $label) {
            $rows[] = [$label, (int) ($counts[$value] ?? 0)];
        }

        $lainnya = $counts
            ->reject(function (int $total, mixed $kunci) use ($options): bool {
                $kunci = is_string($kunci) ? $kunci : (string) $kunci;

                return $kunci === '' || array_key_exists($kunci, $options);
            })
            ->sum();

        if ($lainnya > 0) {
            $rows[] = ['Lainnya', $lainnya];
        }

        return $rows;
    }

    /**
     * @param  Builder<Anggota>  $base
     * @return list<list<string|int>>
     */
    private function monthly(Builder $base, string $column, int $tahun): array
    {
        $expr = $this->monthExpression($column);
        $counts = (clone $base)
            ->whereNotNull($column)
            ->whereYear($column, $tahun)
            ->selectRaw($expr.' as bulan, count(*) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $rows = [];

        for ($month = 1; $month <= 12; $month++) {
            $rows[] = [
                $this->namaBulan($month).' '.$tahun,
                (int) ($counts[$month] ?? $counts[sprintf('%02d', $month)] ?? 0),
            ];
        }

        return $rows;
    }

    private function monthExpression(string $column): string
    {
        $allowed = ['created_at', 'masa_berlaku_hingga'];

        if (! in_array($column, $allowed, true)) {
            throw new \InvalidArgumentException('Kolom rekap bulanan tidak valid.');
        }

        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            default => "MONTH({$column})",
        };
    }

    /**
     * @return Collection<int, Wilayah>
     */
    private function provinsiUntukLaporan(User $user, ?string $provinsi): Collection
    {
        if ($user->organisasiLevel() === Area::CABANG && filled($user->pd_kode)) {
            return Wilayah::query()->where('kode', $user->pd_kode)->orderBy('nama')->get();
        }

        if ($user->organisasiLevel() === Area::DAERAH && filled($user->pd_kode)) {
            return Wilayah::query()->where('kode', $user->pd_kode)->orderBy('nama')->get();
        }

        if (filled($provinsi)) {
            return Wilayah::query()->where('kode', $provinsi)->orderBy('nama')->get();
        }

        return Wilayah::query()->anak(null)->orderBy('nama')->get();
    }

    /**
     * @return Collection<int, Wilayah>
     */
    private function kabupatenUntukLaporan(User $user, ?string $provinsi, ?string $kabupaten): Collection
    {
        if ($user->organisasiLevel() === Area::CABANG && filled($user->pc_kode)) {
            return Wilayah::query()->where('kode', $user->pc_kode)->orderBy('nama')->get();
        }

        if (filled($kabupaten)) {
            return Wilayah::query()->where('kode', $kabupaten)->orderBy('nama')->get();
        }

        $parent = $provinsi
            ?? ($user->organisasiLevel() === Area::DAERAH ? $user->pd_kode : null);

        if (filled($parent)) {
            return Wilayah::query()->anak($parent)->orderBy('nama')->get();
        }

        return collect();
    }

    private function cakupanLabel(User $user, ?string $provinsi, ?string $kabupaten): string
    {
        if (filled($kabupaten)) {
            $nama = Wilayah::query()->where('kode', $kabupaten)->value('nama');

            return $nama ? 'PC '.$nama : 'PC '.$kabupaten;
        }

        if (filled($provinsi)) {
            $nama = Wilayah::query()->where('kode', $provinsi)->value('nama');

            return $nama ? 'PD '.$nama : 'PD '.$provinsi;
        }

        return $user->wilayahTugasLabel();
    }

    private function namaBulan(int $bulan): string
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ][$bulan];
    }
}
