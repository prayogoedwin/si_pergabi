<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanSheet implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @param  list<string>  $headingRow
     * @param  list<list<string|int>>  $rows
     */
    public function __construct(
        private readonly string $sheetTitle,
        private readonly array $headingRow,
        private readonly array $rows,
    ) {}

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return $this->headingRow;
    }

    public function title(): string
    {
        return mb_substr($this->sheetTitle, 0, 31);
    }
}
