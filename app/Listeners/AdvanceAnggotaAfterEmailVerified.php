<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AnggotaStatusService;
use Illuminate\Auth\Events\Verified;

class AdvanceAnggotaAfterEmailVerified
{
    public function __construct(private readonly AnggotaStatusService $status) {}

    public function handle(Verified $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $anggota = $user->anggota;

        if ($anggota === null) {
            return;
        }

        $this->status->verifyEmail($anggota, $user);
    }
}
