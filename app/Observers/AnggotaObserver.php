<?php

namespace App\Observers;

use App\Models\Anggota;
use App\Services\DashboardCacheService;

class AnggotaObserver
{
    public function __construct(private readonly DashboardCacheService $dashboardCache) {}

    public function saved(Anggota $anggota): void
    {
        $this->dashboardCache->forgetForAnggota($anggota);
    }

    public function deleted(Anggota $anggota): void
    {
        $this->dashboardCache->forgetForAnggota($anggota);
    }
}
