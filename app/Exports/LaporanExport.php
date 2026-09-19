<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanExport implements Export, WithMultipleSheets
{
    /**
     * @param  list<array{judul: string, headings: list<string>, rows: list<list<string|int>>}>  $sections
     */
    public function __construct(private readonly array $sections) {}

    /**
     * @return list<LaporanSheet>
     */
    public function sheets(): array
    {
        return array_map(
            fn (array $section) => new LaporanSheet($section['judul'], $section['headings'], $section['rows']),
            $this->sections,
        );
    }
}
