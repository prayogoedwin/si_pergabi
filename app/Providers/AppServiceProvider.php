<?php

namespace App\Providers;

use App\Listeners\AdvanceAnggotaAfterEmailVerified;
use App\Models\Anggota;
use App\Observers\AnggotaObserver;
use App\Services\SettingService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Verified::class, AdvanceAnggotaAfterEmailVerified::class);
        Anggota::observe(AnggotaObserver::class);
        $this->app->make(SettingService::class)->applyMailConfig();
    }
}
