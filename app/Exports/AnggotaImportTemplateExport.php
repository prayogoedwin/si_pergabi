<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AnggotaImportTemplateExport implements Export, WithMultipleSheets
{
    /**
     * @return list<object>
     */
    public function sheets(): array
    {
        return [
            new AnggotaImportTemplateSheet,
            new AnggotaImportPetunjukSheet,
        ];
    }
}
