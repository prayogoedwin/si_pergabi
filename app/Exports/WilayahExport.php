<?php

namespace App\Exports;

use App\Models\Wilayah;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WilayahExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Wilayah::query()->orderBy('kode');
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama',
            'Tingkat',
        ];
    }

    public function map($wilayah): array
    {
        return [
            $wilayah->kode,
            $wilayah->nama,
            $wilayah->tingkatLabel(),
        ];
    }
}
