<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AnggotaImport implements SkipsEmptyRows, ToCollection, WithHeadingRow, WithMultipleSheets
{
    use Importable;

    /** @var Collection<int, mixed> */
    public Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    /**
     * @return array<int, $this>
     */
    public function sheets(): array
    {
        return [0 => $this];
    }

    public function collection(Collection $collection): void
    {
        $this->rows = $collection;
    }
}
