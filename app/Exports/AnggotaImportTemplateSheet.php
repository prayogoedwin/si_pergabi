<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AnggotaImportTemplateSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'NTA',
            'NAMA',
            'HP',
            'EMAIL',
            'TEMPAT LAHIR',
            'JL',
            'ALAMAT',
            'NIK',
        ];
    }

    /**
     * @return list<list<string>>
     */
    public function array(): array
    {
        return [
            [
                '2026.36.3671.001',
                'Sinta Dewi, S.Pd',
                '081234567890',
                'sinta.contoh@example.com',
                'Tangerang',
                'Perempuan',
                'Jl. Melati No. 1, Kota Tangerang',
                '3671010101010001',
            ],
            [
                '2026.36.3671.002',
                'Budi Santoso, S.Ag',
                '081298765432',
                'budi.contoh@example.com',
                'Tangerang',
                'Laki-laki',
                'Jl. Mawar No. 2, Kota Tangerang',
                '',
            ],
        ];
    }

    public function title(): string
    {
        return 'Anggota';
    }
}
