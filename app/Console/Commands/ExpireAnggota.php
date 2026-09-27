<?php

namespace App\Console\Commands;

use App\Services\AnggotaStatusService;
use Illuminate\Console\Command;

class ExpireAnggota extends Command
{
    protected $signature = 'pergabi:expire-anggota';

    protected $description = 'Nonaktifkan anggota aktif yang masa berlakunya sudah habis';

    public function handle(AnggotaStatusService $status): int
    {
        $count = $status->expireOverdue();

        $this->info("Dinonaktifkan: {$count} anggota.");

        return self::SUCCESS;
    }
}
